"""
Certificate Generator CLI

Usage: python certificate_generator.py --template template.pptx --data data.xlsx --outdir out --format png

Reads a PPTX template with placeholders in the form <<ColumnName>> and an Excel file where each row is a recipient.
Generates a file per row in the requested output format (pptx, pdf, png, jpg). Filename may be customized with a template string
using column names in braces, e.g. "{Initials}_{Surname}_{date}.png". Default is Fullname_date_timestamp.

Notes:
- Requires python-pptx, pandas, pillow, pdf2image.
- For PDF/PNG/JPG conversion, LibreOffice (soffice) and Poppler may be required. See README.
"""
import argparse
import logging
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime
from tempfile import mkstemp, mkdtemp
from typing import List, Dict, Optional, Tuple
from copy import deepcopy

import pandas as pd
from pptx import Presentation
from PIL import Image
from pdf2image import convert_from_path

PLACEHOLDER_RE = re.compile(r"<<([^<>]+)>>")


def parse_mapping_arg(map_arg: Optional[str]) -> Dict[str, int]:
    """Parse mapping string like "SheetA:0,SheetB:2" into dict{"SheetA":0,...}.
    Accepts comma or semicolon separators. Slide indices are 0-based.
    """
    mapping: Dict[str, int] = {}
    if not map_arg:
        return mapping
    parts = [p.strip() for p in re.split(r'[;,]', map_arg) if p.strip()]
    for part in parts:
        if ':' not in part:
            continue
        key, val = part.split(':', 1)
        key = key.strip()
        try:
            idx = int(val.strip())
            mapping[key] = idx
        except ValueError:
            # ignore non-integer mapping
            continue
    return mapping


def find_slide_indices_by_sheetname(prs: Presentation, sheet_name: str) -> List[int]:
    """Return list of slide indices where any text contains the sheet_name (case-insensitive)."""
    found: List[int] = []
    needle = str(sheet_name).strip().lower()
    if not needle:
        return found
    for idx, slide in enumerate(prs.slides):
        matched = False
        for shape in slide.shapes:
            try:
                if not shape.has_text_frame:
                    continue
                for p in shape.text_frame.paragraphs:
                    for run in p.runs:
                        if run.text and needle in run.text.lower():
                            matched = True
                            break
                    if matched:
                        break
            except Exception:
                continue
            if matched:
                break
        if matched:
            found.append(idx)
    return found


def remove_slide_at_index(prs: Presentation, index: int) -> None:
    """Remove the slide at `index` from `prs` by manipulating the slideIdList."""
    # Remove the slide id from the presentation sldIdLst
    sldIdLst = prs.slides._sldIdLst
    if index < 0 or index >= len(sldIdLst):
        return
    sldId = sldIdLst[index]
    sldIdLst.remove(sldId)


def sanitize_filename(name: str) -> str:
    # remove characters invalid on Windows filenames
    invalid = '<>:"/\\|?*'
    for ch in invalid:
        name = name.replace(ch, "_")
    name = name.strip()
    return name


# NEW: ensure we don't overwrite when multiple rows resolve to the same base name
def ensure_unique_basename(out_dir: str, base: str, ext: str) -> str:
    """Return a basename that is unique within out_dir for the given ext, appending (2), (3), ... if needed."""
    candidate = sanitize_filename(str(base))
    path = os.path.join(out_dir, f"{candidate}.{ext}")
    if not os.path.exists(path):
        return candidate
    i = 2
    while True:
        alt = f"{candidate} ({i})"
        path = os.path.join(out_dir, f"{alt}.{ext}")
        if not os.path.exists(path):
            return alt
        i += 1


def find_and_replace_in_shape(shape, mapping):
    # Replace placeholders in text frames
    if not shape.has_text_frame:
        return
    text_frame = shape.text_frame
    for p in text_frame.paragraphs:
        for run in p.runs:
            if not run.text:
                continue
            original = run.text
            new_text = original
            for match in PLACEHOLDER_RE.findall(original):
                key = match.strip().lower()
                value = mapping.get(key, "")
                # mapping values may be numpy types; handle NaN
                try:
                    if pd.isna(value):
                        value = ""
                except Exception:
                    pass
                new_text = new_text.replace(f"<<{match}>>", str(value))
            if new_text != original:
                run.text = new_text


def replace_placeholders(prs: Presentation, mapping: dict):
    for slide in prs.slides:
        for shape in slide.shapes:
            # text shapes
            try:
                find_and_replace_in_shape(shape, mapping)
            except Exception:
                # some shapes may throw; ignore and continue
                continue
            # tables
            if shape.shape_type == 19:  # TABLE
                table = shape.table
                for r in range(len(table.rows)):
                    for c in range(len(table.columns)):
                        cell = table.cell(r, c)
                        for p in cell.text_frame.paragraphs:
                            for run in p.runs:
                                original = run.text
                                new_text = original
                                for match in PLACEHOLDER_RE.findall(original):
                                    key = match.strip().lower()
                                    value = mapping.get(key, "")
                                    try:
                                        if pd.isna(value):
                                            value = ""
                                    except Exception:
                                        pass
                                    new_text = new_text.replace(f"<<{match}>>", str(value))
                                if new_text != original:
                                    run.text = new_text


def format_filename(template: str, row: pd.Series, date_now: datetime):
    # allow placeholders like {FirstName}, {Fullname}, {date}, {timestamp}
    # create a case-insensitive mapping of column headers
    mapping_ci = {str(k).strip().lower(): ('' if pd.isna(v) else v) for k, v in row.items()}
    mapping_ci['date'] = date_now.strftime('%m-%d-%Y')
    # timestamp: time component only to pair with {date}
    mapping_ci['timestamp'] = date_now.strftime('%H-%M-%S-%f')

    # default naming when no explicit template is provided
    if not template:
        # Prefer common fullname columns; fallback to first column value
        name_val = (
            mapping_ci.get('fullname')
            or mapping_ci.get('full name')
            or mapping_ci.get('name')
        )
        if not name_val:
            try:
                name_val = row.iloc[0]
            except Exception:
                name_val = ''
        if pd.isna(name_val):
            name_val = ''
        filename = f"{name_val}_{mapping_ci['date']}_{mapping_ci['timestamp']}"
    else:
        filename = template
        # replace {Token} placeholders case-insensitively using mapping_ci
        for token in re.findall(r"\{([^{}]+)\}", filename):
            val = mapping_ci.get(token.strip().lower(), '')
            filename = filename.replace(f"{{{token}}}", str(val))

    filename = sanitize_filename(str(filename))
    return filename


def pptx_to_pdf(soffice_path: str, pptx_path: str, out_dir: str) -> str:
    # Convert pptx to pdf using LibreOffice soffice CLI
    if not shutil.which(soffice_path):
        raise FileNotFoundError(f"soffice not found at '{soffice_path}'. Please install LibreOffice or provide full path.")
    cmd = [soffice_path, '--headless', '--convert-to', 'pdf', '--outdir', out_dir, pptx_path]
    subprocess.run(cmd, check=True)
    base = os.path.splitext(os.path.basename(pptx_path))[0]
    pdf_path = os.path.join(out_dir, base + '.pdf')
    if not os.path.exists(pdf_path):
        raise FileNotFoundError('PDF conversion failed; expected output not found: ' + pdf_path)
    return pdf_path


def pdf_to_images(pdf_path: str, dpi: int = 200, poppler_path: str = None):
    # returns list of PIL Images (one per page)
    images = convert_from_path(pdf_path, dpi=dpi, poppler_path=poppler_path)
    return images


def generate_for_row(prs_template: Presentation, row: pd.Series, out_dir: str, out_format: str,
                     filename_template: str, slide_indices: Optional[List[int]] = None,
                     soffice_path: str = 'soffice', poppler_path: str = None,
                     dpi: int = 200) -> Tuple[bool, str, Optional[Dict]]:
    """Generate certificate for a row. Returns (success, message, output_paths dict)."""
    date_now = datetime.now()
    filename_base = format_filename(filename_template, row, date_now)
    os.makedirs(out_dir, exist_ok=True)

    # choose a unique base name to avoid overwriting when duplicates occur
    unique_base = ensure_unique_basename(out_dir, filename_base, 'pptx')

    # copy presentation
    prs = Presentation()
    # load from template path by serializing: easier to use deep copy via save/load
    # Instead, save prs_template to a temp file and reopen
    fd, tmp_pptx = mkstemp(suffix='.pptx')
    os.close(fd)
    try:
        prs_template.save(tmp_pptx)
        prs = Presentation(tmp_pptx)
    except Exception:
        prs = prs_template

    # If slide_indices provided, remove all other slides so output contains only relevant slide(s)
    if slide_indices is not None:
        total = len(prs.slides)
        # normalize and keep unique valid indices
        keep = sorted(set(i for i in slide_indices if 0 <= i < total))
        # remove slides not in keep, iterate in reverse to avoid index shifting
        for i in range(total - 1, -1, -1):
            if i not in keep:
                try:
                    remove_slide_at_index(prs, i)
                except Exception:
                    # ignore removal errors and continue
                    continue

    # mapping: case-insensitive column headers -> values
    mapping = {str(k).strip().lower(): v for k, v in row.items()}
    replace_placeholders(prs, mapping)

    out_pptx = os.path.join(out_dir, unique_base + '.pptx')
    prs.save(out_pptx)

    out_paths = {}
    out_paths['pptx'] = out_pptx

    if out_format == 'pptx':
        return True, f"Generated: {out_paths}", out_paths

    # convert to pdf
    tmp_dir = mkdtemp()
    try:
        try:
            pdf_path = pptx_to_pdf(soffice_path, out_pptx, tmp_dir)
            out_pdf = os.path.join(out_dir, unique_base + '.pdf')
            shutil.move(pdf_path, out_pdf)
            out_paths['pdf'] = out_pdf
        except FileNotFoundError as e:
            # likely soffice not found; warn and skip conversion, keep PPTX only
            msg = f"PPTX->PDF conversion skipped for '{unique_base}': {e}"
            return False, msg, out_paths
        except subprocess.CalledProcessError as e:
            msg = f"PPTX->PDF conversion failed for '{unique_base}': {e}"
            return False, msg, out_paths
        except Exception as e:
            msg = f"Unexpected error during PPTX->PDF conversion for '{unique_base}': {e}"
            return False, msg, out_paths

        if out_format in ('png', 'jpg', 'jpeg'):
            images = pdf_to_images(out_pdf, dpi=dpi, poppler_path=poppler_path)
            # take first page by default; if multiple slides exist, return numbered files
            if len(images) == 1:
                img = images[0]
                img_format = 'PNG' if out_format == 'png' else 'JPEG'
                out_img_path = os.path.join(out_dir, unique_base + ('.png' if out_format == 'png' else '.jpg'))
                img.save(out_img_path, img_format)
                out_paths[out_format] = out_img_path
            else:
                # save numbered
                saved = []
                for i, img in enumerate(images, start=1):
                    ext = '.png' if out_format == 'png' else '.jpg'
                    out_img_path = os.path.join(out_dir, f"{unique_base}_page{i}{ext}")
                    img.save(out_img_path, 'PNG' if out_format == 'png' else 'JPEG')
                    saved.append(out_img_path)
                out_paths[out_format] = saved
    finally:
        try:
            os.remove(tmp_pptx)
        except Exception:
            pass
        shutil.rmtree(tmp_dir, ignore_errors=True)

    return True, f"Generated: {out_paths}", out_paths


def main(argv=None):
    parser = argparse.ArgumentParser(description='Generate certificates from a PPTX template and Excel data')
    parser.add_argument('--template', '-t', required=True, help='Path to PPTX template file')
    parser.add_argument('--data', '-d', required=True, help='Path to Excel file (.xlsx)')
    parser.add_argument('--outdir', '-o', required=True, help='Output directory')
    parser.add_argument('--format', '-f', choices=['pptx', 'pdf', 'png', 'jpg'], default='png', help='Export format')
    parser.add_argument('--name-template', '-n', default='', help='Filename template, use {ColumnName}, {date}, and {timestamp}. Default is Fullname_date_timestamp')
    parser.add_argument('--map', '-m', default='', help='Optional mapping from sheet name to slide index: "SheetA:0,SheetB:2" (0-based)')
    parser.add_argument('--soffice-path', default='soffice', help='Path to LibreOffice "soffice" executable (used for pptx->pdf)')
    parser.add_argument('--poppler-path', default=None, help='Optional path to poppler bin folder for pdf2image on Windows')
    parser.add_argument('--dpi', type=int, default=200, help='DPI for image rendering from PDF')
    args = parser.parse_args(argv)

    if not os.path.exists(args.template):
        print('Template file not found:', args.template)
        sys.exit(2)
    if not os.path.exists(args.data):
        print('Data file not found:', args.data)
        sys.exit(2)

    # Create date-based subfolder
    date_str = datetime.now().strftime('%Y-%m-%d')
    base_outdir = args.outdir
    args.outdir = os.path.join(base_outdir, date_str)
    os.makedirs(args.outdir, exist_ok=True)

    # Set up logging for failed items
    log_file = os.path.join(args.outdir, 'failed_items.log')
    logging.basicConfig(
        filename=log_file,
        level=logging.ERROR,
        format='%(asctime)s - %(message)s',
        datefmt='%Y-%m-%d %H:%M:%S'
    )

    # read data: read all sheets into a dict of DataFrames
    try:
        sheets = pd.read_excel(args.data, sheet_name=None)
    except Exception as e:
        print('Failed to read Excel file:', e)
        sys.exit(3)

    # load template once
    try:
        prs_template = Presentation(args.template)
    except Exception as e:
        print('Failed to read template pptx:', e)
        sys.exit(4)

    explicit_map = parse_mapping_arg(args.map)

    sheet_names = list(sheets.keys())
    total_files = 0
    failed_count = 0
    print(f'Generating files to "{args.outdir}" in format {args.format}...')

    for sheet_idx, sheet_name in enumerate(sheet_names):
        df = sheets[sheet_name]
        # determine slide indices for this sheet
        slide_indices: List[int] = []
        if sheet_name in explicit_map:
            slide_indices = [explicit_map[sheet_name]]
        else:
            slide_indices = find_slide_indices_by_sheetname(prs_template, sheet_name)
            if not slide_indices:
                # fallback: if counts match, map by order
                if len(sheet_names) == len(prs_template.slides):
                    slide_indices = [sheet_idx]
                else:
                    print(f'Warning: could not find slide for sheet "{sheet_name}" and no explicit map provided; skipping this sheet')
                    continue

        print(f'Processing sheet "{sheet_name}" with {len(df)} rows using slide indices {slide_indices}')
        for idx, row in df.iterrows():
            try:
                success, message, res = generate_for_row(prs_template, row, args.outdir, args.format, args.name_template,
                                       slide_indices=slide_indices, soffice_path=args.soffice_path,
                                       poppler_path=args.poppler_path, dpi=args.dpi)
                if success:
                    print('Generated:', res)
                    total_files += 1
                else:
                    print(f'Warning: {message}')
                    logging.error(f"Sheet: {sheet_name}, Row: {idx}, Error: {message}")
                    failed_count += 1
            except Exception as e:
                error_msg = f'Failed for sheet {sheet_name} row {idx}: {e}'
                print(error_msg)
                logging.error(error_msg)
                failed_count += 1

    summary = f'\nGeneration complete. Generated: {total_files} files, Failed: {failed_count} items.'
    print(summary)
    if failed_count > 0:
        print(f'See "{log_file}" for details on failed items.')
    logging.error(f'Generation Summary: {total_files} files generated, {failed_count} failed.')


if __name__ == '__main__':
    main()

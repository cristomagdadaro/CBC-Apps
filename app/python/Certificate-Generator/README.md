Certificate Generator

This is a small Python CLI tool that generates personalized certificates from a PPTX template and an Excel data workbook.

Overview
- The tool replaces placeholders in a PPTX template (placeholders use the form <<ColumnName>>).
- The Excel input can be a single sheet or a workbook with multiple sheets. Each sheet is treated as a separate data source that maps to one or more slides in the PPTX template.
- You can export each generated certificate as PPTX, PDF, PNG, or JPG.

Key features
- Placeholder replacement using <<ColumnName>> tokens in PPTX text boxes and table cells.
- Multi-template support: A single PPTX can contain several certificate slides (for example: Appreciation, Recognition, Participation). The script maps each Excel sheet to the matching slide(s) and generates per-row certificates using only the matched slide(s).
- Multiple-sheet Excel input: pass an .xlsx workbook with multiple sheets; each sheet will be processed independently.
- Slide mapping detection:
  - Auto-detect: the script searches each slide for the sheet name text (case-insensitive). If found, that slide is used for that sheet.
  - Order fallback: if the workbook has the same number of sheets as slides, the script maps by sheet order to slide order.
  - Explicit mapping: you can force a mapping using the `--map` argument (useful if auto-detection fails).
- Filename customization using `--name-template` with tokens like `{FirstName}`, `{Surname}`, and `{date}`. A sensible default filename is generated when not provided.

Requirements
- Python 3.8+
- Install Python dependencies:

```bash
pip install -r requirements.txt
```

Optional system tools (required for non-PPTX outputs)
- LibreOffice (soffice) — used to convert PPTX -> PDF. On Windows, ensure `soffice.exe` is on PATH or pass its full path with `--soffice-path`.
- Poppler — recommended on Windows for `pdf2image` to convert PDF -> PNG/JPG. If you need PNG/JPG output on Windows, install Poppler and pass the path to its `bin` folder via `--poppler-path`.

Quick start
1. Prepare a PPTX template with placeholders such as `<<FirstName>>` and `<<Surname>>` on the slide(s) you want to populate.
2. Prepare an Excel workbook (`.xlsx`). It can be a single sheet or multiple sheets. Each sheet should contain column headers that match the placeholders.
3. Run the generator. Examples below assume `cmd.exe` on Windows.

Generate PNG files (auto-detect slide mapping by sheet name):

```cmd
python certificate_generator.py --template my_template.pptx --data my_workbook.xlsx --outdir out --format png
```

Generate PPTX files (no external tools required):

```cmd
python certificate_generator.py --template my_template.pptx --data my_workbook.xlsx --outdir out --format pptx
```

Explicit slide mapping
- If auto-detection (searching slide text for the sheet name) does not find the correct slide, provide an explicit mapping with `--map` using `SheetName:SlideIndex` pairs (SlideIndex is 0-based). Multiple pairs are separated by commas or semicolons.

Example: map three sheets to slides 0,1,2 respectively

```cmd
python certificate_generator.py --template my_template.pptx --data my_workbook.xlsx --outdir out --format png --map "Appreciation:0,Recognition:1,Participation:2"
```

Filename customization
- Use `--name-template` to control output filenames. Use `{ColumnName}` or `{date}` inside the template. Example:

```cmd
python certificate_generator.py --template my_template.pptx --data my_workbook.xlsx --outdir out --format png --name-template "{FirstName}_{Surname}_{date}"
```

- Default filename when `--name-template` is not provided: initials + full surname + _date (MM-DD-YYYY). Example output: `JSmith_11-12-2025.png`.

Sample files and testing
- There are helper scripts included to generate sample templates and data:
  - `create_sample_files.py` — creates a simple single-slide template and a single-sheet Excel file.
  - `create_multi_template.py` — creates a 3-slide PPTX (Appreciation, Recognition, Participation) and a 3-sheet Excel workbook for testing the multi-sheet/multi-slide flow.

Run the multi-template sample and generate PPTX outputs (Windows cmd.exe):

```cmd
python create_multi_template.py
python certificate_generator.py --template multi_template.pptx --data multi_data.xlsx --outdir out_multi --format pptx
```

Troubleshooting
- If PDF conversion fails, make sure LibreOffice is installed and `--soffice-path` points to the `soffice`/`soffice.exe` executable.
- If `pdf2image` fails on Windows, install Poppler and pass `--poppler-path "C:\path\to\poppler\bin"`.
- If a sheet is skipped with a warning that no slide was found:
  - Confirm the sheet name appears as text inside the intended slide for auto-detection, or
  - Provide an explicit mapping using `--map`, or
  - Ensure the number of sheets equals number of slides to enable the order-based fallback.

Notes and edge-cases
- Placeholders in table cells are supported, but very complex embedded shapes may not be modified — the script ignores shape types that raise exceptions during replacement and continues.
- If a sheet maps to multiple slides (the sheet name occurs in more than one slide), the output will include all matched slides in the generated file.
- Filename sanitization: characters invalid on Windows filenames (e.g. `< > : " / \ | ? *`) are replaced with underscores.

License
- MIT

If you'd like, I can:
- Add a `--zip` option to bundle generated outputs,
- Add parallelized processing to speed large batches,
- Improve matching rules to allow regex or exact-match modes,
- Or create a simple GUI/web UI for non-CLI users.

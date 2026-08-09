<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $template['label'] ?? 'Report' }}</title>
    <style>
        body {
            font-family: Cambria, sans-serif;
            font-size: 8px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            background: #f2f2f2;
        }

        @page {
            size: A4 portrait;
            margin: 5mm;
        }
    </style>
</head>

<body>

    @php
        $dateReported = Carbon\Carbon::parse($report->reported_at)->format('F d, Y');
        $dateGenerated = now();
        $reportId = $report->id;
        $transaction = $report->transaction;
        $item = $transaction ? $transaction->item : null;
        $personnel = $transaction ? $transaction->personnel : null;
        
        $getImagePath = function ($path) {
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return asset($path);
        };

        $logos = [
            'cbc' => $getImagePath('imgs/logo-black.png'),
            'overlay' => $getImagePath('imgs/Overlay.png'),
            'da' => $getImagePath('imgs/da_bpo.png'),
            'bp' => $getImagePath('imgs/bagong_pilipinas.png'),
        ];
    @endphp


    <div style="width:100%;padding:3mm;box-sizing:border-box;">
        <div style="position:relative">
            <table style="border:none; width:100%; font-family:'Times New Roman'; border-collapse:collapse;">
                <tr style="border:none">
                    <!-- Logo -->
                    <td style="border:none; width:40px; vertical-align:middle;">
                        <img src="{{ $logos['cbc'] }}" style="height:50px;">
                    </td>

                    <!-- Text block -->
                    <td style="border:none; vertical-align:middle; padding-left:6px;">
                        <div style="font-size:10px; line-height:12px;">
                            Department of Agriculture
                        </div>

                        <div style="font-size:12px; font-weight:bold; color:#4CAF50; line-height:14px;">
                            CROP BIOTECHNOLOGY CENTER
                        </div>

                        <div style="font-size:8px; line-height:10px;">
                            DA-PhilRice Compound, Muñoz, Nueva Ecija
                        </div>
                    </td>

                </tr>
            </table>

            <img src="{{ $logos['overlay'] }}"
                style="height:120px;position:absolute;right:0;top:0;z-index:-1">
        </div>

        <div class="header">
            <h3 style="font-size:14px">{{ strtoupper($template['label'] ?? 'Report') }}</h3>
            <div style="opacity: 50%;">SYSTEM GENERATED REPORT</div>
        </div>

        <table style="border: none; border-collapse: collapse;">
            <tr class="section-title">
                <td colspan="4">General Information</td>
            </tr>
            <tr>
                <td style="width: 20%;"><b>Date of Report</b></td>
                <td style="width: 30%;">{{ $dateReported }}</td>
                <td style="width: 20%;"><b>Report Type</b></td>
                <td style="width: 30%;">{{ $template['label'] ?? $report->report_type }}</td>
            </tr>
            @if ($transaction)
                <tr>
                    <td><b>Transaction Barcode</b></td>
                    <td>{{ $transaction->barcode ?? 'N/A' }}</td>
                    <td><b>Transaction Type</b></td>
                    <td>{{ $transaction->transac_type ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><b>Personnel / User</b></td>
                    <td colspan="3">{{ $personnel->name ?? $personnel->full_name ?? $transaction->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><b>Item / Equipment</b></td>
                    <td colspan="3">
                        @if ($item)
                            {{ $item->brand ?? '' }} {{ $item->description ?? 'Unnamed Item' }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endif

            <tr class="section-title">
                <td colspan="4">Report Details</td>
            </tr>

            @if (!empty($template['fields']) && is_array($template['fields']))
                @foreach ($template['fields'] as $key => $field)
                    @php
                        $value = $report->report_data[$key] ?? 'N/A';
                    @endphp
                    <tr>
                        <td colspan="1"><b>{{ $field['label'] ?? ucwords(str_replace('_', ' ', $key)) }}</b></td>
                        <td colspan="3">{!! nl2br(e($value)) !!}</td>
                    </tr>
                @endforeach
            @else
                @foreach ($report->report_data ?? [] as $key => $value)
                    <tr>
                        <td colspan="1"><b>{{ ucwords(str_replace('_', ' ', $key)) }}</b></td>
                        <td colspan="3">{!! nl2br(e($value)) !!}</td>
                    </tr>
                @endforeach
            @endif

            @if (!empty($report->notes))
                <tr class="section-title">
                    <td colspan="4">Additional Notes</td>
                </tr>
                <tr>
                    <td colspan="4">{!! nl2br(e($report->notes)) !!}</td>
                </tr>
            @endif

        </table>

        <footer style="margin-top: 5mm; position: absolute; bottom: 0; width: 100%;">
            <table style="width: 100%; border: none; border-collapse: collapse;">
                <tr style="height: 30px;">
                    <!-- Left side -->
                    <td style="text-align: left; border: none; vertical-align: top; padding: 0;">
                        <h3
                            style="font-size: 8px !important; margin: 0; font-weight: bold; color: #4CAF50; font-family: 'Times New Roman'">
                            Biotech for Better Crop for Better Lives
                        </h3>
                        <div style="font-size: 6px !important;">Email: cropbiotechcenter@gmail.com</div>
                        <div style="font-size: 6px !important;">Website: dacbc.philrice.gov.ph</div>
                        <div style="font-size: 6px !important;">Social Media:
                            www.facebook.com/DACropBiotechCenter</div>
                    </td>

                    <td
                        style="border: none; font-size: 6px !important; text-align: left; vertical-align: top;">
                        <div>SYSTEM GENERATED</div>
                        <div>Date Generated: {{ now() }}</div>
                        <div>Report ID: {{ $report->id }}</div>
                    </td>

                    <!-- Right side -->
                    <td
                        style="text-align: right; vertical-align: bottom; border: none; padding-right: 20px;">
                        <img src="{{ $logos['da'] }}" style="width: 30px; height: auto;">
                        <img src="{{ $logos['bp'] }}" style="width: 30px; height: auto;">
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>

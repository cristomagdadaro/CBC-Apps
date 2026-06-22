@php
    $logoPath = public_path('imgs/logo-black.png');
    $logoDataUri = null;

    if (is_file($logoPath)) {
        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Personnel ID Card</title>
    <style>
        @page {
            size: 74mm 105mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }

        .id-card {
            width: 74mm;
            height: 105mm;
            border: 0.3mm solid #e2e8f0;
            border-radius: 2mm;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .header {
            height: 12mm;
            padding: 3mm 3.4mm;
            border-bottom: 0.8mm solid #16a34a;
        }

        .logo {
            width: 6mm;
            height: 6mm;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
        }

        .brand {
            display: inline-block;
            vertical-align: middle;
            margin-left: 2mm;
            color: #14532d;
            font-size: 9px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .photo-wrap {
            position: relative;
            height: 33mm;
            text-align: center;
        }

        .stripe {
            position: absolute;
            left: 0;
            right: 0;
            top: 12mm;
            height: 10mm;
            background: #14532d;
        }

        .photo {
            position: relative;
            z-index: 2;
            width: 28mm;
            height: 28mm;
            margin-top: 3mm;
            border: 0.7mm solid #14532d;
            border-radius: 50%;
            object-fit: cover;
            background: #f8fafc;
        }

        .photo-placeholder {
            position: relative;
            z-index: 2;
            width: 28mm;
            height: 28mm;
            margin: 3mm auto 0;
            border: 0.7mm solid #14532d;
            border-radius: 50%;
            background: #e2e8f0;
        }

        .content {
            padding: 0 4.4mm;
            text-align: center;
        }

        .name {
            margin: 0 0 1.5mm;
            color: #0f172a;
            font-size: 13px;
            line-height: 1.25;
            font-weight: 800;
            text-transform: uppercase;
            word-break: break-word;
        }

        .employee-id {
            display: inline-block;
            padding: 0.8mm 2.5mm;
            border-radius: 4mm;
            background: #dcfce7;
            color: #14532d;
            font-size: 8px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .fields {
            margin-top: 5mm;
            text-align: left;
        }

        .field {
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            border-bottom: 0.25mm dashed #e2e8f0;
        }

        .field:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .label {
            margin: 0;
            color: #64748b;
            font-size: 7.5px;
            line-height: 1.15;
            font-weight: 700;
            text-transform: uppercase;
        }

        .value {
            margin: 0.5mm 0 0;
            color: #334155;
            font-size: 9px;
            line-height: 1.2;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 2mm 3.5mm;
            border-top: 0.25mm solid #f1f5f9;
            background: #f8fafc;
            color: #94a3b8;
            text-align: center;
            font-size: 6px;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <section class="id-card">
        <header class="header">
            @if ($logoDataUri)
                <img class="logo" src="{{ $logoDataUri }}" alt="CBC Logo">
            @endif
            <div class="brand">DA-Crop Biotechnology Center</div>
        </header>

        <div class="photo-wrap">
            <div class="stripe"></div>
            @if (!empty($card['photo_data_uri']))
                <img class="photo" src="{{ $card['photo_data_uri'] }}" alt="Personnel Photo">
            @else
                <div class="photo-placeholder"></div>
            @endif
        </div>

        <main class="content">
            <h1 class="name">{{ $card['full_name'] }}</h1>
            <div class="employee-id">{{ $card['employee_id'] ?: '-' }}</div>

            <div class="fields">
                <div class="field">
                    <p class="label">Registration Type</p>
                    <p class="value">{{ $card['registration_type_label'] ?: '-' }}</p>
                </div>

                <div class="field">
                    <p class="label">Date Issued</p>
                    <p class="value">{{ $card['date_issued'] ?: '-' }}</p>
                </div>
            </div>
        </main>

        <footer class="footer">This ID is system-generated for authorized CBC access and coordination.</footer>
    </section>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Barcode Labels</title>
    <style>
        @page {
            size: 5cm 3cm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .label {
            width: 5cm;
            height: 3cm;
            padding: 0.1cm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
        }
        .label-text {
            font-size: 10px;
            line-height: 1.2;
            color: #111827;
        }
        .label-item {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .label-brand {
            font-size: 9px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .label-barcode {
            font-size: 10px;
            text-align: center;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .barcode-svg {
            width: 100%;
            height: 30px;
            display: block;
        }
        .barcode-svg svg {
            width: 100%;
            height: 30px;
            display: block;
        }
    </style>
</head>
<body>
@foreach ($labels as $label)
    <div class="label">
        <div class="label-text">
            <div class="label-item">{{ $label['name'] }}</div>
            <div class="label-brand">{{ $label['brand'] ?? 'N/A' }}</div>
        </div>
        <div class="barcode-svg">{!! $label['svg'] !!}</div>
        <div class="label-barcode">{{ $label['barcode'] }}</div>
    </div>
@endforeach
</body>
</html>

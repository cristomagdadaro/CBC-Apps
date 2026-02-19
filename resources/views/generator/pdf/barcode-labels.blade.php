<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Barcode Labels</title>
    <style>
        @page {
            size: {{ $paperWidth }}cm {{ $paperHeight }}cm;
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
            width: {{ $paperWidth }}cm;
            height: {{ $paperHeight }}cm;
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
        .barcode-svg img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .barcode-svg svg {
            width: 100%;
            height: 30px;
            display: block;
        }
        .qr-code {
            width: 56px;
            height: 56px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .qr-code svg,
        .qr-code-both svg {
            width: 100%;
            height: 100%;
            display: block;
        }
        .label-content-barcode {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }
        .label-content-qr {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            flex-grow: 1;
        }
        .label-content-both {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            flex-grow: 1;
            gap: 2px;
        }
        .qr-code-both {
            width: 40px;
            height: 40px;
        }
        .qr-code-both img {
            width: 100%;
            height: 100%;
        }
        .barcode-svg-both {
            height: 24px;
        }
    </style>
</head>
<body>
@foreach ($labels as $label)
    <div class="label">
        @if ($printMode === 'barcode')
            <div class="label-content-barcode">
                <div class="label-text">
                    <div class="label-item">{{ $label['name'] }}</div>
                    <div class="label-brand">{{ $label['brand'] ?? 'N/A' }}</div>
                </div>
                @if (!empty($label['barcodeDataUri']))
                    <div class="barcode-svg"><img src="{{ $label['barcodeDataUri'] }}" alt="Barcode" /></div>
                @endif
                <div class="label-barcode">{{ $label['barcode'] }}</div>
            </div>
        @elseif ($printMode === 'qr')
            <div class="label-content-qr">
                <div class="label-text">
                    <div class="label-item">{{ $label['name'] }}</div>
                    <div class="label-brand">{{ $label['brand'] ?? 'N/A' }}</div>
                </div>
                @if (!empty($label['qrSvg']))
                    <div class="qr-code" style="width: {{ $label['qrSizePx'] ?? 56 }}px; height: {{ $label['qrSizePx'] ?? 56 }}px;">
                        {!! $label['qrSvg'] !!}
                    </div>
                @endif
                <div class="label-barcode">{{ $label['barcode'] }}</div>
            </div>
        @elseif ($printMode === 'both')
            <div class="label-content-both">
                <div class="label-text" style="text-align: center;">
                    <div class="label-item">{{ $label['name'] }}</div>
                    <div class="label-brand">{{ $label['brand'] ?? 'N/A' }}</div>
                </div>
                @if (!empty($label['qrSvg']))
                    <div class="qr-code-both" style="width: {{ $label['qrSizePx'] ?? 40 }}px; height: {{ $label['qrSizePx'] ?? 40 }}px;">
                        {!! $label['qrSvg'] !!}
                    </div>
                @endif
                @if (!empty($label['barcodeDataUri']))
                    <div class="barcode-svg barcode-svg-both"><img src="{{ $label['barcodeDataUri'] }}" alt="Barcode" /></div>
                @endif
                <div class="label-barcode">{{ $label['barcode'] }}</div>
            </div>
        @endif
    </div>
@endforeach
</body>
</html>
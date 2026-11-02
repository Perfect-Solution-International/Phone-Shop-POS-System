<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Labels - {{ $labelData['code'] }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #fff;
            color: #000;
        }

        @if($template === 'thermal_50x30')
            @page {
                size: 50mm 30mm;
                margin: 0;
            }
            .label-page {
                width: 50mm;
                height: 30mm;
                padding: 1.5mm 2mm;
                page-break-after: always;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                text-align: center;
                overflow: hidden;
            }
            .shop-name {
                font-size: 8pt;
                font-weight: 800;
                text-transform: uppercase;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                border-bottom: 0.5pt dashed #666;
                padding-bottom: 0.5mm;
            }
            .item-title {
                font-size: 8pt;
                font-weight: 700;
                line-height: 1.1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .item-sub {
                font-size: 6.5pt;
                color: #333;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .barcode-svg {
                width: 100%;
                max-height: 10mm;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .barcode-svg svg {
                height: 9mm;
                width: auto;
                max-width: 44mm;
            }
            .code-text {
                font-size: 7.5pt;
                font-weight: 700;
                letter-spacing: 0.5px;
            }
            .price-tag {
                font-size: 8.5pt;
                font-weight: 900;
                border-top: 0.5pt solid #000;
                padding-top: 0.5mm;
            }

        @elseif($template === 'thermal_40x25')
            @page {
                size: 40mm 25mm;
                margin: 0;
            }
            .label-page {
                width: 40mm;
                height: 25mm;
                padding: 1mm 1.5mm;
                page-break-after: always;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                text-align: center;
                overflow: hidden;
            }
            .shop-name {
                font-size: 7pt;
                font-weight: 800;
                text-transform: uppercase;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .item-title {
                font-size: 7pt;
                font-weight: 700;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .barcode-svg {
                max-height: 7mm;
            }
            .barcode-svg svg {
                height: 7mm;
                max-width: 36mm;
            }
            .code-text {
                font-size: 6.5pt;
                font-weight: 700;
            }
            .price-tag {
                font-size: 7.5pt;
                font-weight: 900;
            }

        @else
            /* A4 Sheet 3 columns x 8 rows */
            @page {
                size: A4 portrait;
                margin: 10mm 8mm;
            }
            .sheet-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                grid-gap: 3mm;
            }
            .label-page {
                height: 32mm;
                border: 0.5pt dashed #cbd5e1;
                padding: 2mm;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                text-align: center;
                border-radius: 2mm;
            }
            .shop-name {
                font-size: 7.5pt;
                font-weight: 800;
                text-transform: uppercase;
            }
            .item-title {
                font-size: 8pt;
                font-weight: 700;
            }
            .barcode-svg svg {
                height: 9mm;
                max-width: 50mm;
            }
            .code-text {
                font-size: 7.5pt;
                font-weight: 700;
            }
            .price-tag {
                font-size: 8.5pt;
                font-weight: 900;
            }
        @endif

        @media screen {
            body {
                background: #f1f5f9;
                padding: 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .print-toolbar {
                background: #1e293b;
                color: #fff;
                padding: 10px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: flex;
                gap: 15px;
                align-items: center;
            }
            .print-btn {
                background: #4f46e5;
                color: #fff;
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
            }
            .label-page {
                background: #fff;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
                margin-bottom: 10px;
            }
        }
        @media print {
            .print-toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span>Ready to print <strong>{{ $quantity }}</strong> labels ({{ $template }})</span>
            <span style="background: rgba(255,255,255,0.2); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.3); display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Tip: Set Margins to "None" in Print Dialog</span>
            </span>
        </div>
        <button class="print-btn" onclick="window.print()">Print Now (Ctrl + P)</button>
    </div>

    @if($template === 'sheet_a4')
        <div class="sheet-grid">
            @for($i = 0; $i < $quantity; $i++)
                <div class="label-page">
                    <div class="shop-name">{{ $shop->name ?? 'PHONE SHOP POS' }}</div>
                    <div class="item-title">{{ $labelData['title'] }}</div>
                    <div class="barcode-svg">{!! $labelData['barcode_svg'] !!}</div>
                    <div class="code-text">{{ $labelData['code'] }}</div>
                    <div class="price-tag">LKR {{ number_format($labelData['price'], 2) }}</div>
                </div>
            @endfor
        </div>
    @else
        @for($i = 0; $i < $quantity; $i++)
            <div class="label-page">
                <div class="shop-name">{{ $shop->name ?? 'PHONE SHOP POS' }}</div>
                <div>
                    <div class="item-title">{{ $labelData['title'] }}</div>
                    @if(!empty($labelData['subtitle']) && $template === 'thermal_50x30')
                        <div class="item-sub">{{ $labelData['subtitle'] }}</div>
                    @endif
                </div>
                <div class="barcode-svg">
                    {!! $labelData['barcode_svg'] !!}
                </div>
                <div class="code-text">{{ $labelData['code'] }}</div>
                <div class="price-tag">LKR {{ number_format($labelData['price'], 2) }}</div>
            </div>
        @endfor
    @endif

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>


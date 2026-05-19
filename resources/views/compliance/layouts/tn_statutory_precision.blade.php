<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('form_title')</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
            
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-family: "Times New Roman", Times, serif;
                font-size: 8pt;
                margin-top: 10mm;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
        }
        
        .statutory-header {
            text-align: center;
            margin-bottom: 10mm;
            border-bottom: 2mm solid #000;
            padding-bottom: 8mm;
        }
        
        .form-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 8mm 0;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            line-height: 1.3;
        }
        
        .act-reference {
            font-size: 10pt;
            margin: 4mm 0;
            font-style: italic;
            line-height: 1.4;
        }
        
        .rule-reference {
            font-size: 10pt;
            margin: 4mm 0 0 0;
            line-height: 1.4;
        }
        
        .establishment-info {
            border: 1.5mm solid #000;
            padding: 8mm;
            margin: 0 0 10mm 0;
            background-color: #FAFAFA;
        }
        
        .establishment-info p {
            margin: 0 0 3mm 0;
            font-size: 10pt;
            line-height: 6mm;
        }
        
        .establishment-info strong {
            display: inline-block;
            width: 50mm;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5mm 0;
            font-size: 9pt;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            border: 0.75mm solid #000;
            padding: 3mm 2mm;
            text-align: left;
            vertical-align: middle;
            line-height: 1.3;
        }
        
        th {
            background-color: #E8E8E8;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
            min-height: 8mm;
        }
        
        td {
            font-size: 9pt;
            min-height: 8mm;
        }
        
        th:not(:last-child),
        td:not(:last-child) {
            border-right: 0.5mm solid #000;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .totals-row {
            font-weight: bold;
            background-color: #E8E8E8;
        }
        
        .totals-row td {
            border-top: 1.5mm double #000;
            padding: 4mm 2mm;
        }
        
        .nil-declaration {
            text-align: center;
            padding: 20mm;
            border: 2mm solid #000;
            margin: 15mm 0;
            font-weight: bold;
            font-size: 12pt;
            background-color: #F5F5F5;
            min-height: 40mm;
        }
        
        .signature-block {
            margin-top: 15mm;
            page-break-inside: avoid;
        }
        
        .declaration-text {
            border: 1mm solid #000;
            padding: 5mm;
            margin: 0 0 10mm 0;
            font-size: 9pt;
            line-height: 1.6;
            text-align: justify;
            background-color: #FAFAFA;
        }
        
        .signature-grid {
            display: table;
            width: 100%;
            margin-top: 10mm;
        }
        
        .signature-left {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            padding-right: 10mm;
        }
        
        .signature-right {
            display: table-cell;
            width: 60%;
            text-align: right;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 0.5mm solid #000;
            width: 80mm;
            margin: 25mm 0 5mm 0;
            display: inline-block;
        }
        
        .signature-label {
            font-size: 9pt;
            margin-top: 5mm;
            line-height: 1.5;
        }
        
        .signature-label strong {
            font-weight: bold;
            font-size: 10pt;
        }
        
        .seal-placeholder {
            margin-top: 8mm;
            font-size: 8pt;
            font-style: italic;
            color: #666;
            border: 0.5mm dashed #999;
            padding: 5mm;
            width: 50mm;
            height: 50mm;
            display: inline-block;
            text-align: center;
            line-height: 50mm;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        @yield('additional_styles')
    </style>
</head>
<body>
    <div class="statutory-header">
        <div class="form-title">@yield('form_title')</div>
        <div class="act-reference">@yield('act_reference')</div>
        <div class="rule-reference">@yield('rule_reference')</div>
    </div>
    
    <div class="establishment-info">
        <p><strong>Name of Establishment:</strong> {{ $header['tenant']['name'] }}</p>
        @if(isset($header['branch']))
        <p><strong>Branch/Unit:</strong> {{ $header['branch']['name'] }}</p>
        <p><strong>Address:</strong> {{ $header['branch']['address'] }}</p>
        <p><strong>Factory License No:</strong> {{ $header['branch']['license'] }}</p>
        @endif
        <p><strong>Period:</strong> {{ $header['period'] }}</p>
    </div>
    
    @yield('content')
    
    @if(!$is_nil)
    <div class="signature-block">
        <div class="declaration-text">
            @yield('declaration')
        </div>
        
        <div class="signature-grid">
            <div class="signature-left">
                <div style="margin: 3mm 0; font-size: 9pt;">
                    Date: <span style="display: inline-block; width: 40mm; border-bottom: 0.5mm solid #000; margin-left: 3mm;"></span>
                </div>
                <div style="margin: 3mm 0; font-size: 9pt;">
                    Place: <span style="display: inline-block; width: 40mm; border-bottom: 0.5mm solid #000; margin-left: 3mm;"></span>
                </div>
            </div>
            <div class="signature-right">
                <div class="signature-line"></div>
                <div class="signature-label">
                    <strong>@yield('signatory_title', 'Manager / Occupier')</strong><br>
                    Name: _______________<br>
                    Designation: _______________<br>
                    Signature & Seal
                </div>
                <div class="seal-placeholder">(Seal)</div>
            </div>
        </div>
    </div>
    @endif
</body>
</html>

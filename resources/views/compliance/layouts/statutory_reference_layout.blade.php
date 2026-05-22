<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('form_title')</title>
    <style>
        @page {
            margin: 20mm 15mm;
            @bottom-center {
                content: @yield('page_number_style', '"Page " counter(page)');
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
            line-height: 1.4;
            color: #000;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: @yield('header_margin_bottom', '15px');
        }
        
        .form-title {
            font-size: @yield('title_font_size', '14pt');
            font-weight: bold;
            text-transform: @yield('title_transform', 'uppercase');
            margin-bottom: 5px;
        }
        
        .act-reference {
            font-size: @yield('act_font_size', '10pt');
            margin-bottom: 3px;
        }
        
        .rule-reference {
            font-size: @yield('rule_font_size', '10pt');
            margin-bottom: 3px;
        }
        
        .establishment-block {
            margin: @yield('establishment_margin', '15px 0');
            font-size: 10pt;
        }
        
        .establishment-block table {
            width: 100%;
            border: none;
        }
        
        .establishment-block td {
            padding: 2px 5px;
            border: none;
        }
        
        .establishment-label {
            font-weight: bold;
            width: @yield('label_width', '180px');
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: @yield('table_font_size', '9pt');
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: @yield('cell_padding', '4px 6px');
            vertical-align: top;
        }
        
        .data-table th {
            background-color: @yield('header_bg', 'transparent');
            font-weight: bold;
            text-align: center;
        }
        
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .totals-row {
            font-weight: bold;
            background-color: @yield('totals_bg', 'transparent');
        }
        
        .nil-block {
            text-align: center;
            padding: @yield('nil_padding', '40px 20px');
            border: @yield('nil_border', '1px solid #000');
            margin: 20px 0;
            font-weight: bold;
            font-size: @yield('nil_font_size', '11pt');
        }
        
        .declaration-section {
            margin-top: @yield('declaration_margin_top', '30px');
            page-break-inside: avoid;
        }
        
        .declaration-text {
            font-size: @yield('declaration_font_size', '10pt');
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .signature-section {
            margin-top: @yield('signature_margin_top', '40px');
            page-break-inside: avoid;
        }
        
        .signature-table {
            width: 100%;
            border: none;
        }
        
        .signature-table td {
            border: none;
            vertical-align: top;
            padding: 5px;
        }
        
        .signature-left {
            width: 50%;
            text-align: left;
        }
        
        .signature-right {
            width: 50%;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: @yield('signature_line_width', '200px');
            margin-top: @yield('signature_line_margin', '60px');
            display: inline-block;
        }
        
        .signature-label {
            font-size: 9pt;
            margin-top: 5px;
        }
        
        .seal-placeholder {
            margin-top: 10px;
            font-size: 8pt;
            font-style: italic;
        }
    </style>
    @yield('custom_styles')
</head>
<body>
    <div class="form-header">
        <div class="form-title">@yield('form_title')</div>
        @hasSection('act_reference')
        <div class="act-reference">@yield('act_reference')</div>
        @endif
        @hasSection('rule_reference')
        <div class="rule-reference">@yield('rule_reference')</div>
        @endif
        @yield('additional_header')
    </div>
    
    <div class="establishment-block">
        @yield('establishment_info')
    </div>
    
    @yield('content')
    
    @if(!$is_nil)
        @hasSection('declaration')
        <div class="declaration-section">
            <div class="declaration-text">
                @yield('declaration')
            </div>
        </div>
        @endif
        
        <div class="signature-section">
            @yield('signature_block')
        </div>
    @endif
</body>
</html>

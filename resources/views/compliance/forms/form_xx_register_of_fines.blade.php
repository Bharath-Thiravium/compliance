<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM XX - REGISTER OF FINES</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 10mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
        }
        
        .form-container {
            width: 100%;
            page-break-after: avoid;
        }
        
        /* Title Section */
        .form-header {
            text-align: center;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        
        .form-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .form-rule {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .form-subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        /* Establishment Details Block */
        .establishment-block {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        
        .establishment-row {
            display: table;
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        
        .establishment-row-label {
            display: table-cell;
            width: 35%;
            padding: 4px 6px;
            font-weight: bold;
            border-right: 1px solid #000;
            vertical-align: top;
            font-size: 9pt;
        }
        
        .establishment-row-value {
            display: table-cell;
            width: 65%;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9pt;
        }
        
        /* Column Number Row */
        .column-numbers {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            border: 1px solid #000;
        }
        
        .column-number-cell {
            display: table-cell;
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            height: 18px;
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 8pt;
            border: 1px solid #000;
        }
        
        .data-table thead {
            display: table-header-group;
        }
        
        .data-table tbody {
            display: table-row-group;
        }
        
        .data-table tfoot {
            display: table-footer-group;
        }
        
        .data-table th {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
            background-color: #fff;
            vertical-align: middle;
            word-wrap: break-word;
            font-size: 8pt;
            line-height: 1.2;
        }
        
        .data-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            font-size: 8pt;
        }
        
        .data-table .sl-no {
            text-align: center;
            width: 4%;
        }
        
        .data-table .col-1 { width: 4%; }
        .data-table .col-2 { width: 8%; }
        .data-table .col-3 { width: 8%; }
        .data-table .col-4 { width: 8%; }
        .data-table .col-5 { width: 10%; }
        .data-table .col-6 { width: 7%; }
        .data-table .col-7 { width: 8%; }
        .data-table .col-8 { width: 10%; }
        .data-table .col-9 { width: 10%; }
        .data-table .col-10 { width: 8%; }
        .data-table .col-11 { width: 8%; }
        .data-table .col-12 { width: 10%; }
        
        /* NIL Block */
        .nil-block {
            text-align: center;
            padding: 30px 20px;
            border: 1px solid #000;
            margin: 15px 0;
            font-weight: bold;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        
        /* Footer Section */
        .footer-section {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .footer-left {
            display: table-cell;
            width: 50%;
            text-align: left;
            font-size: 9pt;
        }
        
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 9pt;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin-top: 40px;
            display: inline-block;
        }
        
        .signature-label {
            font-size: 8pt;
            margin-top: 3px;
            font-weight: bold;
        }
        
        .seal-space {
            margin-top: 20px;
            font-size: 8pt;
            font-style: italic;
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .bold { font-weight: bold; }
        
        /* Print Settings */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .form-container {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Title Section -->
        <div class="form-header">
            <div class="form-title">FORM XX</div>
            <div class="form-rule">[See Rule 78(2)(d)]</div>
            <div class="form-subtitle">Register of Fines</div>
        </div>
        
        <!-- Establishment Details Block -->
        <div class="establishment-block">
            <div class="establishment-row">
                <div class="establishment-row-label">NAME AND ADDRESS OF CONTRACTOR :</div>
                <div class="establishment-row-value">{{ $data['contractor_name'] ?? '' }}</div>
            </div>
            
            <div class="establishment-row">
                <div class="establishment-row-label">NATURE AND LOCATION OF WORK :</div>
                <div class="establishment-row-value">{{ $data['nature_of_work'] ?? '' }}</div>
            </div>
            
            <div class="establishment-row">
                <div class="establishment-row-label">NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</div>
                <div class="establishment-row-value">{{ $data['establishment_name'] ?? '' }}</div>
            </div>
            
            <div class="establishment-row">
                <div class="establishment-row-label">NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</div>
                <div class="establishment-row-value">{{ $data['principal_employer'] ?? '' }}</div>
            </div>
            
            <div class="establishment-row">
                <div class="establishment-row-label">Month & Year :</div>
                <div class="establishment-row-value">{{ $data['month_year'] ?? '' }}</div>
            </div>
        </div>
        
        <!-- Column Number Row -->
        <div class="column-numbers">
            <div class="column-number-cell" style="width: 4%;">1</div>
            <div class="column-number-cell" style="width: 8%;">2</div>
            <div class="column-number-cell" style="width: 8%;">3</div>
            <div class="column-number-cell" style="width: 8%;">4</div>
            <div class="column-number-cell" style="width: 10%;">5</div>
            <div class="column-number-cell" style="width: 7%;">6</div>
            <div class="column-number-cell" style="width: 8%;">7</div>
            <div class="column-number-cell" style="width: 10%;">8</div>
            <div class="column-number-cell" style="width: 10%;">9</div>
            <div class="column-number-cell" style="width: 8%;">10</div>
            <div class="column-number-cell" style="width: 8%;">11</div>
            <div class="column-number-cell" style="width: 10%;">12</div>
        </div>
        
        <!-- Data Table -->
        @if(empty($data['fines'] ?? '') || count($data['fines'] ?? '') === 0)
            <div class="nil-block">
                Nil for the month of {{ $data['month_year'] ?? '' }}
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-1">SL No</th>
                        <th class="col-2">Name of workmen</th>
                        <th class="col-3">Father's/Husband's name</th>
                        <th class="col-4">Designation/Nature of employment</th>
                        <th class="col-5">Act/Omission for which fine imposed</th>
                        <th class="col-6">Date of offence</th>
                        <th class="col-7">Whether workmen showed cause against fine</th>
                        <th class="col-8">Name of person in whose presence employee's explanation was heard</th>
                        <th class="col-9">Wage period and wages payable</th>
                        <th class="col-10">Amount of fine imposed</th>
                        <th class="col-11">Date on which fine realised</th>
                        <th class="col-12">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['fines'] ?? '' as $index => $fine)
                    <tr>
                        <td class="col-1 text-center">{{ $index + 1 }}</td>
                        <td class="col-2">{{ $fine['workmen_name'] ?? '' }}</td>
                        <td class="col-3">{{ $fine['father_husband_name'] ?? '' }}</td>
                        <td class="col-4">{{ $fine['designation'] ?? '' }}</td>
                        <td class="col-5">{{ $fine['act_omission'] ?? '' }}</td>
                        <td class="col-6">{{ $fine['date_of_offence'] ?? '' }}</td>
                        <td class="col-7">{{ $fine['showed_cause'] ?? '' }}</td>
                        <td class="col-8">{{ $fine['person_present'] ?? '' }}</td>
                        <td class="col-9">{{ $fine['wage_period'] ?? '' }}</td>
                        <td class="col-10 text-right">{{ ($fine['amount_fine'] ?? '') ? number_format($fine['amount_fine'] ?? '', 2) : '' }}</td>
                        <td class="col-11">{{ $fine['date_realised'] ?? '' }}</td>
                        <td class="col-12">{{ $fine['remarks'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        
        <!-- Footer Section -->
        <div class="footer-section">
            <div class="footer-row">
                <div class="footer-left">
                    <span class="bold">*</span>Applicable only in case of damage/loss/fine
                </div>
                <div class="footer-right">
                    <div class="signature-line"></div>
                    <div class="signature-label">Seal Signature of the Contractor</div>
                    <div class="seal-space">[Space for seal image]</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

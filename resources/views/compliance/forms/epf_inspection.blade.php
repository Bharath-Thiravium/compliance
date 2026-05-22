<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>INSPECTION REGISTER</title>
    <style>
        @page {
            margin: 15mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .form-outer-border {
            border: 2px solid #000;
            padding: 15px;
        }
        .statutory-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .form-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .act-reference {
            font-size: 8pt;
            margin: 3px 0;
            font-style: italic;
        }
        .rule-reference {
            font-size: 8pt;
            margin: 3px 0;
        }
        .establishment-info {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
            font-size: 8pt;
        }
        .establishment-info td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .establishment-info .est-label {
            font-weight: bold;
            width: 160px;
            white-space: nowrap;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }
        thead { display: table-header-group; }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .nil-declaration {
            text-align: center;
            padding: 30px;
            border: 1px solid #000;
            margin: 20px 0;
            font-weight: bold;
            font-size: 10pt;
        }
        .col-sno      { width: 5%; }
        .col-date     { width: 12%; }
        .col-authority{ width: 20%; }
        .col-ref      { width: 13%; }
        .col-remarks  { width: 32%; }
        .col-sign     { width: 18%; }
        .signature-block {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
            display: inline-block;
        }
        .signature-label {
            font-size: 8pt;
            margin-top: 5px;
        }
        .declaration-text {
            margin: 20px 0;
            font-size: 8pt;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="form-outer-border">

    <div class="statutory-header">
        <div class="form-title">INSPECTION REGISTER</div>
        <div class="act-reference">[Under Section 17 of the Employees Provident Funds and Miscellaneous Provisions Act, 1952]</div>
        <div class="rule-reference">[See Rule 44 of the Employees Provident Funds Scheme, 1952]</div>
    </div>

    <table class="establishment-info">
        <tr>
            <td class="est-label">Name of Establishment:</td>
            <td>{{ is_array($header['tenant'] ?? null) ? $header['tenant']['name'] : $header['tenant'] }}</td>
        </tr>
        @if(isset($header['branch']))
        <tr>
            <td class="est-label">Address:</td>
            <td>{{ $header['branch']['address'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="est-label">Code No:</td>
            <td>{{ $header['branch']['pf_code'] ?? 'N/A' }}</td>
        </tr>
        @endif
        <tr>
            <td class="est-label">Period:</td>
            <td>{{ $header['period'] ?? 'N/A' }}</td>
        </tr>
    </table>

    @if($is_nil)
        <div class="nil-declaration">NIL – No records during this period</div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-sno">S.No.</th>
                    <th class="col-date">Date of<br>Inspection</th>
                    <th class="col-authority">Name and Designation<br>of Inspecting Officer</th>
                    <th class="col-ref">Reference<br>No.</th>
                    <th class="col-remarks">Remarks / Observations</th>
                    <th class="col-sign">Signature of<br>Inspecting Officer</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row['inspection_date'] ?? 'N/A' }}</td>
                    <td>{{ $row['authority'] ?? 'N/A' }}</td>
                    <td>{{ $row['reference'] ?? 'N/A' }}</td>
                    <td>{{ $row['remarks'] ?? 'N/A' }}</td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-block">
            <div class="declaration-text">
                Certified that the above inspection records are maintained as per statutory requirements.
            </div>
            <div class="signature-grid">
                <div class="signature-left">
                    <div class="signature-label">Date: _______________</div>
                    <div class="signature-label" style="margin-top:10px;">Place: _______________</div>
                </div>
                <div class="signature-right">
                    @if(isset($batch_signature) && $batch_signature)
                        <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" style="height: 60px; margin-bottom: 10px;">
                        <div class="signature-label">
                            <strong>{{ $batch_signature['signatory_name'] }}</strong><br>
                            {{ $batch_signature['signatory_designation'] }}
                        </div>
                    @else
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            <strong>Signature of Employer/Manager</strong><br>
                            Name: _______________<br>
                            Designation: _______________<br>
                            (Seal)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
</body>
</html>

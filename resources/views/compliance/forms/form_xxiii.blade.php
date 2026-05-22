<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XXIII - Register of Overtime</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 1px;
        }
        .form-container {
            border: 2px solid #000;
            margin: 1px auto;
            width: 99%;
        }
        .form-header {
            text-align: center;
            padding: 2px 0;
            line-height: 1.4;
        }
        .form-title {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.4;
        }
        .form-rule {
            font-size: 8px;
            line-height: 1.4;
        }
        .form-subtitle {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
        }
        .establishment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        .establishment-table td {
            border: 1px solid black;
            padding: 2px 3px;
            font-size: 7.5px;
            line-height: 1.4;
            vertical-align: middle;
        }
        .establishment-table td:first-child {
            font-size: 7.5px;
            font-weight: bold;
            line-height: 1.4;
            width: 35%;
            vertical-align: middle;
        }
        .month-year-row {
            display: flex;
            align-items: center;
            font-size: 7.5px;
            padding: 2px 3px;
            line-height: 1.4;
        }
        .month-year-row span:first-child {
            font-weight: bold;
            width: 35%;
        }
        .month-year-row span:last-child {
            width: 65%;
        }
        .column-numbers {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-bottom: 0;
            table-layout: fixed;
        }
        .column-numbers td {
            border: 1px solid black;
            padding: 1px 0;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            line-height: 1.4;
            vertical-align: middle;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px 3px;
            text-align: left;
            vertical-align: middle;
            font-size: 7px;
            line-height: 1.4;
        }
        .register-table thead th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            vertical-align: middle;
            text-align: center;
        }
        .col-1 { width: 4%; text-align: center; }
        .col-2 { width: 8%; }
        .col-3 { width: 8%; }
        .col-4 { width: 5%; text-align: center; }
        .col-5 { width: 9%; }
        .col-6 { width: 9%; }
        .col-7 { width: 9%; }
        .col-8 { width: 8%; text-align: right; }
        .col-9 { width: 8%; text-align: right; }
        .col-10 { width: 8%; text-align: right; }
        .col-11 { width: 8%; }
        .col-12 { width: 9%; }
        .no-overtime-row td {
            text-align: center;
            font-weight: normal;
            font-size: 7px;
            padding: 6px 4px;
            border: 1px solid #000;
            vertical-align: middle;
            line-height: 1.4;
        }
        .footer-section {
            margin-top: 2px;
            padding: 2px 3px;
            font-size: 7.5px;
            line-height: 1.4;
        }
        .footer-left {
            font-size: 7.5px;
        }
        .signature-section {
            position: relative;
            height: 80px;
        }
        .signature-label {
            position: absolute;
            right: 10px;
            bottom: 8px;
            font-size: 7.5px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="form-title">FORM XXIII</div>
            <div class="form-rule">[See Rule 78 (1) (a) (iii)]</div>
            <div class="form-subtitle">Register of Overtime</div>
        </div>

        <table class="establishment-table">
            <tr>
                <td>NAME AND ADDRESS OF CONTRACTOR :</td>
                <td>{{ $header['contractor_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>NAME AND LOCATION OF WORK :</td>
                <td>{{ $header['work_location'] ?? '' }}</td>
            </tr>
            <tr>
                <td>NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</td>
                <td>{{ $header['establishment_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</td>
                <td>{{ $header['principal_employer'] ?? '' }}</td>
            </tr>
        </table>

        <div class="month-year-row">
            <span>Month & Year:</span>
            <span>{{ $header['month_year'] ?? '' }}</span>
        </div>

        <table class="column-numbers">
            <tr>
                <td style="width: 4%;">1</td>
                <td style="width: 8%;">2</td>
                <td style="width: 8%;">3</td>
                <td style="width: 5%;">4</td>
                <td style="width: 9%;">5</td>
                <td style="width: 9%;">6</td>
                <td style="width: 9%;">7</td>
                <td style="width: 8%;">8</td>
                <td style="width: 8%;">9</td>
                <td style="width: 8%;">10</td>
                <td style="width: 8%;">11</td>
                <td style="width: 9%;">12</td>
            </tr>
        </table>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-1">Sl. No.</th>
                    <th class="col-2">Name of workman</th>
                    <th class="col-3">Father's/Husband's name</th>
                    <th class="col-4">Sex</th>
                    <th class="col-5">Designation / Nature of employment</th>
                    <th class="col-6">Dates on which overtime worked</th>
                    <th class="col-7">Total overtime worked or production in case of piece rates</th>
                    <th class="col-8">Normal rate of wages</th>
                    <th class="col-9">Overtime rate of wages</th>
                    <th class="col-10">Overtime earnings</th>
                    <th class="col-11">Date on which overtime wages paid</th>
                    <th class="col-12">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($rows))
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="col-1">{{ $index + 1 }}</td>
                            <td class="col-2">{{ $row['name'] ?? 'NIL' }}</td>
                            <td class="col-3">{{ $row['father_name'] ?? 'NIL' }}</td>
                            <td class="col-4">{{ $row['sex'] ?? 'NIL' }}</td>
                            <td class="col-5">{{ $row['designation'] ?? 'NIL' }}</td>
                            <td class="col-6">{{ $row['overtime_dates'] ?? 'NIL' }}</td>
                            <td class="col-7">{{ $row['total_overtime'] ?? 'NIL' }}</td>
                            <td class="col-8">{{ $row['normal_rate'] ?? 'NIL' }}</td>
                            <td class="col-9">{{ $row['overtime_rate'] ?? 'NIL' }}</td>
                            <td class="col-10">{{ $row['overtime_earnings'] ?? 'NIL' }}</td>
                            <td class="col-11">{{ $row['payment_date'] ?? 'NIL' }}</td>
                            <td class="col-12">{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12" style="height: 18px;"></td>
                    </tr>
                    <tr class="no-overtime-row">
                        <td colspan="12">NO BODY IN THE ORGANIZATION HAS WORKED OVER TIME FOR THE MONTH OF {{ strtoupper($header['month_year'] ?? '') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-left">*Applicable only in case of damage/loss/fine</div>
        </div>
        <div class="signature-section">
            <div class="signature-label">Seal Signature of The Contractor</div>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM B - Register of Fines</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8px;
            background: #fff;
            padding: 8px;
        }
        .page-wrap {
            width: 740px;
            margin: 0 auto;
        }
        .form-container {
            border: 1.5px solid black;
            padding: 8px 10px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 7px;
            font-size: 9px;
            line-height: 1.4;
        }
        .header-title {
            font-weight: bold;
        }
        .establishment-field {
            margin-bottom: 6px;
            font-size: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .establishment-label {
            font-weight: bold;
            white-space: nowrap;
        }
        .establishment-value {
            flex: 1;
            border-bottom: 1px solid black;
            min-height: 12px;
            padding-left: 3px;
            font-size: 8px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.2;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
        }
        .register-table td {
            text-align: left;
            height: 18px;
        }
        .text-center { text-align: center !important; }

        /* Column widths — total = 100% */
        .col-sl     { width: 5%; }
        .col-name   { width: 11%; }
        .col-father { width: 13%; }
        .col-act    { width: 14%; }
        .col-cause  { width: 17%; }
        .col-wages  { width: 10%; }
        .col-amount { width: 10%; }
        .col-date   { width: 8%; }
        .col-sign   { width: 7%; }
        .col-remarks{ width: 5%; }

        @media print {
            body { padding: 0; }
            .page-wrap { width: 100%; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="form-container">

        <!-- Header -->
        <div class="form-header">
            <div>Tamil Nadu Shops And Establishments Rules</div>
            <div class="header-title">FORM B</div>
            <div>See Rule 11(3)(a)</div>
            <div class="header-title">Register of fines</div>
        </div>

        <!-- Establishment Field -->
        <div class="establishment-field">
            <span class="establishment-label">Establishment:</span>
            <span class="establishment-value">{{ $header['establishment_name'] ?? $establishment_name ?? '' }}</span>
        </div>

        <!-- Fines Register Table -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sl">Sl. No.</th>
                    <th class="col-name">Name</th>
                    <th class="col-father">Father's name or Husband's name</th>
                    <th class="col-act">Act or commission for which fine imposed</th>
                    <th class="col-cause">Whether workman showed cause against fine or not and if so, date on which cause was shown</th>
                    <th class="col-wages">Total wages for the wage-period in which fine imposed</th>
                    <th class="col-amount">Amount of, and date on which fine imposed</th>
                    <th class="col-date">Date on which fine realized</th>
                    <th class="col-sign">Signature or thumb-impression of person employed</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
                <tr>
                    <th class="col-sl">(1)</th>
                    <th class="col-name">(2)</th>
                    <th class="col-father">(3)</th>
                    <th class="col-act">(4)</th>
                    <th class="col-cause">(5)</th>
                    <th class="col-wages">(6)</th>
                    <th class="col-amount">(7)</th>
                    <th class="col-date">(8)</th>
                    <th class="col-sign">(9)</th>
                    <th class="col-remarks">(10)</th>
                </tr>
            </thead>
            <tbody>
                @if($is_nil)
                <tr>
                    <td colspan="10" class="text-center">Nil</td>
                </tr>
                @else
                @foreach($rows as $index => $row)
                <tr>
                    <td class="col-sl text-center">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $row['employee_name'] }}</td>
                    <td class="col-father">{{ $row['father_name'] }}</td>
                    <td class="col-act">{{ $row['reason'] }}</td>
                    <td class="col-cause">{{ $row['cause'] }}</td>
                    <td class="col-wages text-center">{{ number_format($row['wages'], 2) }}</td>
                    <td class="col-amount text-center">{{ number_format($row['fine_amount'], 2) }}<br>{{ $row['fine_date'] }}</td>
                    <td class="col-date text-center">{{ $row['realized_date'] }}</td>
                    <td class="col-sign"></td>
                    <td class="col-remarks">{{ $row['remarks'] }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>

    </div>
</div>
</body>
</html>

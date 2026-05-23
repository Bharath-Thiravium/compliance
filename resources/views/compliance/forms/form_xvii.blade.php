<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XVII - Register of Wages</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 12px;
        }
        .form-container {
            border: 2px solid black;
            padding: 10px;
            margin: 0 auto;
            width: 99%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
        }
        .form-header div {
            margin: 2px 0;
        }
        .header-title {
            font-weight: bold;
            font-size: 12px;
        }
        .header-subtitle {
            font-size: 9px;
        }
        .header-section {
            display: flex;
            margin-bottom: 8px;
            font-size: 9px;
            gap: 2px;
        }
        .header-left,
        .header-right {
            flex: 1;
            border: 1px solid black;
            padding: 4px;
        }
        .header-row {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .header-label {
            font-weight: bold;
            display: inline;
        }
        .header-value {
            display: inline;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 8px;
            margin-bottom: 10px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            line-height: 1.1;
            height: 20px;
        }
        .register-table td {
            height: 18px;
        }
        .col-sl { width: 2.5%; }
        .col-name { width: 7%; text-align: left; }
        .col-serial { width: 5%; }
        .col-designation { width: 6%; text-align: left; }
        .col-days { width: 4%; }
        .col-unit { width: 4%; }
        .col-rate { width: 5%; text-align: right; }
        .col-basic { width: 5%; text-align: right; }
        .col-da { width: 5%; text-align: right; }
        .col-ot { width: 5%; text-align: right; }
        .col-other { width: 5%; text-align: right; }
        .col-total { width: 5%; text-align: right; }
        .col-esi { width: 4%; text-align: right; }
        .col-pf { width: 4%; text-align: right; }
        .col-pt { width: 4%; text-align: right; }
        .col-deduct-total { width: 4%; text-align: right; }
        .col-net { width: 5%; text-align: right; }
        .col-signature { width: 5%; }
        .col-initial { width: 5%; }
        .signature-space {
            margin-top: 20px;
        }
        .signature-row{
            display:flex;
            justify-content:space-between;
            margin-top:30px;
            font-size:9px;
            page-break-inside: avoid;
        }

        .left-sign{
            text-align:left;
        }

        .right-sign{
            text-align:right;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XVII</div>
            <div class="header-title">REGISTER OF WAGES</div>
            <div class="header-subtitle">[Rule 78(1)(a)(i) of Contract Labour (Regulation & Abolition) Central Rules]</div>
        </div>

        <div class="header-section">
            <div class="header-left">
                <div class="header-row">
                    <span class="header-label">Name and address of Contractor:</span><br>
                    <span class="header-value">{{ $header['contractor_name'] ?? '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Nature and location of work:</span><br>
                    <span class="header-value">{{ $header['work_nature'] ?? '' }}{{ !empty($header['work_location']) ? ' - ' . $header['work_location'] : '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Wage period:</span>
                    <span class="header-value">{{ $header['wage_period'] ?? '' }}</span>
                </div>
            </div>
            <div class="header-right">
                <div class="header-row">
                    <span class="header-label">Name and address of Establishment in/under which contract is carried on:</span><br>
                    <span class="header-value">{{ $header['establishment_name'] ?? '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Name and address of Principal Employer:</span><br>
                    <span class="header-value">{{ $header['principal_employer'] ?? '' }}</span>
                </div>
            </div>
        </div>

        <table class="register-table">
            <thead>

                <tr>
                    <th rowspan="2" class="col-sl">S.No</th>
                    <th rowspan="2" class="col-name">Name of Workman</th>
                    <th rowspan="2" class="col-serial">Serial No in Register of Workmen</th>
                    <th rowspan="2" class="col-designation">Designation / Nature of Work</th>
                    <th rowspan="2" class="col-days">No. of Days Worked</th>
                    <th rowspan="2" class="col-unit">Unit of Work Done</th>
                    <th rowspan="2" class="col-rate">Daily Rate of Wages / Piece Rate</th>

                    <th rowspan="2" class="col-basic">Basic Wages</th>
                    <th rowspan="2" class="col-da">Dearness Allowance</th>
                    <th rowspan="2" class="col-ot">Overtime</th>
                    <th rowspan="2" class="col-other">Other Cash Payments</th>

                    <th rowspan="2" class="col-total">Total</th>

                    <th colspan="4">Deductions (if any)</th>

                    <th rowspan="2" class="col-net">Net Amount Paid</th>
                    <th rowspan="2" class="col-signature">Signature / Thumb Impression of Workman</th>
                    <th rowspan="2" class="col-initial">Initial of Contractor or his representative</th>
                </tr>

                <tr>
                    <th class="col-esi">ESI</th>
                    <th class="col-pf">PF</th>
                    <th class="col-pt">PT</th>
                    <th class="col-deduct-total">Total</th>
                </tr>

            </thead>
            <tbody>
                @forelse($rows ?? [] as $index => $row)
                <tr>
                    <td class="col-sl">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $row['name'] ?? '' }}</td>
                    <td class="col-father">{{ $row['father_name'] ?? '' }}</td>
                    <td class="col-designation">{{ $row['designation'] ?? '' }}</td>
                    <td class="col-days">{{ $row['days_worked'] ?? '' }}</td>
                    <td class="col-unit">{{ $row['unit_work'] ?? '' }}</td>
                    <td class="col-rate">{{ ($row['daily_rate'] ?? '') ? number_format($row['daily_rate'], 2) : '' }}</td>
                    <td class="col-basic">{{ ($row['basic_wages'] ?? '') ? number_format($row['basic_wages'], 2) : '' }}</td>
                    <td class="col-da">{{ ($row['da'] ?? '') ? number_format($row['da'], 2) : '' }}</td>
                    <td class="col-ot">{{ ($row['overtime'] ?? '') ? number_format($row['overtime'], 2) : '' }}</td>
                    <td class="col-other">{{ ($row['other_cash'] ?? '') ? number_format($row['other_cash'], 2) : '' }}</td>
                    <td class="col-total">{{ ($row['gross_salary'] ?? '') ? number_format($row['gross_salary'], 2) : '' }}</td>
                    <td class="col-esi">{{ ($row['esi'] ?? '') ? number_format($row['esi'], 2) : '' }}</td>
                    <td class="col-pf">{{ ($row['pf'] ?? '') ? number_format($row['pf'], 2) : '' }}</td>
                    <td class="col-pt">{{ ($row['pt'] ?? '') ? number_format($row['pt'], 2) : '' }}</td>
                    <td class="col-deduct-total">{{ ($row['total_deductions'] ?? '') ? number_format($row['total_deductions'], 2) : '' }}</td>
                    <td class="col-net">{{ ($row['net_amount'] ?? '') ? number_format($row['net_amount'], 2) : '' }}</td>
                    <td class="col-signature"></td>
                    <td class="col-initial"></td>
                </tr>
                @empty
                    <tr>
                        <td colspan="19" style="text-align:center;">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

       <div class="signature-space">
            <div class="signature-row">
                <div class="left-sign">Signature of the Site Engineer</div>
                <div class="right-sign">Signature of the Contractor with Seal</div>
            </div>
        </div>
    </div>
</body>
</html>

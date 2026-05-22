<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 12 - Register of Adult Workers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 20px;
            font-size: 11px;
        }
        .form-container {
            border: 2px solid black;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-header {
            text-align: center;
            padding: 15px;
            border-bottom: 2px solid black;
            font-size: 12px;
            font-weight: bold;
        }
        .form-header div {
            margin: 3px 0;
        }
        .header-title {
            font-weight: bold;
            font-size: 14px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            text-align: center;
            padding: 8px;
        }
        .register-table td {
            min-height: 30px;
        }
        .col-sl { width: 4%; text-align: center; }
        .col-name { width: 28%; }
        .col-father { width: 12%; }
        .col-nature { width: 12%; }
        .col-group { width: 6%; text-align: center; }
        .col-relay { width: 6%; text-align: center; }
        .col-cert { width: 10%; text-align: center; }
        .col-token { width: 10%; text-align: center; }
        .col-remarks { width: 12%; }
        .footer-section {
            padding: 20px;
            border-top: 2px solid black;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            min-height: 100px;
        }
        .footer-left {
            flex: 1;
        }
        .footer-right {
            flex: 1;
            text-align: right;
        }
        .signature-label {
            margin-top: 60px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 12</div>
            <div>(Prescribed under Rule 80)</div>
            <div class="header-title">Register of adult workers</div>
        </div>

        <!-- Register Table -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sl">(1)</th>
                    <th class="col-name">(2)</th>
                    <th class="col-father">(3)</th>
                    <th class="col-nature">(4)</th>
                    <th class="col-group">(5)</th>
                    <th class="col-relay">(6)</th>
                    <th class="col-cert">(7)</th>
                    <th class="col-token">(8)</th>
                    <th class="col-remarks">(9)</th>
                </tr>
                <tr>
                    <th class="col-sl">Sl. No.</th>
                    <th class="col-name">Name & residential address</th>
                    <th class="col-father">Father's name</th>
                    <th class="col-nature">Nature of work</th>
                    <th class="col-group">Group</th>
                    <th class="col-relay">Relay</th>
                    <th class="col-cert">Certificate No.</th>
                    <th class="col-token">Token No.</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="col-sl">{{ $index + 1 }}</td>
                            <td class="col-name">{{ $row['employee_name'] ?? '' }}</td>
                            <td class="col-father">{{ $row['father_name'] ?? '' }}</td>
                            <td class="col-nature">{{ $row['designation'] ?? '' }}</td>
                            <td class="col-group">{{ $row['group'] ?? '' }}</td>
                            <td class="col-relay">{{ $row['relay'] ?? '' }}</td>
                            <td class="col-cert">{{ $row['certificate_no'] ?? '' }}</td>
                            <td class="col-token">{{ $row['token_no'] ?? '' }}</td>
                            <td class="col-remarks">{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" style="text-align:center;">No records found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                <div>Date: ___________</div>
            </div>
            <div class="footer-right">
                <div class="signature-label">(Signed) Manager/Occupier</div>
            </div>
        </div>
    </div>
</body>
</html>

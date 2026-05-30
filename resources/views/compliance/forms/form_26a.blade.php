<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 26-A - Register of Dangerous Occurrences</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 12px;
            font-size: 9px;
        }
        .form-container {
            border: 1px solid black;
            padding: 10px 0;
            margin: 0 auto;
            width: 99%;
        }
        .form-inner {
            padding: 0 10px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .form-header div { margin: 2px 0; }
        .header-title { font-weight: bold; }
        .factory-details { margin-bottom: 8px; font-size: 9px; }
        .detail-row { margin-bottom: 8px; }
        .detail-label {
            font-weight: bold;
            font-size: 9px;
            display: block;
            margin-bottom: 1px;
        }
        .detail-line {
            display: block;
            border-bottom: 1px solid black;
            min-height: 14px;
            padding-left: 3px;
            width: 100%;
            font-size: 9px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.2;
        }
        .register-table th { font-weight: bold; background-color: #fff; line-height: 1.1; }
        .register-table td { text-align: left; }
        .text-center { text-align: center; }
        .footer-section {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        .footer-left { flex: 1; }
        .footer-right { flex: 1; text-align: right; }
        .signature-label { margin-top: 30px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-inner">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 26-A</div>
            <div>(Prescribed under Rule 104)</div>
            <div class="header-title">Register of dangerous occurrences</div>
        </div>

        <!-- Factory Details -->
        <div class="factory-details">
            <div class="detail-row">
                <span class="detail-label">Name and address of the factory</span>
                <span class="detail-line" style="border-bottom:none;">{{ $header['factory_name'] ?? '' }} {{ $header['factory_address'] ?? '' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Registration number of the factory</span>
                <span class="detail-line" style="border-bottom:none;">{{ $header['registration_number'] ?? '' }}</span>
            </div>
        </div>
        </div>

        <!-- Dangerous Occurrences Register Table -->
        <table class="register-table">
            <colgroup>
                <col style="width:6%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:16%">
                <col style="width:22%">
                <col style="width:18%">
                <col style="width:8%">
            </colgroup>
            <thead>
                <tr>
                    <th>Calendar year</th>
                    <th>Running Sl. No. of the dangerous occurrence in the factory for the calendar year</th>
                    <th>Date and hour of dangerous occurrence</th>
                    <th>Date of despatch of report in Form 18-A</th>
                    <th>Exact place in the factory (branch, department, plant, equipment, etc.) where the dangerous occurrence took place</th>
                    <th>A full clear description of the dangerous occurrence, the damage caused and steps taken to arrest further damage or danger</th>
                    <th>Details of ultimate damage or loss with value thereof and of repair, replacement, reconstruction, etc., with cost thereof</th>
                    <th>Remarks and initials of the Manager</th>
                </tr>
                <tr>
                    <th class="text-center">(1)</th>
                    <th class="text-center">(2)</th>
                    <th class="text-center">(3)</th>
                    <th class="text-center">(4)</th>
                    <th class="text-center">(5)</th>
                    <th class="text-center">(6)</th>
                    <th class="text-center">(7)</th>
                    <th class="text-center">(8)</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                    <tr>
                        <td>{{ $row['calendar_year'] ?? '' }}</td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['date_and_hour'] ?? '' }}</td>
                        <td>{{ $row['report_date'] ?? '' }}</td>
                        <td>{{ $row['place'] ?? '' }}</td>
                        <td>{{ $row['description'] ?? '' }}</td>
                        <td>{{ $row['damage_details'] ?? '' }}</td>
                        <td>{{ $row['remarks'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="form-inner">
        <div class="footer-section">
            <div class="footer-left">
                <div>Date:</div>
            </div>
            <div class="footer-right">
                <div class="signature-label"><strong>Signature of Manager</strong></div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>

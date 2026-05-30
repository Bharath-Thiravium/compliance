<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 26 - Register of Accidents</title>
    <style>
        * { margin: 0; padding: 0; }
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
            margin-bottom: 18px;
            font-size: 11px;
        }
        .form-header div { margin: 2px 0; }
        .header-title { font-weight: bold; font-size: 14px; }
        .header-subtitle { font-weight: bold; font-size: 11px; }
        .factory-details { margin-bottom: 10px; font-size: 9px; }
        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .detail-label {
            white-space: nowrap;
            margin-right: 4px;
            font-size: 9px;
            flex-shrink: 0;
        }
        .detail-line {
            flex: 1;
            border-bottom: 1px solid black;
            min-height: 13px;
            padding-left: 3px;
            font-size: 9px;
        }
        .accidents-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 5.5px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .accidents-table th,
        .accidents-table td {
            border: 1px solid black;
            padding: 1px 1px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.1;
        }
        .accidents-table th { font-weight: bold; background-color: #fff; }
        .accidents-table td { text-align: left; }
        .text-center { text-align: center !important; }
        .footer-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        .footer-right { text-align: right; }
        .signature-label { margin-top: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-inner">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 26</div>
            <div>(Prescribed under Rule 104)</div>
            <br>
            <div class="header-subtitle">Register of Accidents</div>
        </div>

        <!-- Factory Details -->
        <div class="factory-details">
            <div class="detail-row">
                <span class="detail-label">Name and address of the factory</span>
                <span class="detail-line" style="border-bottom:none;">{{ $header['factory_name'] ?? '' }} {{ $header['factory_address'] ?? '' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Calendar year</span>
                <span class="detail-line" style="border-bottom:none;">{{ $header['calendar_year'] ?? '' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Registration number of the factory</span>
                <span class="detail-line">{{ $header['registration_number'] ?? '' }}</span>
            </div>
        </div>
        </div>

        <!-- Accidents Register Table: All 14 columns -->
        <table class="accidents-table">
            <colgroup>
                <col style="width:6%">
                <col style="width:7%">
                <col style="width:7%">
                <col style="width:8%">
                <col style="width:8%">
                <col style="width:7%">
                <col style="width:6%">
                <col style="width:6%">
                <col style="width:8%">
                <col style="width:7%">
                <col style="width:6%">
                <col style="width:6%">
                <col style="width:7%">
                <col style="width:7%">
            </colgroup>
            <thead>
                <tr>
                    <th>Running Sl. No. of the accident for the calendar year</th>
                    <th>Date and hour of accident</th>
                    <th>Name and designation of person injured</th>
                    <th>Exact place in the factory (Branch, Department, Machine, etc) where the accident occurred</th>
                    <th>A full clear description of how the accident occurred</th>
                    <th>Nature, extent, location, etc, of injury received</th>
                    <th>Date of despatch of report on Form 18</th>
                    <th>Date of return to work of persons injured</th>
                    <th>Date of despatch of report to the Inspector of the date of return to work of the person injured</th>
                    <th>Date/s of despatch of subsequent report/s in Form 18B</th>
                    <th>Number of days the person injured was away from work</th>
                    <th>Number of man-days lost</th>
                    <th>Details of disablement and loss of earning capacity, if any</th>
                    <th>Remarks and initials of Manager</th>
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
                    <th class="text-center">(9)</th>
                    <th class="text-center">(10)</th>
                    <th class="text-center">(11)</th>
                    <th class="text-center">(12)</th>
                    <th class="text-center">(13)</th>
                    <th class="text-center">(14)</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($rows) && count($rows) > 0)
                    @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['running_sl_no'] ?? '' }}</td>
                        <td>{{ $row['date_and_hour_of_accident'] ?? '' }}</td>
                        <td>{{ $row['name_and_designation_of_person_injured'] ?? '' }}</td>
                        <td>{{ $row['exact_place_of_accident'] ?? '' }}</td>
                        <td>{{ $row['full_description_of_accident'] ?? '' }}</td>
                        <td>{{ $row['nature_extent_location_of_injury'] ?? '' }}</td>
                        <td>{{ $row['date_of_despatch_of_report_form_18'] ?? '' }}</td>
                        <td>{{ $row['date_of_return_to_work'] ?? '' }}</td>
                        <td>{{ $row['date_of_despatch_of_return_to_work_report'] ?? '' }}</td>
                        <td>{{ $row['date_of_despatch_of_subsequent_reports_form_18b'] ?? '' }}</td>
                        <td>{{ $row['number_of_days_away_from_work'] ?? '' }}</td>
                        <td>{{ $row['number_of_man_days_lost'] ?? '' }}</td>
                        <td>{{ $row['details_of_disablement_and_loss_of_earning_capacity'] ?? '' }}</td>
                        <td>{{ $row['remarks_and_initials_of_manager'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="form-inner">
        <div class="footer-section">
            <div>Date:</div>
            <div class="footer-right">
                <div class="signature-label">Signature of Manager</div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>

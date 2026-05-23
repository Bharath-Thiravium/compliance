<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XVI - Muster Roll</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 2px;
        }
        .form-container {
            border: 2px solid black;
            padding: 2px;
            margin: 0 auto;
            width: 98%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 2px;
            font-size: 6px;
        }
        .form-header div {
            margin: 0;
        }
        .header-title {
            font-weight: bold;
            font-size: 7px;
        }
        .header-subtitle {
            font-size: 5.5px;
        }
        .header-section {
            display: flex;
            margin-bottom: 2px;
            font-size: 5.5px;
        }
        .header-left {
            flex: 1;
            border: 1px solid black;
            padding: 1px;
        }
        .header-right {
            flex: 1;
            border: 1px solid black;
            padding: 1px;
            margin-left: 1px;
        }
        .header-row {
            margin-bottom: 0;
            line-height: 1.1;
        }
        .header-label {
            font-weight: bold;
            display: inline;
        }
        .header-value {
            display: inline;
        }
        .column-numbers {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-bottom: 0;
            font-size: 9px;
        }
        .column-numbers td {
            border: 1px solid black;
            padding: 2px 1px;
            text-align: center;
            font-weight: bold;
            height: 16px;
        }
        .muster-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 5.5px;
            table-layout: auto;
        }
        .muster-table th,
        .muster-table td {
            border: 1px solid black;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            font-size: 5.5px;
        }
        .muster-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            line-height: 1.0;
            height: 11px;
        }
        .muster-table td {
            height: 9px;
        }
        .col-sl { width: 2%; }
        .col-name { width: auto; text-align: left; font-size: 5.5px; white-space: nowrap; }
        .col-father { width: auto; text-align: left; white-space: nowrap; }
        .col-sex { width: 1.5%; }
        .col-date { width: 1.5%; }
        .col-remarks { width: 2%; text-align: left; }
        .signature-space {
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-subtitle">Contract Labour Act</div>
            <div class="header-title">FORM XVI</div>
            <div class="header-subtitle">[See rule 78(2)(a)]</div>
            <div class="header-title">Muster Roll</div>
        </div>

        <div class="header-section">
            <div class="header-left">
                <div class="header-row">
                    <span class="header-label">Name and address of contractor:</span><br>
                    <span class="header-value">{{ $contractor_name ?? '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Name and address of establishment in/under which contract is carried on:</span><br>
                    <span class="header-value">{{ $establishment_name ?? '' }}</span>
                </div>
            </div>
            <div class="header-right">
                <div class="header-row">
                    <span class="header-label">Name and address of principal employer:</span><br>
                    <span class="header-value">{{ $principal_employer ?? '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Nature and location of work:</span><br>
                    <span class="header-value">{{ $work_nature ?? '' }}{{ !empty($work_location) ? ' - ' . $work_location : '' }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">Wage period:</span>
                    <span class="header-value">{{ $wage_period ?? 'Monthly' }}</span>
                </div>
            </div>
        </div>


        <table class="muster-table">
            <thead>
                <tr>
                    <th colspan="1">1</th>
                    <th colspan="1">2</th>
                    <th colspan="1">3</th>
                    <th colspan="1">4</th>
                    <th colspan="31">5</th>
                    <th colspan="1">6</th>
                </tr>

                <tr>
                    <th rowspan="2" class="col-sl">Sl. No</th>
                    <th rowspan="2" class="col-name">Name of workman</th>
                    <th rowspan="2" class="col-father">Father's/Husband's name</th>
                    <th rowspan="2" class="col-sex">Sex</th>

                    <th colspan="31">Dates</th>

                    <th rowspan="2" class="col-remarks">Remarks</th>
                </tr>

                <tr>
                    @for($day = 1; $day <= 31; $day++)
                        <th class="col-date">{{ str_pad($day,2,'0',STR_PAD_LEFT) }}</th>
                    @endfor
                </tr>

            </thead>
            <tbody>
                @if(isset($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                    <tr>
                        <td class="col-sl">{{ $index + 1 }}</td>
                        <td class="col-name">{{ $row['name'] ?? '' }}</td>
                        <td class="col-father">{{ $row['father_name'] ?? '' }}</td>
                        <td class="col-sex">{{ $row['sex'] ?? '' }}</td>
                        @for($day = 1; $day <= 31; $day++)
                            <td class="col-date">{{ $row['day_' . $day] ?? '' }}</td>
                        @endfor
                        <td class="col-remarks"></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="37" style="text-align:center;">No records found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="signature-space"></div>
    </div>
</body>
</html>

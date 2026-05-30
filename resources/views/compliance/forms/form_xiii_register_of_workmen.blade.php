<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XIII - Register of Workmen Employed by Contractor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        html, body {
            transform: scale(0.75);
            transform-origin: top left;
            width: 133.33%;
            height: 133.33%;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 2px;
        }
        .form-container {
            border: 2px solid black;
            padding: 4px;
            margin: 2px auto;
            width: 88%;
            box-sizing: border-box;
        }
        .form-header {
            text-align: center;
            line-height: 1.0;
            margin-bottom: 3px;
        }
        .form-header div {
            margin: 0px 0;
        }
        .header-title {
            font-weight: bold;
            font-size: 11px;
        }
        .header-rule {
            font-size: 7px;
        }
        .info-section {
            margin-bottom: 2px;
            font-size: 7px;
        }
        .info-row {
            margin-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 1px;
        }
        .info-value {
            margin-left: 0;
            padding-left: 0;
            line-height: 1.2;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 7px;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 1px 2px;
            font-size: 7px;
            line-height: 1.0;
            text-align: center;
            vertical-align: middle;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
        }
        .register-table td {
            height: 12px;
            font-size: 6px;
        }
        .col-1 { width: 4%; }
        .col-2 { width: 11%; text-align: left; }
        .col-3 { width: 6%; }
        .col-4 { width: 9%; text-align: left; }
        .col-5 { width: 10%; text-align: left; }
        .col-6 { width: 13%; text-align: left; }
        .col-7 { width: 7%; text-align: left; }
        .col-8 { width: 10%; }
        .col-9 { width: 8%; }
        .col-10 { width: 9%; }
        .col-11 { width: 9%; text-align: left; }
        .col-12 { width: 4%; }
        .signature-space {
            margin-top: 2px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XIII</div>
            <div class="header-rule">[See Rule 75]</div>
            <div class="header-title">Register of Workmen Employed by Contractor</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Name and address of Contractor</div>
                <div class="info-value">{{ data_get($header, 'tenant.name') ?? '' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Name and address of Establishment in / under which contract is carried on</div>
                <div class="info-value">{{ data_get($header, 'branch.name') ?? '' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Nature and location of work</div>
                <div class="info-value">{{ data_get($header, 'branch.address') ?? '' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Name and address of Principal Employer</div>
                <div class="info-value">{{ data_get($header, 'tenant.address') ?? '' }}</div>
            </div>
        </div>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-1">SL. No.</th>
                    <th class="col-2">Name and surname of workman</th>
                    <th class="col-3">Age and Sex</th>
                    <th class="col-4">Father's / Husband's name</th>
                    <th class="col-5">Nature of Employment / Designation</th>
                    <th class="col-6">Permanent Home Address (Village and Tahsil / Taluka and District)</th>
                    <th class="col-7">Local address</th>
                    <th class="col-8">Date of commencement of employment</th>
                    <th class="col-9">Signature or thumb impression of workman</th>
                    <th class="col-10">Date of termination of employment</th>
                    <th class="col-11">Reasons for termination</th>
                    <th class="col-12">Remarks</th>
                </tr>
                <tr>
                    <th class="col-1">1</th>
                    <th class="col-2">2</th>
                    <th class="col-3">3</th>
                    <th class="col-4">4</th>
                    <th class="col-5">5</th>
                    <th class="col-6">6</th>
                    <th class="col-7">7</th>
                    <th class="col-8">8</th>
                    <th class="col-9">9</th>
                    <th class="col-10">10</th>
                    <th class="col-11">11</th>
                    <th class="col-12">12</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                    <tr>
                        <td class="col-1">{{ $index + 1 }}</td>
                        <td class="col-2">{{ $row['name'] ?? '' }}</td>
                        <td class="col-3">{{ $row['age'] ?? '' }}{{ !empty($row['sex']) ? ' / ' . $row['sex'] : '' }}</td>
                        <td class="col-4">{{ $row['father_name'] ?? '' }}</td>
                        <td class="col-5">{{ $row['designation'] ?? '' }}</td>
                        <td class="col-6">{{ $row['permanent_address'] ?? '' }}</td>
                        <td class="col-7">{{ $row['local_address'] ?? '' }}</td>
                        <td class="col-8">{{ $row['joining_date'] ?? '' }}</td>
                        <td class="col-9"></td>
                        <td class="col-10">{{ $row['termination_date'] ?? '' }}</td>
                        <td class="col-11">{{ $row['termination_reason'] ?? '' }}</td>
                        <td class="col-12">{{ $row['remarks'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12" style="height:32px; text-align:center; vertical-align:middle; font-weight:bold;">NIL</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="signature-space"></div>
    </div>
</body>
</html> 
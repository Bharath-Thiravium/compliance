<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XIII - Register of Workmen Employed by Contractor</title>
    <style>
        @page { margin: 3mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 6px;
        }
        .form-container {
            border: 1px solid black;
            padding: 2px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 2px;
        }
        .header-title {
            font-weight: bold;
            font-size: 8px;
        }
        .header-rule {
            font-size: 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: 6px;
        }
        .info-table td {
            padding: 0px 1px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 38%;
        }
        .info-colon { width: 2%; }
        .info-value {
            width: 60%;
            border-bottom: 1px dotted #000;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 5px;
            margin-bottom: 2px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 0px 1px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }
        .register-table th {
            font-weight: bold;
            line-height: 1.0;
            height: 11px;
        }
        .register-table td {
            height: 9px;
            font-size: 5px;
        }
        .col-1  { width: 3%; }
        .col-2  { width: 11%; text-align: left; }
        .col-3  { width: 6%; }
        .col-4  { width: 9%; text-align: left; }
        .col-5  { width: 9%; text-align: left; }
        .col-6  { width: 13%; text-align: left; }
        .col-7  { width: 8%; text-align: left; }
        .col-8  { width: 9%; }
        .col-9  { width: 7%; }
        .col-10 { width: 9%; }
        .col-11 { width: 9%; text-align: left; }
        .col-12 { width: 7%; }
        .signature-space { margin-top: 4px; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XIII</div>
            <div class="header-rule">[See Rule 75]</div>
            <div class="header-title">Register of Workmen Employed by Contractor</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Name and address of Contractor</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ data_get($header, 'tenant.name') ?? '' }}</td>
            </tr>
            <tr>
                <td class="info-label">Name and address of Establishment in/under which contract is carried on</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ data_get($header, 'branch.name') ?? '' }}</td>
            </tr>
            <tr>
                <td class="info-label">Nature and location of work</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ data_get($header, 'branch.address') ?? '' }}</td>
            </tr>
            <tr>
                <td class="info-label">Name and address of Principal Employer</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ data_get($header, 'tenant.address') ?? '' }}</td>
            </tr>
        </table>

        <table class="register-table">
            <colgroup>
                <col style="width:3%">
                <col style="width:11%">
                <col style="width:6%">
                <col style="width:9%">
                <col style="width:9%">
                <col style="width:13%">
                <col style="width:8%">
                <col style="width:9%">
                <col style="width:7%">
                <col style="width:9%">
                <col style="width:9%">
                <col style="width:7%">
            </colgroup>
            <thead>
                <tr>
                    <th>SL. No.</th>
                    <th>Name and surname of workman</th>
                    <th>Age and Sex</th>
                    <th>Father's / Husband's name</th>
                    <th>Nature of Employment / Designation</th>
                    <th>Permanent Home Address (Village and Tahsil / Taluka and District)</th>
                    <th>Local address</th>
                    <th>Date of commencement of employment</th>
                    <th>Signature or thumb impression of workman</th>
                    <th>Date of termination of employment</th>
                    <th>Reasons for termination</th>
                    <th>Remarks</th>
                </tr>
                <tr>
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>
                    <th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align:left">{{ $row['name'] ?? '' }}</td>
                        <td>{{ $row['age'] ?? '' }}{{ !empty($row['sex']) ? ' / ' . $row['sex'] : '' }}</td>
                        <td style="text-align:left">{{ $row['father_name'] ?? '' }}</td>
                        <td style="text-align:left">{{ $row['designation'] ?? '' }}</td>
                        <td style="text-align:left">{{ $row['permanent_address'] ?? '' }}</td>
                        <td style="text-align:left">{{ $row['local_address'] ?? '' }}</td>
                        <td>{{ $row['joining_date'] ?? '' }}</td>
                        <td></td>
                        <td>{{ $row['termination_date'] ?? '' }}</td>
                        <td style="text-align:left">{{ $row['termination_reason'] ?? '' }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12" style="text-align:center;">No records found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="signature-space"></div>
    </div>
</body>
</html>

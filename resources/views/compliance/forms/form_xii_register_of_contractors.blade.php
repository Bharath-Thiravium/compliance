<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XII - Register of Contractors</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; padding: 12px; font-size: 10px; }
        .form-container { border: 1px solid black; padding: 10px 0; margin: 0 auto; width: 99%; }
        .form-inner { padding: 0 12px; }

        .form-header { text-align: center; margin-bottom: 12px; }
        .form-header div { margin: 2px 0; }
        .header-title { font-weight: bold; font-size: 13px; }
        .header-rule { font-size: 10px; }
        .header-sub { font-weight: bold; font-size: 12px; }

        .info-section { margin-bottom: 10px; font-size: 10px; }
        .info-block { margin-bottom: 8px; }
        .info-label { font-weight: bold; display: block; margin-bottom: 2px; }
        .info-value { display: block; padding-left: 4px; }

        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.2;
        }
        .register-table th { font-weight: bold; background-color: #fff; }
        .register-table td { font-size: 9px; height: 18px; }

        .footer-section { display: flex; justify-content: space-between; margin-top: 20px; font-size: 10px; }
        .footer-left { flex: 1; line-height: 1.8; }
        .footer-right { flex: 1; text-align: right; }
        .signature-line { margin-top: 40px; border-top: 1px solid #000; width: 220px; margin-left: auto; }
        .signature-label { margin-top: 3px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
<div class="form-container">
    <div class="form-inner">
        <!-- Header -->
        <div class="form-header">
            <div class="header-title">FORM XII</div>
            <div class="header-rule">[See Rule 74]</div>
            <div class="header-sub">Register of Contractors</div>
        </div>

        <!-- Factory Details -->
        <div class="info-section">
            <div class="info-block">
                <span class="info-label">Name and address of the Principal Employer :</span>
                <span class="info-value">{{ data_get($header, 'tenant.name', '') }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">Name and address of the Establishment :</span>
                <span class="info-value">{{ data_get($header, 'branch.address', '') }}</span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table class="register-table">
        <colgroup>
            <col style="width:6%">
            <col style="width:22%">
            <col style="width:17%">
            <col style="width:17%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:14%">
        </colgroup>
        <thead>
            <tr>
                <th>SL. No.</th>
                <th>Name and address of contractor</th>
                <th>Nature of work on contract</th>
                <th>Location of contract work</th>
                <th colspan="2">Period of contract</th>
                <th>Maximum No. of workmen employed by contractor</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5<br>(From)</th>
                <th>6<br>(To)</th>
                <th>7</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($rows) && count($rows) > 0)
                @foreach($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align:left">{{ $row['contractor_name'] ?? '' }}</td>
                    <td style="text-align:left">{{ $row['nature_of_work'] ?? '' }}</td>
                    <td style="text-align:left">{{ $row['work_location'] ?? '' }}</td>
                    <td>{{ $row['contract_from'] ?? '' }}</td>
                    <td>{{ $row['contract_to'] ?? '' }}</td>
                    <td>{{ $row['max_workers'] ?? '' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" style="text-align:center; padding: 6px;">No records found</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="form-inner">
        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                <div>Place: __________</div>
                <div>Date: &nbsp;&nbsp;__________</div>
            </div>
            <div class="footer-right">
                <div class="signature-line"></div>
                <div class="signature-label">Signature of the Licensing Officer</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

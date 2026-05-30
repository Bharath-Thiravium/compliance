<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM C - Register of Loan / Recoveries / Damage / Loss / Fine / Advance</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #000; background: #ccc; }
        .page-wrapper { padding: 8px; }
        .page-container { background: #fff; border: 2px solid #000; padding: 15px 10px 20px 10px; }
        .form-title { text-align: center; font-weight: bold; font-size: 11px; line-height: 1.6; margin-bottom: 16px; }
        .header-block { margin: 10px 0 14px 0; }
        .header-block .row { display: flex; margin-bottom: 10px; font-size: 9px; }
        .header-block .row .label { width: 200px; flex-shrink: 0; }
        .header-block .row .value { padding-left: 40px; flex: 1; }
        .table-wrap { width: 100%; overflow: visible; }
        table.reg-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.reg-table th, table.reg-table td { border: 1px solid #000; font-size: 7px; padding: 2px 1px; text-align: center; vertical-align: middle; }
        table.reg-table th { font-weight: bold; white-space: normal; word-break: break-word; line-height: 1.3; height: 50px; }
        table.reg-table td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        table.reg-table td.left { text-align: left; padding: 2px 2px; font-size: 6.5px; }
        table.reg-table tr.col-nums td { font-size: 6.5px; font-weight: bold; background: #f5f5f5; padding: 2px 0; }
        .footer-note { font-size: 8px; margin-top: 6px; }
        @media print {
            body { background: #fff; }
            .page-wrapper { padding: 0; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
<div class="page-container">
    <div class="form-title">
        FORM C<br>
        REGISTER OF LOAN / RECOVERIES / Damage / loss / fine / advance<br>
        <span style="font-weight:normal; font-size:9px;">[See rule 2(1)]</span>
    </div>
    <div class="header-block">
        <div class="row"><span class="label">Name of the Establishment :-</span><span class="value">{{ $establishment_name ?? '' }}</span></div>
        <div class="row"><span class="label">Name of Owner :-</span><span class="value">{{ $owner_name ?? '' }}</span></div>
    </div>
    <div class="table-wrap">
    <table class="reg-table">
        <thead>
            <tr>
                <th style="width:5%">Sr. No.</th>
                <th style="width:14%">Name</th>
                <th style="width:9%">Recovery Type<br>(Damage/loss/fine/<br>advance/loans)</th>
                <th style="width:7%">Particulars</th>
                <th style="width:8%">Date of<br>damage/loss *</th>
                <th style="width:6%">Amount</th>
                <th style="width:8%">Whether show<br>cause issued*</th>
                <th style="width:9%">Explanation heard in<br>presence of *</th>
                <th style="width:6%">Number of<br>Instalments</th>
                <th style="width:7%">First<br>Month/Year</th>
                <th style="width:7%">Last<br>Month/Year</th>
                <th style="width:8%">Date of<br>Complete<br>Recovery</th>
                <th style="width:6%">Remarks</th>
            </tr>
            <tr class="col-nums">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td>
                <td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td>
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? [] as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="left">{{ $row['employee_name'] ?? '' }}</td>
                <td>{{ $row['recovery_type'] ?? '' }}</td>
                <td>{{ $row['particulars'] ?? '' }}</td>
                <td>{{ $row['damage_date'] ?? '' }}</td>
                <td>{{ $row['amount'] ?? '' }}</td>
                <td>{{ $row['show_cause'] ?? '' }}</td>
                <td>{{ $row['explanation'] ?? '' }}</td>
                <td>{{ $row['installments'] ?? '' }}</td>
                <td>{{ $row['first_month'] ?? '' }}</td>
                <td>{{ $row['last_month'] ?? '' }}</td>
                <td>{{ $row['recovery_date'] ?? '' }}</td>
                <td>{{ $row['remarks'] ?? '' }}</td>
            </tr>
            @empty
            <tr><td colspan="13" style="text-align:center; padding:4px;">NIL</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div style="height: 30px;"></div>
    <div class="footer-note">*Applicable only in case of damage/loss/fine</div>

</div>
</div>
</body>
</html>

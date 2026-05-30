<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM XX - Register of Deductions for Damage or Loss</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #000; background: #ccc; }

        .page-wrapper { display: flex; justify-content: center; }
        .page-container { width: 95%; margin: 8px auto; background: #fff; border: 2px solid #000; padding: 4px 0 0 0; }

        .form-header { text-align: center; padding: 3px 0 4px; }
        .form-header .title    { font-size: 9px; font-weight: bold; }
        .form-header .rule     { font-size: 7.5px; }
        .form-header .subtitle { font-size: 8px; font-weight: bold; }

        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { border: 1px solid #000; padding: 2px 4px; font-size: 8px; vertical-align: middle; }
        .meta-table td.label { width: 32%; font-weight: bold; }

        .form-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 40px; }
        .form-table th,
        .form-table td {
            border: 1px solid #000;
            padding: 2px 1px;
            font-size: 6.5px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }
        .form-table th { font-weight: bold; white-space: normal; word-break: break-word; line-height: 1.1; height: 40px; }
        .form-table td.left  { text-align: left; padding-left: 3px; }
        .form-table td.right { text-align: right; }
        .form-table td.name-cell {
            text-align: left;
            padding-left: 3px;
            word-break: break-word;
            font-size: 6.5px;
        }

        .form-table tr.col-nums th { height: auto; font-size: 7px; padding: 1px 0; }
        .form-table tr.nil-row td  { text-align: center; font-weight: bold; padding: 6px 0; font-size: 8px; }
        .form-table tr.footnote td { text-align: left; font-size: 7px; padding: 2px 4px; font-style: italic; }

        .signature { margin-top: 60px; text-align: right; padding: 0 20px 10px 0; font-size: 8px; min-height: 60px; page-break-inside: avoid; display: block; }

        @media print {
            body { background: #fff; }
            .page-wrapper { display: block; }
            .page-container { width: 100%; margin: 0; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
<div class="page-container">

    <div class="form-header">
        <div class="title">FORM XX</div>
        <div class="rule">[See Rule 78(2)(d)]</div>
        <div class="subtitle">Register of Deductions for Damage or Loss</div>
    </div>

    <table class="meta-table">
        <tr><td class="label">NAME AND ADDRESS OF CONTRACTOR :</td><td>{{ $header['contractor_name'] ?? '' }}</td></tr>
        <tr><td class="label">NATURE AND LOCATION OF WORK :</td><td>{{ $header['work_nature'] ?? '' }}</td></tr>
        <tr><td class="label">NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</td><td>{{ $header['establishment_name'] ?? '' }}</td></tr>
        <tr><td class="label">NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</td><td>{{ $header['principal_employer'] ?? '' }}</td></tr>
        <tr><td class="label">Month &amp; Year :</td><td>{{ $header['period'] ?? '' }}</td></tr>
    </table>

    <table class="form-table">
        <colgroup>
            <col style="width:4%">
            <col style="width:17%">
            <col style="width:11%">
            <col style="width:9%">
            <col style="width:9%">
            <col style="width:7%">
            <col style="width:9%">
            <col style="width:11%">
            <col style="width:7%">
            <col style="width:5%">
            <col style="width:6%">
            <col style="width:6%">
            <col style="width:5%">
        </colgroup>
        <thead>
            <tr class="col-nums">
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th>
                <th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>13</th>
            </tr>
            <tr>
                <th>Sl. No.</th>
                <th>Name of workmen</th>
                <th>Father's / Husband's name</th>
                <th>Designation / Nature of employment</th>
                <th>Particulars of damage or loss</th>
                <th>Date of damage / loss *</th>
                <th>Whether workmen showed cause against deduction</th>
                <th>Name of person in whose presence employee's explanation was heard</th>
                <th>Amount of deduction imposed</th>
                <th>No. of instalments</th>
                <th>First Month / Year</th>
                <th>Last Month / Year</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? [] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="name-cell">{{ $row['employee_name'] ?? '' }}</td>
                <td>{{ $row['father_name'] ?? '' }}</td>
                <td>{{ $row['designation'] ?? '' }}</td>
                <td>{{ $row['damage_particulars'] ?? '' }}</td>
                <td>{{ $row['damage_date'] ?? '' }}</td>
                <td>{{ $row['showed_cause'] ?? '' }}</td>
                <td>{{ $row['witness_name'] ?? '' }}</td>
                <td class="right">{{ $row['deduction_amount'] ?? '' }}</td>
                <td>{{ $row['instalments'] ?? '' }}</td>
                <td>{{ $row['first_month'] ?? '' }}</td>
                <td>{{ $row['last_month'] ?? '' }}</td>
                <td>{{ $row['remarks'] ?? '' }}</td>
            </tr>
            @empty
            <tr class="nil-row"><td colspan="13">NIL</td></tr>
            @endforelse
            @if(!empty($rows))
            <tr style="font-weight:bold;">
                <td colspan="8" style="text-align:right; padding-right:4px;">Total</td>
                <td class="right">{{ isset($totals['deduction_amount']) ? number_format($totals['deduction_amount'], 2) : '' }}</td>
                <td colspan="4"></td>
            </tr>
            @endif
            <tr class="footnote">
                <td colspan="13">* Applicable only in case of damage / loss / fine</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        Seal &amp; Signature of the Contractor
    </div>

</div>{{-- /page-container --}}
</div>{{-- /page-wrapper --}}
</body>
</html>

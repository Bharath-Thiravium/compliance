<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XXI - Register of Fines</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 landscape; margin: 8mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }

        /* ── OUTER WRAPPER ── */
        .form-wrapper {
            border: 2px solid #000;
            width: 100%;
            box-sizing: border-box;
        }

        /* ── TITLE BLOCK ── */
        .title-block {
            text-align: center;
            padding: 4px 0 3px;
            border-bottom: 1px solid #000;
            line-height: 1.5;
        }
        .title-block .t1 { font-size: 9px; font-weight: bold; }
        .title-block .t2 { font-size: 8px; font-weight: normal; }
        .title-block .t3 { font-size: 9px; font-weight: bold; }

        /* ── HEADER TABLE ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .header-table tr td {
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            font-size: 8px;
            vertical-align: middle;
            line-height: 1.3;
        }
        .header-table tr td:first-child {
            width: 42%;
            font-weight: bold;
            border-right: none;
            white-space: nowrap;
            font-size: 7.5px;
        }
        .header-table tr td:last-child {
            width: 58%;
        }

        /* ── MAIN REGISTER TABLE ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table thead tr.col-num th {
            border: 1px solid #000;
            font-size: 7px;
            font-weight: normal;
            text-align: center;
            padding: 1px 0;
            height: 12px;
            background: #fff;
        }

        .main-table thead tr.col-hdr th {
            border: 1px solid #000;
            font-size: 6.5px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            line-height: 1.2;
            padding: 2px 1px;
            height: 40px;
            background: #fff;
        }
        .main-table thead tr.col-hdr th:nth-child(2),
        .main-table thead tr.col-hdr th:nth-child(3),
        .main-table thead tr.col-hdr th:nth-child(4) {
            text-align: left;
            padding-left: 3px;
        }

        .main-table tbody td {
            border: 1px solid #000;
            font-size: 7.5px;
            padding: 1px 3px;
            text-align: center;
            vertical-align: middle;
            height: 15px;
            line-height: 1.2;
        }
        .main-table tbody td:nth-child(2),
        .main-table tbody td:nth-child(3),
        .main-table tbody td:nth-child(4) {
            text-align: left;
        }

        .main-table tbody tr.footer-row td {
            border: 1px solid #000;
            font-size: 7.5px;
            padding: 2px 5px;
            vertical-align: middle;
            height: auto;
        }
        .fn-note { text-align: left; font-style: italic; }
        .fn-nil  { text-align: center; font-weight: bold; }

        /* ── SIGNATURE BLOCK ── */
        .sig-area {
            padding: 80px 8px 8px 0;
            text-align: right;
            font-size: 8px;
            font-weight: bold;
        }

        @media print {
            body { padding: 10px; }
            .form-wrapper { border: 2px solid #000 !important; }
        }
    </style>
</head>
<body>
<div class="form-wrapper">

    {{-- ── TITLE ── --}}
    <div class="title-block">
        <div class="t1">FORM XXI</div>
        <div class="t2">[See Rule 78(2)(d)]</div>
        <div class="t3">Register of Fines</div>
    </div>

    {{-- ── HEADER TABLE ── --}}
    <table class="header-table">
        <tr>
            <td>NAME AND ADDRESS OF CONTRACTOR :</td>
            <td>{{ $header['contractor_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NATURE AND LOCATION OF WORK :</td>
            <td>{{ $header['work_nature'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</td>
            <td>{{ $header['establishment_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</td>
            <td>{{ $header['principal_employer'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Month &amp; Year:</td>
            <td>{{ $header['month_year'] ?? '' }}</td>
        </tr>
    </table>

    {{-- ── MAIN REGISTER TABLE ── --}}
    <table class="main-table">
        <colgroup>
            <col style="width:3%">
            <col style="width:22%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:7%">
            <col style="width:5%">
            <col style="width:7%">
            <col style="width:11%">
            <col style="width:7%">
            <col style="width:7%">
            <col style="width:9%">
            <col style="width:4%">
        </colgroup>

        <thead>
            <tr class="col-num">
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>
                <th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th>
            </tr>
            <tr class="col-hdr">
                <th>SL<br>NO</th>
                <th>Name of workmen</th>
                <th>Father's/Husband's<br>name</th>
                <th>Designation/Nature<br>of employment</th>
                <th>Act/Omission<br>for which fine<br>imposed</th>
                <th>Date of<br>offence</th>
                <th>Whether workmen<br>showed against<br>fine</th>
                <th>Name of person in whose<br>presence employer's<br>explanation was heard</th>
                <th>Wage period<br>and wages<br>payable</th>
                <th>Amount of<br>fine imposed</th>
                <th>Date on which<br>fine realised</th>
                <th>Remarks</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows ?? [] as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-size:5.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:0;">{{ $row['name'] ?? '' }}</td>
                <td>{{ ($v = $row['father_name'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['designation'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['act_or_omission'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['date_of_offence'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['showed_cause'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['heard_by'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['wage_period'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['fine_amount'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['fine_realised'] ?? '') === 'NIL' ? '' : $v }}</td>
                <td>{{ ($v = $row['remarks'] ?? '') === '-' ? '' : $v }}</td>
            </tr>
            @empty
            @endforelse

            <tr class="footer-row">
                <td colspan="4" class="fn-note">*Applicable only in case of damage/loss/fine</td>
                <td colspan="8" class="fn-nil">Nil for the month of &nbsp;{{ $header['month_year'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── SIGNATURE ── --}}
    <div class="sig-area">
        @if(!empty($seal_path))
            <img src="{{ $seal_path }}" style="width:60px;height:auto;" alt="seal"><br>
        @endif
        @if(!empty($signature_path))
            <img src="{{ $signature_path }}" style="width:100px;height:auto;" alt="signature"><br>
        @endif
        Seal Signature of The Contractor
    </div>

</div>
</body>
</html>

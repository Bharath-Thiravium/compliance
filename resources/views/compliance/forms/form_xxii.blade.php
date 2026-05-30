<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>FORM XXII - Register of Advances v2.0</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            padding: 10px;
            margin: 0;
            background: #fff;
            color: #000;
        }
        .form-container {
            border: 2px solid #000;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-title {
            text-align: center;
            padding: 3px 2px;
            border-bottom: 1px solid #000;
            line-height: 1.3;
        }
        .form-title .t1 { font-size: 11px; font-weight: bold; }
        .form-title .t2 { font-size: 9px; }
        .form-title .t3 { font-size: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8px;
            vertical-align: middle;
        }
        .info-table tr.two-cell td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 3px 5px; white-space: normal; vertical-align: middle;
        }
        .info-table tr.two-cell td.lbl { font-weight: bold; font-size: 8px; width: 30%; border-right: 1px solid #000; }
        .info-table tr.two-cell td.val { font-size: 8px; width: 70%; }
        .info-table tr.one-cell td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 3px 5px; white-space: normal; font-size: 8px;
        }
        .info-table tr.one-cell td span.lbl { font-weight: bold; }
        .month-table td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 3px 5px; font-size: 8px; vertical-align: middle;
        }
        .month-table td.lbl { font-weight: bold; width: 18%; }
        .month-table td.val { width: 82%; }
        .reg-table { border-top: none; table-layout: fixed; width: 100%; }
        .reg-table td, .reg-table th {
            border: 1px solid #000; padding: 2px 3px;
            font-size: 8px; vertical-align: middle;
        }
        .reg-table .num-row td {
            text-align: center; font-weight: bold; font-size: 8px;
            padding: 2px; height: 12px; color: #000; background: #fff;
        }
        .reg-table .hdr-row th {
            text-align: center; font-weight: bold; font-size: 7px;
            white-space: normal; line-height: 1.1; padding: 1px;
            color: #000; background: #fff; vertical-align: middle;
        }
        .reg-table tbody td {
            font-size: 8px; height: 18px; vertical-align: middle;
        }
        .td-sl   { text-align: center; }
        .td-name { text-align: left; font-size: 6.5px !important; padding: 2px 1px !important; white-space: nowrap; overflow: hidden; }
        .td-left { text-align: left; font-size: 7px; padding: 2px 1px; white-space: nowrap; }
        .td-ctr  { text-align: center; font-size: 8px; }
        .nil-row td { text-align: center; font-weight: bold; font-size: 9px; height: 20px; }
        .tfoot-note { text-align: left; font-style: italic; padding: 4px 6px; border: 1px solid #000; font-size: 8px; vertical-align: middle; }
        .tfoot-nil  { text-align: center; font-weight: bold; padding: 4px 6px; border: 1px solid #000; font-size: 8px; vertical-align: middle; }
        .tfoot-sig  { text-align: right; padding: 4px 6px 2px 6px; border: 1px solid #000; font-size: 8px; vertical-align: bottom; height: 100px; }
        .sig-label  { font-size: 8px; font-weight: bold; color: #000; text-align: right; display: block; }
    </style>
</head>
<body>
<div class="form-container">

    {{-- TITLE --}}
    <div class="form-title">
        <div class="t1">FORM XXII</div>
        <div class="t2">[See Rule 78(2)(d)]</div>
        <div class="t3">Register of Advances</div>
    </div>

    {{-- INFO ROWS --}}
    <table class="info-table">
        <tbody>
            <tr class="two-cell">
                <td class="lbl">NAME AND ADDRESS OF CONTRACTOR :</td>
                <td class="val">{{ $header['contractor_name'] ?? '' }}</td>
            </tr>
            <tr class="two-cell">
                <td class="lbl">NATURE AND LOCATION OF WORK :</td>
                <td class="val">{{ $header['work_nature'] ?? '' }}</td>
            </tr>
            <tr class="one-cell">
                <td colspan="2">
                    <span class="lbl">NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</span>
                    {{ $header['establishment_name'] ?? '' }}
                </td>
            </tr>
            <tr class="one-cell">
                <td colspan="2">
                    <span class="lbl">NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</span>
                    {{ $header['principal_employer'] ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- MONTH & YEAR --}}
    <table class="month-table">
        <tbody>
            <tr>
                <td class="lbl">Month &amp; Year:</td>
                <td class="val">{{ $header['month_year'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- REGISTER TABLE --}}
    <table class="reg-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 22%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 7%;">
            <col style="width: 7%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 13%;">
            <col style="width: 3%;">
        </colgroup>
        <thead>
            <tr class="num-row">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td>
                <td>7</td><td>8</td><td>9</td><td>10</td><td>11</td>
            </tr>
            <tr class="hdr-row">
                <th>SL NO</th>
                <th>Name of workman</th>
                <th>Father's/Husband's name</th>
                <th>Designation/<br>Nature of<br>employment</th>
                <th>Date and<br>Amount of<br>Advance Given</th>
                <th>Date and<br>Amount of<br>Advance Given</th>
                <th>Purpose (S) for<br>Which Advance<br>Made</th>
                <th>No. Of<br>Instalments by<br>Which Advance to<br>Be Repaid</th>
                <th>Date and<br>Amount of Each<br>Instalment<br>Repaid</th>
                <th>Date on Which Last<br>Instalment Was<br>Repaid</th>
                <th>Signature of<br>Thumb Impresion<br>of Workman</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($rows) && count($rows) > 0)
                @foreach($rows as $i => $row)
                <tr>
                    <td class="td-sl">{{ $i + 1 }}</td>
                    <td class="td-name">{{ strtoupper($row['name'] ?? '') }}</td>
                    <td class="td-left">{{ strtoupper($row['father_name'] ?? '') }}</td>
                    <td class="td-left">{{ strtoupper($row['designation'] ?? '') }}</td>
                    <td class="td-ctr">{{ $row['advance_date_amount_1'] ?? '' }}</td>
                    <td class="td-ctr">{{ $row['advance_date_amount_2'] ?? '' }}</td>
                    <td class="td-ctr">{{ $row['purpose'] ?? '' }}</td>
                    <td class="td-ctr">{{ $row['installments'] ?? '' }}</td>
                    <td class="td-ctr">{{ $row['installment_repaid'] ?? '' }}</td>
                    <td class="td-ctr">{{ $row['last_installment_date'] ?? '' }}</td>
                    <td class="td-ctr"></td>
                </tr>
                @endforeach
            @else
                <tr class="nil-row">
                    <td colspan="11">NIL</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="tfoot-note">*Applicable only in case of damage/loss/fine</td>
                <td colspan="6" class="tfoot-nil">
                    @if((isset($is_nil) && $is_nil) || (isset($all_nil_advances) && $all_nil_advances))
                        Nil for the month of {{ $header['month_year'] ?? '' }}
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: right; padding: 100px 6px 8px 6px; font-size: 8px; font-weight: bold; border-top: 1px solid #000;">
        @if(isset($company_signature) && $company_signature)
            <img src="{{ storage_path('app/' . $company_signature) }}" style="width:45px;height:45px;object-fit:contain;" alt="Seal"><br>
        @endif
        Seal Signature of The Contractor
    </div>

</div>
</body>
</html>

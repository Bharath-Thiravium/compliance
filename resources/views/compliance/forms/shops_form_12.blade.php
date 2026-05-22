<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM D - Register of Advances</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 landscape; margin: 8mm; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .form-container {
            border: 2px solid #000;
            width: 100%;
            box-sizing: border-box;
        }

        .form-header {
            text-align: center;
            padding: 6px 4px 4px;
            border-bottom: 1px solid #000;
            font-size: 10px;
            line-height: 1.6;
        }
        .form-header .bold { font-weight: bold; }

        .establishment-row {
            padding: 4px 8px;
            border-bottom: 1px solid #000;
            font-size: 9px;
            font-weight: bold;
        }

        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }
        .register-table th {
            font-weight: bold;
            line-height: 1.2;
        }
        .register-table td { text-align: left; }
        .text-center { text-align: center !important; }
    </style>
</head>
<body>
<div class="form-container">

    <div class="form-header">
        <div>Tamil Nadu Shops And Establishments Rules</div>
        <div class="bold">FORM D</div>
        <div>See Rule 13 (4)</div>
        <div class="bold">Register of advances made to employed persons</div>
    </div>

    <div class="establishment-row">Establishment</div>

    <table class="register-table">
        <colgroup>
            <col style="width:4%">
            <col style="width:11%">
            <col style="width:13%">
            <col style="width:11%">
            <col style="width:11%">
            <col style="width:13%">
            <col style="width:10%">
            <col style="width:10%">
            <col style="width:12%">
            <col style="width:5%">
        </colgroup>
        <thead>
            <tr>
                <th>Sl. No.</th>
                <th>Name</th>
                <th>Father's name or Husband's name</th>
                <th>Amount of, and date on which advance made</th>
                <th>Purpose(s) for which advance made</th>
                <th>Number of installments by which advance to be repaid</th>
                <th>Postponements granted</th>
                <th>Date on which total amount repaid</th>
                <th>Signature or thumb-impression of person employed</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <th>(1)</th><th>(2)</th><th>(3)</th><th>(4)</th><th>(5)</th>
                <th>(6)</th><th>(7)</th><th>(8)</th><th>(9)</th><th>(10)</th>
            </tr>
        </thead>
        <tbody>
            @if($is_nil)
            <tr>
                <td colspan="10" style="height: 35px;"></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align: center; font-weight: bold; height: 35px; vertical-align: middle;">NIL</td>
            </tr>
            @else
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['father_name'] }}</td>
                <td class="text-center">{{ number_format($row['advance_amount'], 2) }}<br>{{ $row['advance_date'] }}</td>
                <td>{{ $row['purpose'] }}</td>
                <td class="text-center">{{ $row['installments'] }}</td>
                <td class="text-center">{{ $row['postponements'] }}</td>
                <td class="text-center">{{ $row['repaid_date'] }}</td>
                <td></td>
                <td>{{ $row['remarks'] }}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>

</div>
</body>
</html>

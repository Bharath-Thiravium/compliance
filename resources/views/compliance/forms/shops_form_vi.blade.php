<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM VI - Register of National and Festival Holidays</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A3 landscape; margin: 25mm; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .form-container {
            border: 1px solid #000;
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
            line-height: 1.2;
        }
        .register-table th { font-weight: bold; }
        .register-table td { text-align: left; }
        .text-center { text-align: center !important; }

        .legend-section {
            padding: 6px 8px;
            font-size: 9px;
            border-top: 1px solid #000;
        }
        .legend-title { font-weight: bold; margin-bottom: 3px; }
        .legend-item { margin-bottom: 2px; }
    </style>
</head>
<body>
<div class="form-container">

    <div class="form-header">
        <div>The Tamil Nadu Industrial Establishments</div>
        <div>(National and Festival Holidays) Rules</div>
        <div class="bold">FORM VI</div>
        <div>See sub-rule (1) of rule 7</div>
        <div class="bold">REGISTER OF NATIONAL AND FESTIVAL HOLIDAYS</div>
    </div>

    <table class="register-table">
        <colgroup>
            <col style="width:4%">
            <col style="width:22%">
            <col style="width:14%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:14%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Name of the employee</th>
                <th rowspan="2">Ticket number or father's name</th>
                <th colspan="9">Days, dates and months of the year on which National and Festival holidays are allowed under section 3 of the Tamil Nadu Industrial Establishments (National and Festival Holidays) Act, 1958 (Tamil Nadu Act XXXIII of 1958)</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th class="text-center">1</th>
                <th class="text-center">2</th>
                <th class="text-center">3</th>
                <th class="text-center">4</th>
                <th class="text-center">5</th>
                <th class="text-center">6</th>
                <th class="text-center">7</th>
                <th class="text-center">8</th>
                <th class="text-center">9</th>
            </tr>
        </thead>
        <tbody>
            @if($is_nil)
            <tr>
                <td colspan="13" class="text-center">Nil</td>
            </tr>
            @else
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="white-space:nowrap; font-size:8px;">{{ $row['employee_name'] }}</td>
                <td>{{ $row['ticket'] }}</td>
                <td class="text-center">{{ $row['holiday1'] }}</td>
                <td class="text-center">{{ $row['holiday2'] }}</td>
                <td class="text-center">{{ $row['holiday3'] }}</td>
                <td class="text-center">{{ $row['holiday4'] }}</td>
                <td class="text-center">{{ $row['holiday5'] }}</td>
                <td class="text-center">{{ $row['holiday6'] }}</td>
                <td class="text-center">{{ $row['holiday7'] }}</td>
                <td class="text-center">{{ $row['holiday8'] }}</td>
                <td class="text-center">{{ $row['holiday9'] }}</td>
                <td>{{ $row['remarks'] }}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>

    <div class="legend-section">
        <div class="legend-title">To be marked as follows:—</div>
        <div class="legend-item">'H'  for holidays allowed</div>
        <div class="legend-item">'W/D' for work on double wages</div>
        <div class="legend-item">'W/H' for work with substituted holiday</div>
        <div class="legend-item">'N/E' if not eligible for the wages</div>
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM D - Register of Attendance</title>
    <style>
        @page { size: A3 landscape; margin: 3mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7px; background: #fff; color: #000; }

        .page-wrapper { padding: 3px; }
        .page-container { background: #fff; border: 2px solid #000; padding: 4px 4px 30px 4px; }

        .form-header { text-align: center; border-bottom: 1px solid #000; padding: 2px 0; }
        .form-header .t1 { font-size: 11px; font-weight: bold; }
        .form-header .t2 { font-size: 9px; font-weight: bold; }
        .form-header .t3 { font-size: 7px; }

        .info-tbl { width:100%; border-collapse:collapse; }
        .info-tbl td { padding:2px 5px; font-size:7.5px; border-bottom:1px solid #000; }
        .info-tbl tr:last-child td { border-bottom:1px solid #000; }
        .lbl { font-weight:bold; width:145px; white-space:nowrap; border-right:1px solid #000; }

        .att { width:100%; border-collapse:collapse; table-layout:fixed; }
        .att th, .att td {
            border:1px solid #000;
            text-align:center; vertical-align:middle;
            overflow:hidden; white-space:nowrap;
            padding:1px 0; font-size:6px;
        }

        .h-orange     { background-color:#f4a623; font-weight:bold; }
        .h-blue       { background-color:#4472c4; color:#fff; font-weight:bold; }
        .h-lblue      { background-color:#bdd7ee; font-weight:bold; }
        .h-sum        { background-color:#bdd7ee; font-weight:bold; white-space:normal; line-height:1.15; }
        .h-sum-nowrap { background-color:#bdd7ee; font-weight:bold; white-space:nowrap; font-size:5.5px; }

        .sp  { color:#c55a11; font-weight:bold; }
        .swo { color:#c55a11; font-weight:bold; }
        .sa  { color:#000; }
        .spl { color:#375623; font-weight:bold; }
        .sph { color:#375623; font-weight:bold; }
        .shd { color:#7030a0; font-weight:bold; }

        .tot td { font-weight:bold; background:#f2f2f2 !important; font-size:7px; }
        .tot-lbl { text-align:right !important; padding-right:4px !important; }
    </style>
</head>
<body>
<div class="page-wrapper">
<div class="page-container">

    <div class="form-header">
        <div class="t1">FORM D</div>
        <div class="t2">REGISTER OF ATTENDANCE</div>
        <div class="t3">[See rule 2(1)]</div>
    </div>

    <table class="info-tbl">
        <tr>
            <td class="lbl">Name of the Establishment</td>
            <td>{{ $establishment_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Name of Owner</td>
            <td>{{ $owner_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">For the Period From</td>
            <td>1st {{ $month_name ?? '' }}{{ isset($year) ? ' '.$year : '' }}</td>
        </tr>
    </table>

    <table class="att">
        <thead>
            <tr>
                <th rowspan="2" style="width:3%;  font-size:7px;" class="h-orange">S.<br>NO</th>
                <th rowspan="2" style="width:10%; font-size:7px; text-align:left; padding-left:3px;" class="h-orange">NAME</th>
                <th rowspan="2" style="width:8%;  font-size:7px; text-align:left; padding-left:3px;" class="h-orange">DESIGNATION</th>
                <th colspan="31" style="font-size:7px;" class="h-blue">DATE</th>
                <th rowspan="2" style="width:5%; font-size:5.5px;" class="h-sum">Total<br>Present<br>Days</th>
                <th rowspan="2" style="width:4%; font-size:5.5px;" class="h-sum">Paid<br>Holi-<br>days</th>
                <th rowspan="2" style="width:4%; font-size:5.5px;" class="h-sum">Paid<br>Leave</th>
                <th rowspan="2" style="width:4%; font-size:5.5px;" class="h-sum">Weekly<br>Off</th>
                <th rowspan="2" style="width:4%; font-size:5.5px;" class="h-sum">ABSENT<br>Days</th>
                <th rowspan="2" style="width:4%; font-size:5.5px;" class="h-sum">Total<br>Days</th>
                <th rowspan="2" style="width:3%;" class="h-sum-nowrap">Remarks</th>
            </tr>
            <tr>
                @for($day = 1; $day <= 31; $day++)
                    <th style="width:1.35%; font-size:5.5px; padding:1px 0;" class="h-lblue">{{ $day }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? $entries ?? [] as $idx => $row)
                <tr>
                    <td style="width:3%;  font-size:7px;">{{ $idx + 1 }}</td>
                    <td style="width:10%; font-size:7px; text-align:left; padding-left:3px; white-space:nowrap; overflow:hidden;">{{ $row['employee_name'] ?? '' }}</td>
                    <td style="width:8%;  font-size:7px; text-align:left; padding-left:3px; white-space:nowrap; overflow:hidden;">{{ $row['designation'] ?? '' }}</td>
                    @for($day = 1; $day <= 31; $day++)
                        @php
                            $s   = $row['day_'.$day] ?? '';
                            $cls = match(strtoupper($s)) {
                                'P'   => 'sp', 'W/O' => 'swo',
                                'PL'  => 'spl', 'PH'  => 'sph',
                                'HD'  => 'shd', default => 'sa',
                            };
                        @endphp
                        <td style="width:1.35%; font-size:5.5px; padding:1px 0;" class="{{ $cls }}">{{ $s }}</td>
                    @endfor
                    <td style="width:5%; font-size:7px;">{{ $row['total_present'] ?? 0 }}</td>
                    <td style="width:4%; font-size:7px;">{{ $row['paid_holidays'] ?? 0 }}</td>
                    <td style="width:4%; font-size:7px;">{{ $row['paid_leave']    ?? 0 }}</td>
                    <td style="width:4%; font-size:7px;">{{ $row['weekly_off']    ?? 0 }}</td>
                    <td style="width:4%; font-size:7px;">{{ $row['absent_days']   ?? 0 }}</td>
                    <td style="width:4%; font-size:7px;">{{ $row['total_days']    ?? 0 }}</td>
                    <td style="width:3%; font-size:7px;">{{ $row['remarks']       ?? '-' }}</td>
                </tr>
            @empty
                @for($i = 1; $i <= 9; $i++)
                    <tr>
                        <td style="width:3%;  font-size:7px;">{{ $i }}</td>
                        <td style="width:10%; font-size:7px;"></td>
                        <td style="width:8%;  font-size:7px;"></td>
                        @for($day = 1; $day <= 31; $day++)
                            <td style="width:1.35%; font-size:5.5px;"></td>
                        @endfor
                        <td style="width:5%; font-size:7px;">0</td>
                        <td style="width:4%; font-size:7px;">0</td>
                        <td style="width:4%; font-size:7px;">0</td>
                        <td style="width:4%; font-size:7px;">0</td>
                        <td style="width:4%; font-size:7px;">0</td>
                        <td style="width:4%; font-size:7px;">0</td>
                        <td style="width:3%; font-size:7px;">-</td>
                    </tr>
                @endfor
            @endforelse

            <tr class="tot">
                <td colspan="3" class="tot-lbl">TOTAL</td>
                @for($day = 1; $day <= 31; $day++)
                    <td style="width:1.35%; font-size:5.5px;"></td>
                @endfor
                <td style="width:5%; font-size:7px;">{{ $totals['total_present'] ?? 0 }}</td>
                <td style="width:4%; font-size:7px;">{{ $totals['paid_holidays'] ?? 0 }}</td>
                <td style="width:4%; font-size:7px;">{{ $totals['paid_leave']    ?? 0 }}</td>
                <td style="width:4%; font-size:7px;">{{ $totals['weekly_off']    ?? 0 }}</td>
                <td style="width:4%; font-size:7px;">{{ $totals['absent_days']   ?? 0 }}</td>
                <td style="width:4%; font-size:7px;">{{ $totals['total_days']    ?? 0 }}</td>
                <td style="width:3%; font-size:7px;">-</td>
            </tr>
        </tbody>
    </table>

</div>
</div>
</body>
</html>

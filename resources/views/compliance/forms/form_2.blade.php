<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 2 - Notice of Periods of Work</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 6px; color: #000; padding-top: 2px; }
        .hdr { text-align: center; margin-bottom: 4px; line-height: 1.4; }
        .ft { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .ft td { font-size: 7px; padding: 1px 2px; border-bottom: 1px solid #000; }
        .ft td.l { font-weight: bold; width: 80pt; }
        .mt { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 5.5px; margin-bottom: 4px; }
        .mt th, .mt td { border: 1px solid #000; padding: 0; text-align: center; vertical-align: middle; height: 12px; line-height: 1; overflow: hidden; }
        .mt th { font-weight: bold; font-size: 5.5px; }
        .lc { text-align: left !important; font-weight: bold; font-size: 5.5px; width: 7%; }
        .sig { width: 100%; border-collapse: collapse; font-size: 7px; margin-top: 4px; }
        .sig td { padding: 1px 0; vertical-align: bottom; }
        .nil-msg { text-align: center; font-size: 9px; padding: 20px; color: #555; }
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="4" style="width:99%;margin-left:auto;margin-right:auto;border:2px solid #000;">
<tr>
<td style="padding:4px;border:1px solid #000;">

    <div class="hdr">
        <div style="font-size:7px;">The Tamil Nadu Factories Rules</div>
        <div style="font-weight:bold;font-size:8px;">FORM 2</div>
        <div style="font-size:7px;">(Prescribed under Rule 79)</div>
        <div style="font-weight:bold;font-size:7px;">Notice of Periods of work for adult workers and children</div>
    </div>

    <table class="ft">
        <tr><td class="l">Name of factory</td><td>{{ $header['factory_name'] ?? '' }}</td></tr>
        <tr><td class="l">Place</td><td>{{ $header['place'] ?? '' }}</td></tr>
        <tr><td class="l">District</td><td>{{ $header['district'] ?? '' }}</td></tr>
    </table>

    @php $relayGroups = $relay_groups ?? $rows ?? []; @endphp

    @if($is_nil ?? empty($relayGroups))
        <div class="nil-msg">{{ $nil_message ?? 'No Shift Schedule Data Available' }}</div>
    @else
        <table class="mt">
            <colgroup>
                <col style="width:4%">  {{-- Relay # --}}
                <col style="width:14%"> {{-- Shift Name --}}
                <col style="width:10%"> {{-- From --}}
                <col style="width:10%"> {{-- To --}}
                <col style="width:7%">  {{-- Men --}}
                <col style="width:7%">  {{-- Women --}}
                <col style="width:7%">  {{-- Children --}}
                <col style="width:8%">  {{-- Weekly Off Days --}}
                <col style="width:8%">  {{-- Holiday Days --}}
                <col style="width:25%"> {{-- Attendance Dates (period) --}}
            </colgroup>
            <thead>
                <tr>
                    <th>Relay</th>
                    <th>Shift Name</th>
                    <th>Work From</th>
                    <th>Work To</th>
                    <th>Men</th>
                    <th>Women</th>
                    <th>Children</th>
                    <th>Weekly Off<br>Days</th>
                    <th>Holiday<br>Days</th>
                    <th>Working Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach($relayGroups as $i => $relay)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:left;padding:0 2px;">{{ $relay['shift_name'] ?? '' }}</td>
                    <td>{{ $relay['shift_start'] ?? '' }}</td>
                    <td>{{ $relay['shift_end'] ?? '' }}</td>
                    <td>{{ $relay['men'] > 0 ? $relay['men'] : '' }}</td>
                    <td>{{ $relay['women'] > 0 ? $relay['women'] : '' }}</td>
                    <td>{{ $relay['children'] > 0 ? $relay['children'] : '' }}</td>
                    <td>{{ $relay['weekly_off_days'] > 0 ? $relay['weekly_off_days'] : '' }}</td>
                    <td>{{ $relay['holiday_days'] > 0 ? $relay['holiday_days'] : '' }}</td>
                    <td style="text-align:left;padding:0 2px;font-size:5px;">
                        @php
                            $dates = $relay['attendance_dates'] ?? [];
                            sort($dates);
                            echo implode(', ', array_slice($dates, 0, 10));
                            if (count($dates) > 10) echo ' ...';
                        @endphp
                    </td>
                </tr>
                @endforeach
                {{-- Totals row --}}
                <tr style="font-weight:bold;">
                    <td colspan="4" style="text-align:right;padding:0 3px;">Total</td>
                    <td>{{ array_sum(array_column($relayGroups, 'men')) ?: '' }}</td>
                    <td>{{ array_sum(array_column($relayGroups, 'women')) ?: '' }}</td>
                    <td>{{ array_sum(array_column($relayGroups, 'children')) ?: '' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    <table class="sig">
        <tr>
            <td style="width:55%;vertical-align:bottom;">
                Date on which this notice was first exhibited: {{ $header['date_first_exhibited'] ?? '' }}
                &nbsp;&nbsp; Period: {{ $header['period'] ?? '' }}
            </td>
            <td style="width:45%;text-align:right;vertical-align:bottom;">
                <table cellspacing="0" style="width:150pt;margin-left:auto;border-collapse:collapse;margin-top:50px;">
                    <tr>
                        <td style="border-top:1px solid #000;text-align:center;font-weight:bold;padding-top:3px;font-size:7px;">
                            (Signed) Manager
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</td>
</tr>
</table>

</body>
</html>

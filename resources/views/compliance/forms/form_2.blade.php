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
    </style>
</head>
<body>

{{-- Outer border as a single table --}}
<table cellspacing="0" cellpadding="4" style="width:99%;margin-left:auto;margin-right:auto;border-top:2px solid #000;border-left:2px solid #000;border-right:2px solid #000;border-bottom:2px solid #000;">
<tr>
<td style="padding:4px;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">

    <div class="hdr">
        <div style="font-size:7px;">The Tamil Nadu Factories Rules</div>
        <div style="font-weight:bold;font-size:8px;">FORM 2</div>
        <div style="font-size:7px;">(Prescribed under Rule 79)</div>
        <div style="font-weight:bold;font-size:7px;">Notice of Periods of work for adult workers and children</div>
    </div>

    <table class="ft">
        <tr><td class="l">Name of factory</td><td>{{ $header['factory_name'] ?? $factory_name ?? 'NIL' }}</td></tr>
        <tr><td class="l">Place</td><td>{{ $header['place'] ?? $place ?? 'NIL' }}</td></tr>
        <tr><td class="l">District</td><td>{{ $header['district'] ?? $district ?? 'NIL' }}</td></tr>
    </table>

    {{-- 31 cols in 750pt: label=40pt, 27 data cols=20pt each(540pt), grp=20pt, nature=30pt = 40+540+20+30=630... pad remaining --}}
    <table class="mt">
        <colgroup>
            <col style="width:7%">
            @for($i=0;$i<27;$i++)<col style="width:2.9%">@endfor
            <col style="width:2.7%">
            <col style="width:4%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="4" class="lc">Periods<br>of work</th>
                <th colspan="9">Men</th>
                <th colspan="9">Women</th>
                <th colspan="9">Children</th>
                <th rowspan="2" colspan="2">Desc.<br>of groups</th>
            </tr>
            <tr>
                <th colspan="9">Total number of men employed</th>
                <th colspan="9">Total number of women employed</th>
                <th colspan="9">Total number of children employed</th>
            </tr>
            <tr>
                <th colspan="3">A</th><th colspan="3">B</th><th colspan="3">C</th>
                <th colspan="3">D</th><th colspan="3">E</th><th colspan="3">F</th>
                <th colspan="3">G</th><th colspan="3">H</th><th colspan="3">I</th>
                <th>Grp</th><th>Nature</th>
            </tr>
            <tr>
                @for($i=0;$i<27;$i++)<th>{{ ($i%3)+1 }}</th>@endfor
                <th></th><th></th>
            </tr>
            <tr>
                <th class="lc">Relays</th>
                @for($i=0;$i<27;$i++)<th>{{ ($i%3)+1 }}</th>@endfor
                <th></th><th></th>
            </tr>
        </thead>
        <tbody>
            @php $wg = ['A','B','C','D','E','F']; @endphp
            @foreach($wg as $i => $g)
            <tr>
                @if($i===0)<td class="lc" rowspan="6">On<br>working<br>days</td>@endif
                @for($c=0;$c<27;$c++)<td></td>@endfor
                <td>{{ $g }}</td><td></td>
            </tr>
            @endforeach
            @php $pg = ['G','H','I','','','']; @endphp
            @foreach($pg as $i => $g)
            <tr>
                @if($i===0)<td class="lc" rowspan="6">On<br>partial<br>working<br>days</td>@endif
                @for($c=0;$c<27;$c++)<td></td>@endfor
                <td>{{ $g }}</td><td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sig">
        <tr>
            <td style="width:55%;vertical-align:bottom;">Date on which this notice was first exhibited weekly holidays: ___________</td>
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

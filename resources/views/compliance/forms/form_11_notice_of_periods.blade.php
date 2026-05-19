<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 11 - Notice of Periods of Work</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 12px;
            font-size: 8px;
        }
        .form-container {
            border: 2px solid black;
            padding: 10px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 8px;
            font-size: 10px;
            line-height: 1.5;
        }
        .header-bold { font-weight: bold; }
        .header-red  { font-weight: bold; color: #c00; }

        /* Factory info rows */
        .factory-info { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .factory-info td { border: 1px solid black; padding: 2px 4px; font-size: 9px; }
        .factory-info .label { font-weight: bold; }

        /* Main work-periods table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 7.5px;
            margin-top: 0;
        }
        .main-table th,
        .main-table td {
            border: 1px solid black;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }
        .main-table th { font-weight: bold; background: #fff; }
        .relay-num { color: #c00; font-weight: bold; }
        .relay-num-blue { color: #00c; font-weight: bold; }
        .label-col { text-align: left; font-weight: bold; width: 80px; }
        .group-letter-col { font-weight: bold; }

        /* Footer */
        .footer-section {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin-left: auto;
            margin-top: 30px;
        }
        .signature-label { text-align: right; font-weight: bold; margin-top: 2px; }
    </style>
</head>
<body>
<div class="form-container">

    <!-- Header -->
    <div class="form-header">
        <div>The Tamil Nadu Factories Rules</div>
        <div class="header-bold">FORM 11</div>
        <div>(Prescribed under Rule 79)</div>
        <div class="header-red">Notice of Periods of work for adult workers and children</div>
    </div>

    <!-- Factory Details -->
    <table class="factory-info">
        <tr>
            <td class="label">Name of factory</td>
            <td colspan="3">{{ $factory_name ?? 'NIL' }}</td>
        </tr>
        <tr>
            <td class="label">Place</td>
            <td colspan="3">{{ $place ?? 'NIL' }}</td>
        </tr>
        <tr>
            <td class="label">District</td>
            <td colspan="3">{{ $district ?? 'NIL' }}</td>
        </tr>
    </table>

    <!-- Main Table -->
    <!--
        Column layout (left → right):
        1  : Periods of work  (rowspan label)
        2-4: Men A (1,2,3)
        5-7: Men B (1,2,3)
        8-10: Men C (1,2,3)
        11-13: Women D (1,2,3)
        14-16: Women E (1,2,3)
        17-19: Women F (1,2,3)
        20-22: Children G (1,2,3)
        23-25: Children H (1,2,3)
        26-28: Children I (1,2,3)
        29: Description of groups – Group letter
        30: Description of groups – Nature of work
    -->
    <table class="main-table">
        <thead>
            <!-- Row 1: Periods of work | Men (9 cols) | Women (9 cols) | Children (9 cols) | Description of groups (2 cols) -->
            <tr>
                <th rowspan="3" class="label-col">Periods of work</th>
                <th colspan="9">Men</th>
                <th colspan="9">Women</th>
                <th colspan="9">Children</th>
                <th rowspan="2" colspan="2">Description of groups</th>
            </tr>
            <!-- Row 2: Total number employed sub-headers -->
            <tr>
                <th colspan="9">Total number of men employed</th>
                <th colspan="9">Total number of women employed</th>
                <th colspan="9">Total number of children employed</th>
            </tr>
            <!-- Row 3: Group letters A B C | D E F | G H I | Group letter | Nature of work -->
            <tr>
                <th colspan="3">A</th>
                <th colspan="3">B</th>
                <th colspan="3">C</th>
                <th colspan="3">D</th>
                <th colspan="3">E</th>
                <th colspan="3">F</th>
                <th colspan="3">G</th>
                <th colspan="3">H</th>
                <th colspan="3">I</th>
                <th>Group<br>letter</th>
                <th>Nature of<br>work</th>
            </tr>
            <!-- Row 4: Relays 1 2 3 for each group -->
            <tr>
                <th>Groups</th>
                @foreach(['A','B','C'] as $g)
                    <th>1</th><th>2</th><th>3</th>
                @endforeach
                @foreach(['D','E','F'] as $g)
                    <th>1</th><th>2</th><th>3</th>
                @endforeach
                @foreach(['G','H','I'] as $g)
                    <th>1</th><th>2</th><th>3</th>
                @endforeach
                <th></th><th></th>
            </tr>
            <!-- Row 5: Relays label row -->
            <tr>
                <th>Relays</th>
                @for($i=0;$i<9;$i++)
                    @php $n = ($i % 3) + 1; @endphp
                    <th class="{{ $n==1 ? 'relay-num' : ($n==2 ? '' : 'relay-num-blue') }}">{{ $n }}</th>
                @endfor
                @for($i=0;$i<9;$i++)
                    @php $n = ($i % 3) + 1; @endphp
                    <th class="{{ $n==1 ? 'relay-num' : ($n==2 ? '' : 'relay-num-blue') }}">{{ $n }}</th>
                @endfor
                @for($i=0;$i<9;$i++)
                    @php $n = ($i % 3) + 1; @endphp
                    <th class="{{ $n==1 ? 'relay-num' : ($n==2 ? '' : 'relay-num-blue') }}">{{ $n }}</th>
                @endfor
                <th></th><th></th>
            </tr>
        </thead>
        <tbody>
            <!-- On working days: 6 data rows, label spans all 6 -->
            @php $workingGroups = ['A','B','C','D','E','F']; @endphp
            @for($row = 0; $row < 6; $row++)
                <tr>
                    @if($row === 0)
                        <td class="label-col" rowspan="6">On working days</td>
                    @endif
                    <!-- 27 empty data cells -->
                    @for($c = 0; $c < 27; $c++)
                        <td></td>
                    @endfor
                    <!-- Description of groups: Group letter + Nature of work -->
                    <td class="group-letter-col" style="color:{{ in_array($workingGroups[$row], ['A','B','C','D','E','F']) ? '#000' : '#c00' }}">
                        {{ $workingGroups[$row] }}
                    </td>
                    <td style="text-align:left;">{{ ${'group_'.strtolower($workingGroups[$row]).'_work'} ?? '' }}</td>
                </tr>
            @endfor

            <!-- On partial working days: 6 data rows, label spans all 6 -->
            @php $partialGroups = ['G','H','I','','',''];
                 $partialColors = ['#c00','#000','#000','','','']; @endphp
            @for($row = 0; $row < 6; $row++)
                <tr>
                    @if($row === 0)
                        <td class="label-col" rowspan="6">On partial working days</td>
                    @endif
                    @for($c = 0; $c < 27; $c++)
                        <td></td>
                    @endfor
                    @if(!empty($partialGroups[$row]))
                        <td class="group-letter-col" style="color:{{ $partialColors[$row] }}">{{ $partialGroups[$row] }}</td>
                        <td style="text-align:left;">{{ ${'group_'.strtolower($partialGroups[$row]).'_work'} ?? '' }}</td>
                    @else
                        <td></td><td></td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer-section">
        <div>
            Date on which this notice was first exhibited weekly holidays: <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
        </div>
        <div>
            <div class="signature-line"></div>
            <div class="signature-label">(Signed) Manager</div>
        </div>
    </div>

</div>
</body>
</html>

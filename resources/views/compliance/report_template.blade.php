<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Compliance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>

    <h1>Compliance Execution Report</h1>

    <p><strong>Batch ID:</strong> {{ $data['batch_id'] }}</p>
    <p><strong>Section:</strong> {{ $data['section_name'] ?? 'N/A' }}</p>
    <p><strong>Subscription:</strong> {{ $data['subscription_type'] ?? 'N/A' }}</p>
    <p><strong>Period:</strong> {{ $data['period_display'] ?? 'N/A' }}</p>
    <p><strong>Generated At:</strong> {{ $data['generated_at'] }}</p>

    <h3>Results</h3>

    <table>
        <thead>
            <tr>
                <th>Form Code</th>
                <th>Form Name</th>
                <th>Status</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @if (is_array($data['results']) && count($data['results']) > 0)
                @foreach ($data['results'] as $result)
                    <tr>
                        <td>{{ $result['form_code'] ?? 'N/A' }}</td>
                        <td>{{ $result['form_name'] ?? 'N/A' }}</td>
                        <td>{{ $result['status'] ?? 'N/A' }}</td>
                        <td>{{ $result['source'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">No results available</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>

</html>

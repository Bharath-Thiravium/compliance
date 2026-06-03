<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request Failed Safely</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        main {
            max-width: 720px;
            margin: 80px auto;
            padding: 32px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }

        p {
            margin: 0;
            line-height: 1.6;
            color: #475569;
        }
    </style>
</head>
<body>
    <main>
        <h1>Request Failed Safely</h1>
        <p>{{ $message ?? 'The request failed safely. Please check the application logs for details.' }}</p>
    </main>
</body>
</html>

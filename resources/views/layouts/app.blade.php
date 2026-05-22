<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap CSS — must be in <head> so the page renders styled on first paint --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f5f6fa;
        }
        .header {
            margin-bottom: 20px;
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="header">
        <h2>Labour Compliance Platform</h2>
    </div>

    @yield('content')
</div>

{{-- Bootstrap JS bundle (includes Popper) — must come BEFORE @stack('scripts') --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Page-level scripts pushed here so they always run after Bootstrap is defined --}}
@stack('scripts')

</body>
</html>

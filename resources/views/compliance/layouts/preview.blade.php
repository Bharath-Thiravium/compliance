<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $form_title ?? $form_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .preview-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .preview-content { background: white; padding: 2rem; margin: 2rem auto; max-width: 1200px; box-shadow: 0 0 20px rgba(0,0,0,0.1); border: 2px solid #000; }
        @media print { .preview-header, .no-print { display: none !important; } }
    </style>
</head>
</html>

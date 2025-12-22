<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 48px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 16px;
        }
        p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1>Internal Server Error</h1>
        <p>
            Oops! Something went wrong on our end. Our team has been notified and is working to fix the issue.
            Please try again later.
        </p>
        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-secondary">← Go Back</a>
            <a href="<?= adminUrl('dashboard.php') ?>" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>

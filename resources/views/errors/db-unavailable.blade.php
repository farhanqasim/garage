<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Unavailable</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 100vh; box-sizing: border-box; }
        .box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 420px; text-align: center; }
        h1 { margin: 0 0 0.5rem; font-size: 1.25rem; color: #333; }
        p { color: #666; margin: 0 0 1.5rem; line-height: 1.5; }
        .steps { text-align: left; background: #f9f9f9; padding: 1rem 1.25rem; border-radius: 6px; font-size: 0.9rem; color: #444; margin-bottom: 1.5rem; }
        .steps strong { display: block; margin-bottom: 0.25rem; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Database unavailable</h1>
        <p>The application could not connect to the database. This usually means MySQL is not running.</p>
        <div class="steps">
            <strong>What to do:</strong>
            1. Open <strong>XAMPP Control Panel</strong>.<br>
            2. Click <strong>Start</strong> next to MySQL.<br>
            3. Refresh this page.
        </div>
        <p><a href="{{ url()->current() }}">Refresh page</a></p>
    </div>
</body>
</html>

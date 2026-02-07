<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .content { background: #f9f9f9; padding: 24px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="body">
                {!! nl2br(e($body)) !!}
            </div>
        </div>
    </div>
</body>
</html>

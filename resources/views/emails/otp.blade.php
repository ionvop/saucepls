<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your SaucePls login code</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #111111; color: #e5e7eb; margin: 0; padding: 0; }
        .wrap { max-width: 480px; margin: 40px auto; background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 32px; }
        .brand { color: #5555AA; font-weight: 700; font-size: 20px; margin-bottom: 16px; }
        .code { font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #ffffff; background: #111111; border: 1px solid #2a2a2a; border-radius: 8px; padding: 16px; text-align: center; margin: 24px 0; }
        .muted { color: #9ca3af; font-size: 14px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">SaucePls</div>
        <p>Use the code below to sign in. It expires in 5 minutes.</p>
        <div class="code">{{ $code }}</div>
        <p class="muted">If you didn't request this code, you can safely ignore this email.</p>
    </div>
</body>
</html>

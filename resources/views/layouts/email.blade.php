<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #fdf2f4; margin: 0; padding: 0; color: #374151; }
        .wrapper { padding: 40px 16px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px -1px rgba(0,0,0,.06); }
        .header { padding: 36px 40px; text-align: center; color: #ffffff; }
        .header-brand { font-size: 18px; font-weight: 700; letter-spacing: -0.3px; margin: 0 0 12px; opacity: 0.9; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .body { padding: 36px 40px; line-height: 1.65; }
        .body p { margin: 0 0 14px; }
        .body p:last-child { margin-bottom: 0; }
        .details { margin: 24px 0; border: 1px solid #f3f4f6; border-radius: 8px; overflow: hidden; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 16px; border-bottom: 1px solid #f3f4f6; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: 600; font-size: 14px; color: #111827; }
        .value-total { font-size: 16px; font-weight: 700; color: #E11D48; }
        .value-success { color: #16a34a; }
        .value-danger { color: #dc2626; }
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; padding: 14px 32px; background: #E11D48; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .btn-success { background: #16a34a; }
        .btn-amber { background: #d97706; }
        .btn-sm { font-size: 14px; padding: 10px 24px; }
        .note { color: #6b7280; font-size: 13px; margin-top: 6px; line-height: 1.5; }
        .alert { padding: 16px 20px; margin: 20px 0; border-radius: 6px; }
        .alert-warning { background: #fffbeb; border-left: 3px solid #d97706; }
        .alert-warning strong { color: #92400e; display: block; margin-bottom: 6px; }
        .alert-warning p { color: #78350f; margin: 0; font-size: 14px; }
        .alert-error { background: #fff1f2; border-left: 3px solid #e11d48; }
        .alert-error strong { color: #9f1239; display: block; margin-bottom: 6px; }
        .alert-error p { color: #be123c; margin: 0; font-size: 14px; }
        .alert-info { background: #fff1f2; border-left: 3px solid #e11d48; }
        .alert-info p { margin: 0; font-size: 14px; color: #374151; }
        .steps { margin: 20px 0; padding: 0; list-style: none; }
        .steps li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f9fafb; font-size: 14px; color: #374151; }
        .steps li:last-child { border-bottom: none; }
        .step-num { flex-shrink: 0; width: 22px; height: 22px; background: #e11d48; color: #fff; border-radius: 50%; text-align: center; line-height: 22px; font-size: 11px; font-weight: 700; }
        .footer { background: #fdf2f4; border-top: 1px solid #fecdd3; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; }
        .footer a { color: #e11d48; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div class="header" style="background: @yield('header-bg', '#E11D48');">
            <p class="header-brand">Acomody</p>
            <h1>@yield('header-title')</h1>
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; {{ __('mail.copyright') }}
        </div>
    </div>
</div>
</body>
</html>

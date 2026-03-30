<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Acomody — Under Maintenance</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f7f4;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 560px;
            width: 100%;
            text-align: center;
        }

        .icon-wrapper {
            width: 72px;
            height: 72px;
            background-color: #f0ede6;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
        }

        .icon-wrapper svg {
            width: 36px;
            height: 36px;
            color: #6b5c45;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #fff8e6;
            border: 1px solid #f5d87a;
            color: #a07d1a;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 100px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #f0b429;
            border-radius: 50%;
            animation: pulse 1.8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .divider {
            width: 48px;
            height: 2px;
            background-color: #e5e0d8;
            margin: 40px auto;
            border-radius: 2px;
        }

        .footer-text {
            font-size: 13px;
            color: #999;
            margin-bottom: 0;
        }

        .footer-text a {
            color: #6b5c45;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
            </svg>
        </div>

        <h1>We'll be right back</h1>
        <p>
            Acomody is currently undergoing scheduled maintenance.<br>
            We're working hard to improve your experience and will be back online shortly.
        </p>

        <div class="status-badge">
            <span class="status-dot"></span>
            Maintenance in progress
        </div>

        <div class="divider"></div>

        <p class="footer-text">
            Questions? Contact us at
            <a href="mailto:support@acomody.com">support@acomody.com</a>
        </p>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acomody Superadmin — Set Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        body { background: #f5f5f5; }
        .set-password-box {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            padding: 32px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        h2 { margin-top: 0; margin-bottom: 24px; font-size: 22px; }
    </style>
</head>
<body>
<div class="set-password-box">
    <h2>Acomody Superadmin</h2>

    @if (!empty($success))
        <div class="alert alert-success">
            <strong>Password set successfully.</strong><br>
            You can now <a href="{{ url('/admin/dashboard') }}">log in to the admin panel</a>.
        </div>
    @else
        <p style="color:#555; margin-bottom:20px;">Set your password to activate your superadmin account.</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p style="margin:0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.set-password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="{{ old('email', $email) }}" required readonly>
            </div>

            <div class="form-group">
                <label for="password">New password</label>
                <input type="password" name="password" id="password" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Set password</button>
        </form>
    @endif
</div>
</body>
</html>

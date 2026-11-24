<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | CargoTrace</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:Arial,sans-serif;background:#f4f7fc;color:#17345f;display:flex;align-items:center;justify-content:center;padding:28px}
        .card{width:100%;max-width:480px;background:#fff;border:1px solid #e6edf7;border-radius:22px;padding:38px;box-shadow:0 12px 35px rgba(25,65,130,.08)}
        .logo{text-align:center;font-size:28px;font-weight:800;color:#2058d8;margin-bottom:8px}.sub{text-align:center;color:#8090aa;margin:0 0 28px;font-size:14px}
        h1{text-align:center;font-size:24px;margin:0 0 8px}.field{margin:17px 0}.field label{display:block;font-size:13px;font-weight:700;margin-bottom:8px}
        input{width:100%;height:48px;border:1px solid #dce5f2;border-radius:11px;padding:0 14px;font-size:14px;outline:none}input:focus{border-color:#2864e8;box-shadow:0 0 0 3px rgba(40,100,232,.1)}
        button{width:100%;height:48px;border:0;border-radius:11px;background:linear-gradient(90deg,#3168ef,#1452cf);color:white;font-size:15px;font-weight:700;cursor:pointer;margin-top:12px}
        .error{color:#d9364b;font-size:12px;margin-top:6px}.alert{background:#fff0f1;color:#bd2639;padding:12px;border-radius:9px;margin:15px 0;font-size:13px}.success{background:#eaf9f1;color:#167449;padding:12px;border-radius:9px;margin:15px 0;font-size:13px}
        .foot{text-align:center;color:#71829e;font-size:13px;margin-top:22px}.foot a{color:#2058d8;font-weight:700;text-decoration:none;margin-left:5px}
    </style>
</head>
<body>
<div class="card">
    <div class="logo">📦 CargoTrace</div>
    <h1>Create your account</h1>
    <p class="sub">Join CargoTrace to track your shipments</p>
    @if ($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    @if (session('success'))<div class="success">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="field"><label for="name">Full name</label><input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" required autocomplete="name">@error('name')<div class="error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email address" required autocomplete="email">@error('email')<div class="error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" placeholder="At least 8 characters" required autocomplete="new-password">@error('password')<div class="error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter your password" required autocomplete="new-password"></div>
        <button type="submit">Create CargoTrace Account</button>
    </form>
    <div class="foot">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
</div>
</body>
</html>

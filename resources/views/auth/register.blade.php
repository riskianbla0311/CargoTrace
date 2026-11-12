
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Account · CargoTrace</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --navy: #102b4b;
    --blue: #2864e8;
    --ink: #183253;
    --muted: #7788a2;
    --line: #dfe7f2;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'DM Sans', 'Segoe UI', Arial, sans-serif;
    color: var(--ink);
    background: #f1f5fb;
    overflow-x: hidden;
}

.auth-shell {
    min-height: 100svh;
    display: grid;
    grid-template-columns: 52% 48%;
    background: #fff;
}

.auth-visual {
    position: relative;
    overflow: hidden;
    min-height: 100svh;
    padding: 30px 8% 22px;
    display: grid;
    grid-template-rows: auto auto 1fr auto;
    background: linear-gradient(145deg, #102b4b 0%, #1d4f85 58%, #2d73ba 100%);
    color: #fff;
}

.auth-visual:before,
.auth-visual:after {
    content: "";
    position: absolute;
    border: 1px solid #ffffff12;
    border-radius: 50%;
    pointer-events: none;
}

.auth-visual:before {
    width: 570px;
    height: 570px;
    right: -280px;
    top: -220px;
}

.auth-visual:after {
    width: 740px;
    height: 740px;
    right: -360px;
    top: -300px;
}

.auth-brand {
    position: relative;
    z-index: 2;
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -.7px;
}

.auth-brand span {
    color: #72b1ff;
}

.auth-copy {
    position: relative;
    z-index: 2;
    max-width: 560px;
    margin-top: 48px;
}

.auth-eyebrow {
    font-size: 10px;
    letter-spacing: 2px;
    font-weight: 800;
    color: #9dc9ff;
    text-transform: uppercase;
    margin-bottom: 18px;
}

.auth-copy h1 {
    font-size: clamp(36px, 4vw, 54px);
    line-height: 1.08;
    letter-spacing: -1.8px;
    margin: 0 0 18px;
}

.auth-copy p {
    font-size: 14px;
    line-height: 1.75;
    color: #d3e4f8;
    max-width: 490px;
    margin: 0;
}

.auth-illustration {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    max-height: 100%;
    min-height: 0;
    max-width: 650px;
    object-fit: contain;
    object-position: center bottom;
    border-radius: 18px;
    filter: drop-shadow(0 12px 20px #06182d24);
    margin: 12px auto 0;
    align-self: end;
}

.auth-foot {
    position: relative;
    z-index: 2;
    font-size: 10px;
    line-height: 1.8;
    color: #a9c8e8;
    padding-top: 8px;
}

.auth-form-side {
    min-height: 100svh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 34px 6%;
    background: radial-gradient(circle at 100% 0%, #e5efff 0, transparent 40%), #f4f7fc;
}

.auth-card {
    width: 100%;
    max-width: 590px;
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 26px;
    padding: 35px 48px;
    box-shadow: 0 18px 55px #19385b12;
    position: relative;
}

.auth-card-head {
    text-align: center;
    margin-bottom: 22px;
}

.auth-parcel {
    width: 54px;
    height: 54px;
    margin: 0 auto 12px;
    border-radius: 17px;
    background: linear-gradient(145deg, #fff3df, #ffe1b6);
    display: grid;
    place-items: center;
    font-size: 29px;
    box-shadow: 0 7px 18px #e6a44b24;
}

.auth-card h2 {
    font-size: 30px;
    letter-spacing: -.8px;
    margin: 0 0 7px;
    color: #172f50;
    text-align: center;
}

.auth-subtitle {
    font-size: 12px;
    color: #8391a6;
    margin: 0;
    text-align: center;
    line-height: 1.6;
}

.auth-field {
    margin-bottom: 15px;
}

.auth-field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #29415f;
    margin-bottom: 8px;
}

.auth-input-wrap {
    position: relative;
}

.auth-input {
    width: 100%;
    height: 46px;
    border: 1px solid #dce5f0;
    border-radius: 10px;
    padding: 0 14px;
    font: inherit;
    font-size: 13px;
    color: #233d5d;
    background: #fff;
    outline: none;
    transition: .2s;
}

.auth-input::placeholder {
    color: #9aa7b8;
    opacity: 1;
}

.auth-input:focus {
    border-color: #7da7ff;
    box-shadow: 0 0 0 3px #2864e81c;
}

.auth-input.has-toggle {
    padding-right: 48px;
}

.auth-toggle {
    position: absolute;
    right: 9px;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: #7c8da5;
    cursor: pointer;
    font-size: 12px;
    padding: 7px;
}

.auth-role-note {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 11px 12px;
    border-radius: 9px;
    background: #eff6ff;
    color: #527092;
    font-size: 10px;
    line-height: 1.5;
    margin: 3px 0 17px;
}

.auth-submit {
    width: 100%;
    height: 49px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(90deg, #2864e8, #1555cc);
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 8px 18px #2864e82a;
    transition: .2s;
}

.auth-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 22px #2864e83a;
}

.auth-switch {
    text-align: center;
    font-size: 10px;
    color: #7889a1;
    margin: 20px 0 0;
}

.auth-switch a {
    color: var(--blue);
    font-weight: 800;
    margin-left: 5px;
    text-decoration: none;
}

.auth-switch a:hover {
    text-decoration: underline;
}

.auth-error {
    font-size: 11px;
    color: #b42318;
    margin-top: 6px;
}

.auth-alert {
    padding: 10px 12px;
    border-radius: 9px;
    background: #fff1f0;
    color: #b42318;
    font-size: 12px;
    margin-bottom: 16px;
}

@media(max-width: 850px) {
    .auth-shell {
        grid-template-columns: 1fr;
    }

    .auth-visual {
        min-height: auto;
        padding: 25px 7% 12px;
        grid-template-rows: auto auto auto auto;
    }

    .auth-brand {
        font-size: 22px;
    }

    .auth-copy {
        margin-top: 28px;
    }

    .auth-copy h1 {
        font-size: 34px;
    }

    .auth-copy p {
        font-size: 12px;
    }

    .auth-illustration {
        height: 230px;
        max-height: 230px;
        margin: 8px auto 0;
    }

    .auth-form-side {
        min-height: 0;
        padding: 30px 7% 42px;
    }

    .auth-card {
        max-width: 500px;
        padding: 32px 30px;
        border-radius: 22px;
    }
}

@media(max-width: 480px) {
    .auth-visual {
        padding: 22px 22px 10px;
    }

    .auth-copy h1 {
        font-size: 30px;
    }

    .auth-illustration {
        height: 185px;
        max-height: 185px;
    }

    .auth-form-side {
        padding: 24px 16px 34px;
    }

    .auth-card {
        padding: 27px 22px;
    }

    .auth-card h2 {
        font-size: 26px;
    }
}
</style>
</head>

<body>

<div class="auth-shell">

    {{-- LEFT SIDE --}}
    <section class="auth-visual">

        <div class="auth-brand">
            Cargo<span>Trace</span>
        </div>

        <div class="auth-copy">
            <div class="auth-eyebrow">
                Connected Cargo Visibility Platform
            </div>

            <h1>
                Every shipment.<br>
                One clear view.
            </h1>

            <p>
                Track cargo journeys, monitor delivery progress,
                and stay informed at every step of your supply chain.
            </p>
        </div>

        <img
            class="auth-illustration"
            src="{{ asset('images/cargotrace-auth-illustration.png') }}"
            alt="Ilustrasi CargoTrace">

        <div class="auth-foot">
            Visibility that goes the distance.<br>
            Built for modern supply chains.
        </div>

    </section>

    {{-- RIGHT SIDE --}}
    <main class="auth-form-side">

        <form
            class="auth-card"
            method="POST"
            action="{{ route('register.store') }}">

            @csrf

            <div class="auth-card-head">

                <div class="auth-parcel" aria-hidden="true">
                    📦
                </div>

                <h2>Create your account</h2>

                <p class="auth-subtitle">
                    Join CargoTrace and keep track of your deliveries.
                </p>

            </div>

            {{-- ERROR MESSAGE --}}
            @if ($errors->any())
                <div class="auth-alert">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- FULL NAME --}}
            <div class="auth-field">

                <label for="name">Full name</label>

                <input
                    class="auth-input"
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    required
                    autofocus>

                @error('name')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

            </div>

            {{-- EMAIL ADDRESS --}}
            <div class="auth-field">

                <label for="email">Email address</label>

                <input
                    class="auth-input"
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your registered email address"
                    autocomplete="email"
                    required>

                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

            </div>

            {{-- PASSWORD --}}
            <div class="auth-field">

                <label for="password">Password</label>

                <div class="auth-input-wrap">

                    <input
                        class="auth-input has-toggle"
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Enter a password (minimum 8 characters)"
                        autocomplete="new-password"
                        required>

                    <button
                        class="auth-toggle"
                        type="button"
                        data-toggle-password="password">
                        Show
                    </button>

                </div>

                @error('password')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

            </div>

            {{-- CONFIRM PASSWORD --}}
            <div class="auth-field">

                <label for="password_confirmation">
                    Confirm password
                </label>

                <div class="auth-input-wrap">

                    <input
                        class="auth-input has-toggle"
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                        required>

                    <button
                        class="auth-toggle"
                        type="button"
                        data-toggle-password="password_confirmation">
                        Show
                    </button>

                </div>

            </div>

            {{-- CUSTOMER REGISTRATION NOTICE --}}
            <div class="auth-role-note">
                <span>🔒</span>

                <span>
                    Registration is for <strong>Customer accounts</strong>.
                    Admin and Operator accounts are managed by the
                    CargoTrace administrator.
                </span>
            </div>

            {{-- REGISTER BUTTON --}}
            <button class="auth-submit" type="submit">
                Create Customer Account →
            </button>

            {{-- LOGIN LINK --}}
            <div class="auth-switch">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </div>

        </form>

    </main>

</div>

{{-- SHOW / HIDE PASSWORD --}}
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function(button) {

    button.addEventListener('click', function() {

        const input = document.getElementById(
            button.dataset.togglePassword
        );

        if (!input) return;

        input.type = input.type === 'password' ? 'text' : 'password';

        button.textContent =
            input.type === 'password' ? 'Show' : 'Hide';

    });

});
</script>

</body>
</html>

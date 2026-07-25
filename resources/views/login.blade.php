<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KM EIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4f7df7;
            --primary-blue-dark: #3f6ee8;
            --primary-blue-soft: #eef4ff;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border-soft: #dfe5ef;
            --input-bg: #f3f6fb;
            --card-shadow: 0 22px 55px rgba(24, 49, 94, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        .login-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.12) 0 14%, transparent 15%),
                linear-gradient(135deg, #4d82fb 0%, #4779f3 45%, #3f70ed 100%);
            isolation: isolate;
        }

        .login-page::before,
        .login-page::after {
            content: "";
            position: absolute;
            z-index: -1;
            border-radius: 38% 62% 56% 44% / 44% 42% 58% 56%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .login-page::before {
            width: 620px;
            height: 620px;
            top: -170px;
            right: -120px;
            transform: rotate(18deg);
        }

        .login-page::after {
            width: 540px;
            height: 540px;
            bottom: -190px;
            left: -120px;
            transform: rotate(-14deg);
        }

        .blob {
            position: absolute;
            z-index: -1;
            border-radius: 32% 68% 70% 30% / 30% 42% 58% 70%;
            background: rgba(255, 255, 255, 0.09);
            pointer-events: none;
        }

        .blob-1 {
            width: 310px;
            height: 310px;
            left: 8%;
            top: 23%;
            transform: rotate(-19deg);
        }

        .blob-2 {
            width: 420px;
            height: 420px;
            right: 8%;
            bottom: 10%;
            transform: rotate(21deg);
            background: rgba(34, 91, 214, 0.12);
        }

        .login-card {
            width: min(100%, 445px);
            position: relative;
            padding: 42px 40px 38px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        .brand {
            text-align: center;
            font-size: 29px;
            font-weight: 800;
            letter-spacing: -0.8px;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .brand span {
            color: var(--primary-blue);
        }

        .login-title {
            text-align: center;
            font-size: 23px;
            font-weight: 700;
            margin: 18px 0 4px;
            color: #20242b;
        }

        .login-subtitle {
            text-align: center;
            margin: 0 0 26px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            height: 45px;
            border-radius: 9px;
            border: 1px solid var(--border-soft);
            background: var(--input-bg);
            font-size: 14px;
            color: var(--text-dark);
            padding: 10px 13px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 125, 247, 0.14);
        }

        .login-button {
            width: 100%;
            height: 45px;
            margin-top: 18px;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #5d8cff 100%);
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            box-shadow: 0 12px 22px rgba(79, 125, 247, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
            box-shadow: 0 16px 28px rgba(79, 125, 247, 0.32);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            font-size: 13px;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 34px 24px 30px;
                border-radius: 18px;
            }

            .brand {
                font-size: 26px;
            }

            .login-title {
                font-size: 21px;
            }

            .blob-1,
            .blob-2 {
                opacity: 0.7;
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>

        <section class="login-card" aria-label="Login KM EIMS">
            <div class="brand"><span>KM</span> EIMS</div>
            <h1 class="login-title">Login</h1>

            @if(session()->has('loginError'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('loginError') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="/login" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="login-button">Login</button>
            </form>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

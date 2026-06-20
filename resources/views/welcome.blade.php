<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sementara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h1 class="text-success">Login Berhasil! 🎉</h1>
                
                @auth
                    <h3 class="mt-4">Selamat datang, <b>{{ auth()->user()->username }}</b>!</h3>
                    <p class="fs-5">Role Anda di sistem ini adalah: <span class="badge bg-primary">{{ auth()->user()->role }}</span></p>

                    <form action="/logout" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                @endauth

            </div>
        </div>
    </div>
</body>
</html>
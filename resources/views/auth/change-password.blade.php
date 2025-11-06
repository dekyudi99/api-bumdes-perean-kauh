<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh">
    <div class="card shadow p-4" style="width: 400px">
        <h4 class="text-center mb-3">Ganti Password</h4>
        <form method="POST" action="{{ route('password.change') }}">
            @csrf
            <input type="hidden" name="token" value="{{ request('token') }}">
            
            <div class="mb-3">
                <label>Password Baru</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirmPassword" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Perbarui Password</button>
        </form>
    </div>
</body>
</html>

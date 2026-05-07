<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Pasien - Rekam Medis Digital</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #e0f2f1, #b2dfdb);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .register-box {
            background: white;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            width: 450px;
            text-align: center;
        }
        h2 { color: #00796b; margin-bottom: 15px; }
        label { display: block; text-align: left; margin-top: 15px; font-weight: 600; color: #004d40; }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            background-color: #00796b;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            margin-top: 25px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background-color: #004d40; }
        .link { margin-top: 15px; font-size: 14px; }
        a { color: #00796b; text-decoration: none; }
        .error {
            background: #ffcdd2;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Formulir Registrasi Pasien</h2>
        
        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ url('/register/pasien') }}">
            @csrf
            <label>Nama Lengkap</label>
            <input type="text" name="name" required>
            <label>NIK</label>
            <input type="text" name="nik" maxlength="16" pattern="[0-9]{16}" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" required>

            <button type="submit">Daftar Sekarang</button>
        </form>
        <div class="link">
            <a href="{{ url('/home') }}">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
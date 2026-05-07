<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Login - Rekam Medis Digital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #e0f2f1, #b2dfdb);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        h1 {
            color: #00796b;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .login-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .login-card {
            background: #f5f8f7;
            padding: 25px 20px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s;
            display: block;
        }
        .login-card:hover {
            background: #e0f2f1;
            transform: translateY(-5px);
        }
        .login-card h3 {
            color: #00796b;
            font-size: 20px;
            margin-bottom: 8px;
        }
        .login-card p {
            color: #666;
            font-size: 12px;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #00796b;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 500px) {
            .container { padding: 25px; }
            .login-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 Rekam Medis Digital</h1>
        <p class="subtitle">Silakan pilih jenis login sesuai dengan peran Anda</p>
        
        <div class="login-grid">
            <a href="{{ route('login.pasien') }}" class="login-card">
                <h3>👤 Pasien</h3>
                <p>Login sebagai pasien</p>
            </a>
            <a href="{{ route('login.dokter') }}" class="login-card">
                <h3>👨‍⚕️ Dokter</h3>
                <p>Login sebagai dokter</p>
            </a>
            <a href="{{ route('login.perawat') }}" class="login-card">
                <h3>👩‍⚕️ Perawat</h3>
                <p>Login sebagai perawat</p>
            </a>
            <a href="{{ route('login.admin') }}" class="login-card">
                <h3>👑 Admin</h3>
                <p>Login sebagai administrator</p>
            </a>
        </div>
        
        <a href="{{ url('/home') }}" class="back-link">← Kembali ke Beranda</a>
    </div>
</body>
</html>
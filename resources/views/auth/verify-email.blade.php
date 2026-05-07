<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Rekam Medis Digital</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #e0f2f1, #b2dfdb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .verify-box {
            background: white;
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            width: 450px;
            text-align: center;
        }
        h2 { color: #00796b; margin-bottom: 10px; }
        .alert-success {
            background: #c8e6c9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .alert-error {
            background: #ffcdd2;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        button {
            background-color: #00796b;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background-color: #004d40; }
        .info {
            color: #666;
            font-size: 14px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="verify-box">
        <h2>Verifikasi Email Anda</h2>
        
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <p>Sebelum melanjutkan, silakan cek email Anda untuk tautan verifikasi.</p>
        <p class="info">Tidak menerima email? Klik tombol di bawah untuk mengirim ulang.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">Kirim Ulang Email Verifikasi</button>
        </form>

        <div class="link" style="margin-top: 20px;">
            <a href="{{ url('/logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                ← Logout
            </a>
            <form id="logout-form" method="POST" action="{{ url('/logout') }}" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</body>
</html>
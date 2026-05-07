<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Dokter - Rekam Medis Digital</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #b8e2db, #d7f0ec);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 450px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #0b7b6a; margin-bottom: 25px; }
        label { font-weight: 600; color: #333; margin-top: 15px; display: block; }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #c9c9c9;
            font-size: 15px;
        }
        .btn {
            width: 100%;
            background-color: #0b7b6a;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
        }
        .btn:hover { background-color: #0a6a5c; }
        .back { text-align: center; margin-top: 12px; }
        .back a { text-decoration: none; color: #0b7b6a; }
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
    <div class="container">
        <h2>Formulir Registrasi Dokter</h2>
        
        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ url('/register/dokter') }}">
            @csrf
            <label>Nama Lengkap</label>
            <input type="text" name="name" required>
            <label>ID Dokter</label>
            <input type="text" name="id_dokter" required>
            <label>Poli</label>
            <select name="poli" required>
                <option value="">Pilih poli</option>
                <option>Poli Umum</option>
                <option>Poli Gigi</option>
                <option>Poli Anak</option>
                <option>Poli Mata</option>
                <option>Poli Kandungan</option>
            </select>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit" class="btn">Daftar Sekarang</button>
        </form>
        <div class="back">
            <a href="{{ url('/home') }}">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
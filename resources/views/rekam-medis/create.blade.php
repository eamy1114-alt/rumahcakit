<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis Pasien - Rekam Medis Digital</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(120deg, #e0f2f1, #b2dfdb);
            color: #004d40;
        }
        header {
            background: linear-gradient(90deg, #009688, #4db6ac);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
        }
        .nav-right a {
            color: white;
            text-decoration: none;
            background: rgba(0,0,0,0.1);
            padding: 8px 14px;
            border-radius: 6px;
        }
        .container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar {
            width: 260px;
            background: white;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-sidebar {
            text-align: center;
            border-bottom: 2px solid #b2dfdb;
            padding-bottom: 20px;
            width: 100%;
        }
        .profile-sidebar img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #009688;
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            width: 100%;
            padding: 10px 15px;
            color: #004d40;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .sidebar a:hover { background: #b2dfdb; }
        .content { flex: 1; padding: 40px; }
        .rekam-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 800px;
        }
        .rekam-item { margin-top: 15px; }
        label { font-weight: 600; color: #004d40; display: block; }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #b2dfdb;
        }
        button {
            background: #009688;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
        footer {
            background: linear-gradient(90deg, #009688, #4db6ac);
            color: white;
            text-align: center;
            padding: 12px;
        }
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .sidebar { width: 100%; flex-direction: row; flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <header>
        <h2>Rekam Medis Pasien</h2>
        <div class="nav-right">
            <a href="{{ url('/dashboard/dokter') }}">🏠 Dashboard</a>
            <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none; border:none; color:white; cursor:pointer;">🚪 Logout</button>
            </form>
        </div>
    </header>

    <div class="container">
        <div class="sidebar">
            <div class="profile-sidebar">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png">
                <h4>dr. {{ auth()->user()->name ?? 'Eka Aditya' }}</h4>
                <p>Poli Umum</p>
            </div>
            <a href="#">📋 Rekam Medis</a>
            <a href="#">📄 Laporan</a>
        </div>

        <div class="content">
            <h2>Formulir Rekam Medis Pasien</h2>
            <div class="rekam-container">
                <form method="POST" action="{{ url('/rekam-medis') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pasien_id" value="{{ $pasien->id ?? '' }}">
                    
                    <div class="rekam-item">
                        <label>Nama Pasien</label>
                        <input type="text" value="{{ $pasien->nama_lengkap ?? '' }}" readonly>
                    </div>
                    <div class="rekam-item">
                        <label>Diagnosa *</label>
                        <textarea name="diagnosa" required></textarea>
                    </div>
                    <div class="rekam-item">
                        <label>Obat yang Diberikan *</label>
                        <textarea name="obat" required></textarea>
                    </div>
                    <div class="rekam-item">
                        <label>Alergi Pasien</label>
                        <input type="text" name="alergi">
                    </div>
                    <div class="rekam-item">
                        <label>Foto Rontgen / Scan (JPG, PNG)</label>
                        <input type="file" name="foto_rontgen" accept="image/*">
                    </div>
                    <div class="rekam-item">
                        <label>Hasil Lab (PDF)</label>
                        <input type="file" name="hasil_lab" accept="application/pdf">
                    </div>
                    <div class="rekam-item">
                        <label>Dokter *</label>
                        <select name="dokter_id" required>
                            <option value="">-- Pilih Dokter --</option>
                            @foreach($dokters as $dokter)
                                <option value="{{ $dokter->id }}">
                                    dr. {{ $dokter->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rekam-item">
                        <label>Rumah Sakit</label>
                        <input type="text" name="rumah_sakit" value="RSUD Banyumas" required>
                    </div>
                    <div class="rekam-item">
                        <label>Tanggal Pemeriksaan</label>
                        <input type="date" name="tanggal_pemeriksaan" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit">💾 Simpan Rekam Medis</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        © 2025 Rekam Medis Digital | Data Kesehatan Anda Aman & Terlindungi
    </footer>
</body>
</html>
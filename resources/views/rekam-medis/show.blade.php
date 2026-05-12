<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Rekam Medis - Rekam Medis Digital</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #e0f2f1, #b2dfdb);
            color: #004d40;
            min-height: 100vh;
        }
        header {
            background: linear-gradient(90deg, #009688, #4db6ac);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { color: #00796b; margin-bottom: 20px; border-bottom: 2px solid #b2dfdb; padding-bottom: 10px; }
        .info { margin-bottom: 15px; }
        .info strong { display: inline-block; width: 140px; color: #004d40; }
        .btn {
            background: #00796b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            
        }
        .btn-warning {
            background: #ffb74d;
        }
        .btn-warning:hover {
            background: #f57c00;
        }
        .btn:hover { background: #004d40; }
        footer {
            background: linear-gradient(90deg, #009688, #4db6ac);
            color: white;
            text-align: center;
            padding: 12px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    @php
    use Illuminate\Support\Facades\Storage;
    @endphp
    <header>
        <h2>📋 Detail Rekam Medis</h2>
        <div>
            {{ auth()->user()->name }}
            <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none; border:none; color:white; cursor:pointer;">Logout</button>
            </form>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Rekam Medis Pasien</h2>
            
            <div class="info"><strong>Tanggal Pemeriksaan:</strong> {{ \Carbon\Carbon::parse($rekamMedis->tanggal_pemeriksaan)->format('d F Y') }}</div>
            <div class="info"><strong>Nama Pasien:</strong> {{ $rekamMedis->pasien->nama_lengkap ?? '-' }}</div>
            <div class="info"><strong>Usia:</strong> {{ $rekamMedis->pasien->usia ?? '-' }} tahun</div>
            <div class="info"><strong>Jenis Kelamin:</strong> {{ $rekamMedis->pasien->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
            <div class="info"><strong>Diagnosa:</strong> {{ $rekamMedis->diagnosa }}</div>
            <div class="info"><strong>Obat:</strong> {{ $rekamMedis->obat }}</div>
            <div class="info"><strong>Alergi:</strong> {{ $rekamMedis->alergi ?? 'Tidak ada' }}</div>
            <div class="info"><strong>Rumah Sakit:</strong> {{ $rekamMedis->rumah_sakit }}</div>
            <div class="info"><strong>Dokter:</strong> {{ $rekamMedis->dokter->name ?? '-' }}</div>
            
           <!-- Foto Rontgen -->
            @if($rekamMedis->foto_rontgen)

            {{ dd(Storage::url($rekamMedis->foto_rontgen)) }}

            <div class="info"><strong>Foto Rontgen:</strong></div>

            <a href="{{ Storage::url($rekamMedis->foto_rontgen) }}" target="_blank" class="btn btn-warning">
                📷 Lihat Rontgen
            </a>

            <br>
            @endif
            <!-- Hasil Lab -->
            @if($rekamMedis->hasil_lab)
            <div class="info"><strong>Hasil Lab:</strong></div>
            <a href="{{ asset('storage/' . $rekamMedis->hasil_lab) }}" target="_blank" class="btn btn-warning">
                📄 Download Hasil Lab (PDF)
            </a>
            @endif
            
            <div style="margin-top: 25px;">
                <a href="{{ url()->previous() }}" class="btn">← Kembali</a>
            </div>
        </div>
    </div>

    <footer>
        © 2025 Rekam Medis Digital | Data Kesehatan Anda Aman & Terlindungi | Enkripsi AES-256
    </footer>
</body>
</html>
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Models\DataVK;
use App\Models\LogAktivitas;
use App\Models\AksesRekamMedis;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin (Lengkap dengan CRUD & Logs)
     */
    public function admin()
    {
        $totalUsers = User::count();
        $totalDokter = User::where('role', 'dokter')->count();
        $totalPerawat = User::where('role', 'perawat')->count();
        $totalPasienUser = User::where('role', 'pasien')->count();
        $totalPasiens = Pasien::count();
        $totalRekamMedis = RekamMedis::count();
        $totalDataVK = DataVK::count();
        $totalLogs = LogAktivitas::count();
        
        $recentDataVK = DataVK::latest()->limit(10)->get();
        $dataVK = DataVK::orderBy('tanggal', 'desc')->paginate(20);
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        $logs = LogAktivitas::with('user')->orderBy('created_at', 'desc')->paginate(30);
        
        return view('dashboard.admin', compact(
            'totalUsers', 'totalDokter', 'totalPerawat', 'totalPasienUser',
            'totalPasiens', 'totalRekamMedis', 'totalDataVK', 'totalLogs',
            'recentDataVK', 'dataVK', 'users', 'logs'
        ));
    }

    
    /**
     * Dashboard Dokter
     * Menampilkan pasien sesuai dokter yang login
     */
    public function dokter()
    {
        $dokterId = auth()->id();

    // 🔥 HANYA pasien milik dokter yang login
        $antrianPasien = Pasien::where('status', 'diproses')
            ->where('dokter_id', $dokterId)
            ->latest()
            ->get();

    // 🔥 Total pasien dokter ini saja
        $totalPasien = Pasien::where('dokter_id', $dokterId)->count();

    // 🔥 Rekam medis dokter ini
        $totalRekamMedis = RekamMedis::where('dokter_id', $dokterId)->count();

    // 🔥 Rekam medis terbaru dokter ini
        $rekamMedisTerbaru = RekamMedis::where('dokter_id', $dokterId)
            ->latest()
            ->limit(5)
            ->with('pasien')
            ->get();

        return view('dashboard.dokter', compact(
            'antrianPasien',
            'totalPasien',
            'totalRekamMedis',
            'rekamMedisTerbaru'
        ));
    }

    /**
     * Dashboard Perawat
     */
    public function perawat()
    {
        $pasiens = Pasien::with('perawat', 'dokter')->latest()->get();
        $totalPasienHariIni = Pasien::whereDate('created_at', today())->count();
        $totalPasienMenunggu = Pasien::where('status', 'menunggu')->count();
        $totalPasienDiproses = Pasien::where('status', 'diproses')->count();
        $totalPasienSelesai = Pasien::where('status', 'selesai')->count();
        
        return view('dashboard.perawat', compact(
            'pasiens', 'totalPasienHariIni', 'totalPasienMenunggu',
            'totalPasienDiproses', 'totalPasienSelesai'
        ));
    }

    /**
     * Dashboard Pasien
     * Mencari data pasien berdasarkan user_id atau nama lengkap
     */
    public function pasien()
    {
        $user = auth()->user();
        
        // Cari data pasien berdasarkan user_id ATAU nama lengkap
        $dataPasien = Pasien::where('user_id', $user->id)
            ->orWhere('nama_lengkap', $user->name)
            ->first();
        
        $rekamMedis = [];
        if ($dataPasien) {
            $rekamMedis = RekamMedis::where('pasien_id', $dataPasien->id)
                ->with('dokter')
                ->latest()
                ->get();
        }
        
        // Akses requests untuk pasien (permintaan dari dokter)
        $aksesRequests = AksesRekamMedis::where('pasien_id', $user->id)
            ->where('status', 'pending')
            ->with('dokter')
            ->get();
        
        // Riwayat keluhan pasien
        $riwayatKeluhan = Pasien::where('user_id', $user->id)
            ->orWhere('nama_lengkap', $user->name)
            ->latest()
            ->get();
        
        return view('dashboard.pasien', compact('rekamMedis', 'aksesRequests', 'riwayatKeluhan', 'dataPasien'));
    }
}
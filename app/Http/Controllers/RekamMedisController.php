<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Models\LogAktivitas;
use App\Models\AksesRekamMedis;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RekamMedisController extends Controller
{
    use LogsActivity;

    /**
     * Form buat rekam medis baru (Dokter)
     */
    public function create($pasienId)
    {
        $pasien = Pasien::findOrFail($pasienId);
        $dokters = User::where('role', 'dokter')->get();
        return view('rekam-medis.create', compact('pasien'));
    }

    /**
     * Simpan rekam medis (Dokter)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pasien_id' => 'required|exists:pasiens,id',
            'diagnosa' => 'required|string',
            'obat' => 'required|string',
            'alergi' => 'nullable|string',
            'rumah_sakit' => 'required|string|max:255',
            'foto_rontgen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'hasil_lab' => 'nullable|mimes:pdf|max:5120',
            'tanggal_pemeriksaan' => 'required|date',
        ]);
        
        // Upload file foto rontgen (dengan nama unik)
        if ($request->hasFile('foto_rontgen')) {
            $file = $request->file('foto_rontgen');
            $filename = time() . '_rontgen_' . $file->getClientOriginalName();
            $path = $file->storeAs('rontgen', $filename, 'public');
            $validated['foto_rontgen'] = $path;
        }
        
        // Upload file hasil lab (dengan nama unik)
        if ($request->hasFile('hasil_lab')) {
            $file = $request->file('hasil_lab');
            $filename = time() . '_lab_' . $file->getClientOriginalName();
            $path = $file->storeAs('hasil_lab', $filename, 'public');
            $validated['hasil_lab'] = $path;
        }
        
        $validated['dokter_id'] = auth()->id();
        
        $rekamMedis = RekamMedis::create($validated);
        
        // Update status pasien menjadi selesai
        $pasien = Pasien::find($validated['pasien_id']);
        $pasien->update([
            'status' => 'selesai',
            'dokter_id' => auth()->id()
        ]);
        
        // Log aktivitas
        $this->logActivity('Membuat rekam medis untuk pasien: ' . $pasien->nama_lengkap);
        
        return redirect()->route('dashboard.dokter')
            ->with('success', 'Rekam medis berhasil disimpan!');
    }

    /**
     * Lihat rekam medis pasien (untuk pasien)
     */
    public function myRecords()
    {
        $user = auth()->user();
        
        // Cari pasien berdasarkan user_id atau nama
        $pasien = Pasien::where('user_id', $user->id)
            ->orWhere('nama_lengkap', $user->name)
            ->first();
        
        if (!$pasien) {
            return redirect()->back()
                ->with('error', 'Data pasien tidak ditemukan.');
        }
        
        $rekamMedis = RekamMedis::where('pasien_id', $pasien->id)
            ->with('dokter')
            ->latest()
            ->get();
        
        return view('rekam-medis.my-records', compact('rekamMedis'));
    }

    /**
     * Lihat detail rekam medis (Dokter & Pasien) - DENGAN CEK EXPIRED
     */
    public function show($id)
    {
        $rekamMedis = RekamMedis::with('pasien', 'dokter')->findOrFail($id);
        $user = auth()->user();
        
        // ADMIN: bisa melihat semua
        if ($user->role === 'admin') {
            return view('rekam-medis.show', compact('rekamMedis'));
        }
        
        // DOKTER: bisa melihat rekam medis yang dibuat sendiri atau yang sudah dapat akses (dan belum expired)
        if ($user->role === 'dokter') {
            // Cek apakah dokter ini yang membuat rekam medis
            if ($rekamMedis->dokter_id == $user->id) {
                return view('rekam-medis.show', compact('rekamMedis'));
            }
            
            // Cek apakah ada akses yang disetujui dari pasien dan belum expired
            $pasienUserId = $rekamMedis->pasien->user_id ?? null;
            if ($pasienUserId) {
                $akses = AksesRekamMedis::where('dokter_id', $user->id)
                    ->where('pasien_id', $pasienUserId)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expired_at')
                          ->orWhere('expired_at', '>', Carbon::now());
                    })
                    ->exists();
                    
                if ($akses) {
                    return view('rekam-medis.show', compact('rekamMedis'));
                }
            }
            
            abort(403, 'Akses Anda telah kedaluwarsa atau tidak memiliki izin.');
        }
        
        // PASIEN: bisa melihat rekam medis milik sendiri
        if ($user->role === 'pasien') {
            // Cari pasien berdasarkan user_id
            $pasien = Pasien::where('user_id', $user->id)
                ->orWhere('nama_lengkap', $user->name)
                ->first();
            
            if ($pasien && $rekamMedis->pasien_id == $pasien->id) {
                return view('rekam-medis.show', compact('rekamMedis'));
            }
            
            abort(403, 'Anda tidak memiliki akses ke rekam medis ini.');
        }
        
        // PERAWAT: bisa melihat semua rekam medis
        if ($user->role === 'perawat') {
            return view('rekam-medis.show', compact('rekamMedis'));
        }
        
        abort(403, 'Anda tidak memiliki akses ke rekam medis ini.');
    }

    /**
     * Lihat semua rekam medis pasien (untuk dokter yang sudah dapat akses) - DENGAN CEK EXPIRED
     */
    public function pasienRecords($pasienId)
    {
        $user = auth()->user();
        $pasien = Pasien::findOrFail($pasienId);
        
        // DOKTER: cek akses yang disetujui dan belum expired
        if ($user->role === 'dokter') {
            $pasienUserId = $pasien->user_id ?? null;
            if ($pasienUserId) {
                $akses = AksesRekamMedis::where('dokter_id', $user->id)
                    ->where('pasien_id', $pasienUserId)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expired_at')
                          ->orWhere('expired_at', '>', Carbon::now());
                    })
                    ->exists();
                    
                if (!$akses && $rekamMedis->dokter_id != $user->id) {
                    abort(403, 'Akses Anda telah kedaluwarsa atau tidak memiliki izin.');
                }
            } else if ($rekamMedis->dokter_id != $user->id) {
                abort(403, 'Anda tidak memiliki akses ke rekam medis pasien ini.');
            }
        }
        
        $rekamMedis = RekamMedis::where('pasien_id', $pasienId)
            ->with('dokter', 'pasien')
            ->latest()
            ->get();
        
        return view('rekam-medis.pasien-records', compact('rekamMedis', 'pasien'));
    }

    /**
     * Untuk AJAX - Mendapatkan semua rekam medis (Perawat & Admin)
     */
    public function all()
    {
        $rekamMedis = RekamMedis::with('pasien', 'dokter')
            ->latest()
            ->get();
        
        return response()->json($rekamMedis);
    }
}
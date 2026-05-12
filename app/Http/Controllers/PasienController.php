<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    use LogsActivity;

    // Simpan data pasien baru (oleh Perawat)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'usia' => 'required|integer|min:0|max:120',
            'berat_badan' => 'nullable|numeric|min:0|max:300',
            'tinggi_badan' => 'nullable|numeric|min:0|max:250',
            'tekanan_darah' => 'nullable|string|max:20',
            'suhu' => 'nullable|numeric|min:30|max:45',
            'keluhan' => 'required|string',
            'catatan_perawat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'alamat' => 'nullable|string',

            // 🔥 TAMBAHAN
            'dokter_id' => 'required|exists:users,id',
        ]);

        $validated['perawat_id'] = auth()->id();
        $validated['status'] = 'menunggu';

        Pasien::create($validated);

        $this->logActivity('Menambah pasien baru: ' . $validated['nama_lengkap']);

        return redirect()->route('dashboard.perawat')
            ->with('success', 'Data pasien berhasil ditambahkan!');
    }

    // Lihat detail pasien (AJAX)
    public function show($id)
    {
        $pasien = Pasien::with('perawat', 'dokter', 'rekamMedis')->findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $pasien->id,
                'nama_lengkap' => $pasien->nama_lengkap,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'usia' => $pasien->usia,
                'keluhan' => $pasien->keluhan,
                'catatan_perawat' => $pasien->catatan_perawat,
                'status' => $pasien->status,
                'dokter' => $pasien->dokter->name ?? '-',
                'no_telp' => $pasien->no_telp ?? '-',
                'alamat' => $pasien->alamat ?? '-',
                'tekanan_darah' => $pasien->tekanan_darah ?? null,
                'suhu' => $pasien->suhu ?? null,
                'berat_badan' => $pasien->berat_badan ?? null,
                'tinggi_badan' => $pasien->tinggi_badan ?? null,
                'created_at' => $pasien->created_at,
            ]);
        }
        
        return view('pasien.show', compact('pasien'));
    }

    // Update status pasien
    public function updateStatus(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        $oldStatus = $pasien->status;
        $pasien->update(['status' => $request->status]);
        
        $this->logActivity('Mengupdate status pasien ' . $pasien->nama_lengkap . ' dari ' . $oldStatus . ' menjadi ' . $request->status);
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'Status pasien berhasil diupdate!');
    }

    /**
     * Update data medis pasien (tensi, suhu, bb, tb, catatan)
     */
    public function updateMedis(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        
        $validated = $request->validate([
            'tekanan_darah' => 'nullable|string|max:20',
            'suhu' => 'nullable|numeric|min:30|max:45',
            'berat_badan' => 'nullable|numeric|min:0|max:300',
            'tinggi_badan' => 'nullable|numeric|min:0|max:250',
            'catatan_perawat' => 'nullable|string',
        ]);
        
        $pasien->update($validated);
        
        $this->logActivity('Mengupdate data medis pasien: ' . $pasien->nama_lengkap);
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'Data medis pasien berhasil diupdate!');
    }

    /**
     * Form edit keluhan pasien (lengkap dengan data medis)
     */
    public function editKeluhan($id)
    {
        $pasien = Pasien::findOrFail($id);
        return view('pasien.edit-keluhan', compact('pasien'));
    }

    /**
     * Update keluhan dan data medis pasien
     */
    public function updateKeluhan(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        
        $request->validate([
            'keluhan' => 'required|string',
            'tekanan_darah' => 'nullable|string|max:20',
            'suhu' => 'nullable|numeric|min:30|max:45',
            'berat_badan' => 'nullable|numeric|min:0|max:300',
            'tinggi_badan' => 'nullable|numeric|min:0|max:250',
            'catatan_perawat' => 'nullable|string',
        ]);
        
        $pasien->update([
            'keluhan' => $request->keluhan,
            'tekanan_darah' => $request->tekanan_darah,
            'suhu' => $request->suhu,
            'berat_badan' => $request->berat_badan,
            'tinggi_badan' => $request->tinggi_badan,
            'catatan_perawat' => $request->catatan_perawat
        ]);
        
        $this->logActivity('Mengedit keluhan dan data medis pasien: ' . $pasien->nama_lengkap);
        
        return redirect()->route('dashboard.perawat')
            ->with('success', 'Keluhan dan data medis pasien berhasil diperbarui!');
    }

    /**
     * Kirim data pasien ke dokter (Perawat)
     */
    public function kirimKeDokter(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        
        // Hanya bisa dikirim jika status masih 'menunggu'
        if ($pasien->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Pasien sudah dikirim ke dokter sebelumnya.'], 400);
        }
        
        $pasien->update(['status' => 'diproses','dokter_id' => $request->dokter_id]);
        
        $this->logActivity('Mengirim data pasien ke dokter: ' . $pasien->nama_lengkap);
        
        return response()->json(['success' => true, 'message' => 'Data berhasil dikirim ke dokter.']);
    }
}
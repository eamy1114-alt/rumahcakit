<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Catat aktivitas registrasi ke log
     */
    private function logActivity($aktivitas, $detail = null)
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? 0,
            'aktivitas' => $aktivitas,
            'ip_address' => request()->ip(),
            'detail' => $detail ? json_encode($detail) : null
        ]);
    }

    /**
     * Register Pasien (Tanpa CAPTCHA + Verifikasi Email)
     */
    public function storePasien(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'tanggal_lahir' => 'required|date',
            // 🔥 HAPUS validasi 'captcha'
        ]);

        // 🔥 HAPUS validasi CAPTCHA

        $user = User::create([
            'name' => $request->name,
            'nik' => $request->nik,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'tanggal_lahir' => $request->tanggal_lahir,
            'role' => 'pasien'
        ]);

        event(new Registered($user));

        return redirect()->route('login.pasien')
            ->with('success', 'Registrasi berhasil! Silakan cek email untuk verifikasi.');
    }

    /**
     * Register Dokter (Tanpa CAPTCHA + Verifikasi Email)
     */
    public function storeDokter(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_dokter' => 'required|string|unique:users',
            'poli' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // 🔥 HAPUS validasi 'captcha'
        ]);

        $user = User::create([
            'name' => $request->name,
            'id_dokter' => $request->id_dokter,
            'poli' => $request->poli,
            'email' => $request->email,
            'username' => $request->id_dokter,
            'password' => Hash::make($request->password),
            'role' => 'dokter'
        ]);

        event(new Registered($user));

        return redirect()->route('login.dokter')
            ->with('success', 'Registrasi dokter berhasil! Silakan cek email untuk verifikasi.');
    }

    /**
     * Register Admin (Tanpa CAPTCHA + Verifikasi Email)
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // 🔥 HAPUS validasi 'captcha'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'admin'
        ]);

        event(new Registered($user));

        return redirect()->route('login.admin')
            ->with('success', 'Registrasi admin berhasil! Silakan cek email untuk verifikasi.');
    }

    /**
     * Register Perawat (Tanpa CAPTCHA + Verifikasi Email)
     */
    public function storePerawat(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_perawat' => 'required|string|unique:users,id_dokter',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // 🔥 HAPUS validasi 'captcha'
        ]);

        $user = User::create([
            'name' => $request->name,
            'id_dokter' => $request->id_perawat,
            'email' => $request->email,
            'username' => $request->id_perawat,
            'password' => Hash::make($request->password),
            'role' => 'perawat',
            'poli' => null
        ]);

        event(new Registered($user));

        return redirect()->route('login.perawat')
            ->with('success', 'Registrasi perawat berhasil! Silakan cek email untuk verifikasi.');
    }
}
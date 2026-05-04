<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Helpers\CaptchaHelper;  // 🔥 TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Catat aktivitas login ke log
     */
    private function logActivity($aktivitas)
    {
        if (auth()->check()) {
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip(),
            ]);
        }
    }

    /**
     * Login untuk Pasien
     */
    public function loginPasien(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|string'  // 🔥 TAMBAHKAN VALIDASI CAPTCHA
        ]);

        // 🔥 VALIDASI CAPTCHA
        if (!CaptchaHelper::validate($request->captcha)) {
            return back()->withErrors(['captcha' => 'Kode captcha yang Anda masukkan salah.'])->withInput();
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            if ($user->role === 'pasien') {
                $this->logActivity('Login sebagai pasien');
                return redirect()->route('dashboard.pasien');
            }
            Auth::logout();
            return back()->withErrors(['error' => 'Akun ini bukan akun pasien']);
        }

        return back()->withErrors(['error' => 'Username atau password salah']);
    }

    /**
     * Login untuk Dokter
     */
    public function loginDokter(Request $request)
    {
        $request->validate([
            'id_dokter' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|string'  // 🔥 TAMBAHKAN VALIDASI CAPTCHA
        ]);

        // 🔥 VALIDASI CAPTCHA
        if (!CaptchaHelper::validate($request->captcha)) {
            return back()->withErrors(['captcha' => 'Kode captcha yang Anda masukkan salah.'])->withInput();
        }

        $user = User::where('id_dokter', $request->id_dokter)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->role === 'dokter') {
                Auth::login($user);
                $this->logActivity('Login sebagai dokter: ' . $user->id_dokter);
                return redirect()->route('dashboard.dokter');
            }
            return back()->withErrors(['error' => 'Akun ini bukan akun dokter']);
        }

        return back()->withErrors(['error' => 'ID Dokter atau password salah']);
    }

    /**
     * Login untuk Admin
     */
    public function loginAdmin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|string'  // 🔥 TAMBAHKAN VALIDASI CAPTCHA
        ]);

        // 🔥 VALIDASI CAPTCHA
        if (!CaptchaHelper::validate($request->captcha)) {
            return back()->withErrors(['captcha' => 'Kode captcha yang Anda masukkan salah.'])->withInput();
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                $this->logActivity('Login sebagai admin');
                return redirect()->route('dashboard.admin');
            }
            Auth::logout();
            return back()->withErrors(['error' => 'Akun ini bukan akun admin']);
        }

        return back()->withErrors(['error' => 'Username atau password salah']);
    }

    /**
     * Login untuk Perawat
     */
    public function loginPerawat(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|string'  // 🔥 TAMBAHKAN VALIDASI CAPTCHA
        ]);

        // 🔥 VALIDASI CAPTCHA
        if (!CaptchaHelper::validate($request->captcha)) {
            return back()->withErrors(['captcha' => 'Kode captcha yang Anda masukkan salah.'])->withInput();
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            if ($user->role === 'perawat') {
                $this->logActivity('Login sebagai perawat');
                return redirect()->route('dashboard.perawat');
            }
            Auth::logout();
            return back()->withErrors(['error' => 'Akun ini bukan akun perawat']);
        }

        return back()->withErrors(['error' => 'Username atau password salah']);
    }
}
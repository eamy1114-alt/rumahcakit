<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\AksesController;
use App\Http\Controllers\DataVKController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\KeluhanController;
use App\Helpers\CaptchaHelper;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// LOGIN REDIRECT ROUTE (Halaman pilihan login)
// ============================================
Route::get('/login', function () {
    return view('auth.login-options');
})->name('login');

// ============================================
// DASHBOARD REDIRECT ROUTE
// ============================================
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $role = auth()->user()->role;
    return redirect()->route("dashboard.{$role}");
})->name('dashboard');

// ============================================
// HOME ROUTES
// ============================================
Route::get('/home', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        return redirect()->route("dashboard.{$role}");
    }
    return view('landing');
})->name('home');

Route::get('/', function () {
    return redirect()->route('home');
});

// ============================================
// CAPTCHA ROUTE
// ============================================
Route::get('/captcha', function () {
    $builder = CaptchaHelper::generate();
    return response($builder->get())
        ->header('Content-Type', 'image/jpeg');
})->name('captcha');

// ============================================
// VERIFIKASI EMAIL ROUTES (Laravel Default)
// ============================================
Route::middleware(['auth'])->group(function () {
    // Halaman pemberitahuan verifikasi email
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    
    // Proses verifikasi email (dari link di email)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard');
    })->middleware(['signed'])->name('verification.verify');
    
    // Kirim ulang email verifikasi
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Link verifikasi baru telah dikirim!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// ============================================
// AUTH ROUTES (Login & Register)
// ============================================
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login/pasien', fn() => view('auth.login-pasien'))->name('login.pasien');
    Route::post('/login/pasien', [LoginController::class, 'loginPasien']);
    
    Route::get('/login/dokter', fn() => view('auth.login-dokter'))->name('login.dokter');
    Route::post('/login/dokter', [LoginController::class, 'loginDokter']);
    
    Route::get('/login/admin', fn() => view('auth.login-admin'))->name('login.admin');
    Route::post('/login/admin', [LoginController::class, 'loginAdmin']);
    
    Route::get('/login/perawat', fn() => view('auth.login-perawat'))->name('login.perawat');
    Route::post('/login/perawat', [LoginController::class, 'loginPerawat']);
    
    // Register Routes
    Route::get('/register/pasien', fn() => view('auth.register-pasien'))->name('register.pasien');
    Route::post('/register/pasien', [RegisteredUserController::class, 'storePasien']);
    
    Route::get('/register/dokter', fn() => view('auth.register-dokter'))->name('register.dokter');
    Route::post('/register/dokter', [RegisteredUserController::class, 'storeDokter']);
    
    Route::get('/register/admin', fn() => view('auth.register-admin'))->name('register.admin');
    Route::post('/register/admin', [RegisteredUserController::class, 'storeAdmin']);
    
    Route::get('/register/perawat', fn() => view('auth.register-perawat'))->name('register.perawat');
    Route::post('/register/perawat', [RegisteredUserController::class, 'storePerawat']);
});

// ============================================
// LOGOUT ROUTE
// ============================================
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ============================================
// DASHBOARD ROUTES (DENGAN MIDDLEWARE AUTH + VERIFIED)
// ============================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->name('dashboard.admin')
        ->middleware('role:admin');
    
    Route::get('/dashboard/dokter', [DashboardController::class, 'dokter'])
        ->name('dashboard.dokter')
        ->middleware('role:dokter');
    
    Route::get('/dashboard/perawat', [DashboardController::class, 'perawat'])
        ->name('dashboard.perawat')
        ->middleware('role:perawat');
    
    Route::get('/dashboard/pasien', [DashboardController::class, 'pasien'])
        ->name('dashboard.pasien')
        ->middleware('role:pasien');
});

// ============================================
// API ROUTES (AJAX)
// ============================================
Route::middleware(['auth'])->group(function () {
    // Ambil semua data pasien (untuk dashboard perawat)
    Route::get('/api/pasiens', function () {
        $pasiens = App\Models\Pasien::with('dokter')->latest()->get();
        
        $pasiens->transform(function ($pasien) {
            return [
                'id' => $pasien->id,
                'nama_lengkap' => $pasien->nama_lengkap,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'usia' => $pasien->usia,
                'keluhan' => $pasien->keluhan,
                'tekanan_darah' => $pasien->tekanan_darah,
                'suhu' => $pasien->suhu,
                'berat_badan' => $pasien->berat_badan,
                'tinggi_badan' => $pasien->tinggi_badan,
                'catatan_perawat' => $pasien->catatan_perawat,
                'status' => $pasien->status,
                'created_at' => $pasien->created_at,
                'dokter' => $pasien->dokter->name ?? null,
            ];
        });
        
        return response()->json($pasiens);
    });
    
    // API untuk mengambil daftar dokter (untuk dropdown perawat)
    Route::get('/api/dokters', function () {
        return App\Models\User::where('role', 'dokter')
            ->select('id', 'name', 'poli', 'id_dokter')
            ->orderBy('name')
            ->get();
    });
    
    // API untuk mengambil rekam medis per pasien
    Route::get('/api/rekam-medis/pasien/{pasienId}', function ($pasienId) {
        return App\Models\RekamMedis::where('pasien_id', $pasienId)
            ->with('dokter')
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->get();
    });
    
    // API untuk mengambil SEMUA rekam medis pasien (tanpa cek akses)
    Route::get('/api/rekam-medis/semua/{pasienId}', [AksesController::class, 'lihatSemuaRekamMedis']);
    
    // API untuk mengambil permintaan akses pasien
    Route::get('/api/akses-requests', [AksesController::class, 'getRequests']);
});

// ============================================
// PASIEN ROUTES (Perawat only)
// ============================================
Route::middleware(['auth', 'role:perawat'])->group(function () {
    Route::post('/pasien', [PasienController::class, 'store'])->name('pasien.store');
    Route::get('/pasien/{id}', [PasienController::class, 'show'])->name('pasien.show');
    Route::patch('/pasien/{id}/status', [PasienController::class, 'updateStatus'])->name('pasien.status');
    Route::post('/pasien/{id}/update-medis', [PasienController::class, 'updateMedis'])->name('pasien.update-medis');
    // EDIT KELUHAN PASIEN
    Route::get('/pasien/{id}/edit-keluhan', [PasienController::class, 'editKeluhan'])->name('pasien.edit-keluhan');
    Route::put('/pasien/{id}/edit-keluhan', [PasienController::class, 'updateKeluhan'])->name('pasien.update-keluhan');
    // KIRIM KE DOKTER
    Route::post('/pasien/{id}/kirim-ke-dokter', [PasienController::class, 'kirimKeDokter'])->name('pasien.kirim-ke-dokter');
});

// ============================================
// REKAM MEDIS ROUTES (Dokter & Pasien)
// ============================================
// Create & Store hanya untuk Dokter
Route::middleware(['auth', 'role:dokter'])->group(function () {
    Route::get('/rekam-medis/create/{pasienId}', [RekamMedisController::class, 'create'])->name('rekam-medis.create');
    Route::post('/rekam-medis', [RekamMedisController::class, 'store'])->name('rekam-medis.store');
});

// Show bisa diakses oleh semua role yang sudah login (otorisasi di controller)
Route::middleware(['auth'])->get('/rekam-medis/{id}', [RekamMedisController::class, 'show'])->name('rekam-medis.show');

// Lihat semua rekam medis pasien (untuk dokter yang sudah dapat akses)
Route::middleware(['auth'])->get('/rekam-medis/pasien/{pasienId}', [RekamMedisController::class, 'pasienRecords'])->name('rekam-medis.pasien');

// REKAM MEDIS UNTUK PASIEN
Route::middleware(['auth', 'role:pasien'])->get('/rekam-medis-saya', [RekamMedisController::class, 'myRecords'])->name('rekam-medis.my');

// REKAM MEDIS ALL (AJAX untuk perawat & admin)
Route::middleware(['auth'])->get('/rekam-medis/all', [RekamMedisController::class, 'all'])->name('rekam-medis.all');

// ============================================
// KELUHAN PASIEN ROUTES (Pasien only)
// ============================================
Route::middleware(['auth', 'role:pasien'])->group(function () {
    Route::get('/keluhan/create', [KeluhanController::class, 'create'])->name('keluhan.create');
    Route::post('/keluhan', [KeluhanController::class, 'store'])->name('keluhan.store');
    Route::get('/keluhan/riwayat', [KeluhanController::class, 'riwayat'])->name('keluhan.riwayat');
});

// ============================================
// AKSES ROUTES (Dokter & Pasien)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/akses/request', [AksesController::class, 'request'])->name('akses.request');
    Route::post('/akses/approve/{id}', [AksesController::class, 'approve'])->name('akses.approve');
    Route::post('/akses/deny/{id}', [AksesController::class, 'deny'])->name('akses.deny');
    Route::get('/akses/approved', [AksesController::class, 'approvedList'])->name('akses.approved');
});

// ============================================
// ADMIN ROUTES (CRUD Data VK & User Management)
// ============================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Data VK Routes (CRUD)
    Route::prefix('data-vk')->group(function () {
        Route::get('/', [DataVKController::class, 'index'])->name('data-vk.index');
        Route::get('/create', [DataVKController::class, 'create'])->name('data-vk.create');
        Route::post('/', [DataVKController::class, 'store'])->name('data-vk.store');
        Route::get('/{id}/edit', [DataVKController::class, 'edit'])->name('data-vk.edit');
        Route::put('/{id}', [DataVKController::class, 'update'])->name('data-vk.update');
        Route::delete('/{id}', [DataVKController::class, 'destroy'])->name('data-vk.destroy');
        Route::get('/{id}', [DataVKController::class, 'show'])->name('data-vk.show');
    });
    
    // User Management (Delete User)
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

// ============================================
// LOG ACTIVITY ROUTES (Admin only)
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('logs')->group(function () {
    Route::get('/', [LogActivityController::class, 'index'])->name('logs.index');
    Route::post('/clear', [LogActivityController::class, 'clear'])->name('logs.clear');
    Route::post('/delete-by-date', [LogActivityController::class, 'deleteByDate'])->name('logs.delete-by-date');
    Route::post('/delete-by-user', [LogActivityController::class, 'deleteByUser'])->name('logs.delete-by-user');
});

// ============================================
// PROFILE ROUTES (dari Breeze - jika ada)
// ============================================
// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
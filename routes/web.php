<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\VerifyCredentialController;
use Illuminate\Support\Facades\Route;

// --- Público ---------------------------------------------------------------
Route::get('/verificar/{code}', VerifyCredentialController::class)
    ->whereUuid('code')->name('credential.verify');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/olvide-mi-contrasena', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/olvide-mi-contrasena', [PasswordResetController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/restablecer/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/restablecer', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// --- Portal cliente --------------------------------------------------------
Route::middleware(['auth', 'client'])->name('portal.')->group(function () {
    Route::get('/', Portal\HomeController::class)->name('home');

    Route::get('/proyectos', [Portal\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/proyectos/{project}', [Portal\ProjectController::class, 'show'])->name('projects.show');

    Route::get('/entregables', [Portal\DeliverableController::class, 'index'])->name('deliverables.index');
    Route::get('/entregables/{deliverable}/descargar', [Portal\DeliverableController::class, 'download'])->name('deliverables.download');
    Route::post('/entregables/{deliverable}/aprobar', [Portal\DeliverableController::class, 'approve'])
        ->middleware('throttle:10,1')->name('deliverables.approve');
    Route::post('/entregables/verificar', [Portal\DeliverableController::class, 'verify'])
        ->middleware('throttle:30,1')->name('deliverables.verify');

    Route::middleware('unit:RESEARCH')->group(function () {
        Route::get('/research', [Portal\ResearchController::class, 'index'])->name('research.index');
        Route::get('/research/{report}/abrir', [Portal\ResearchController::class, 'open'])->name('research.open');
    });

    Route::get('/academia', [Portal\AcademyController::class, 'index'])->name('academy.index');
    Route::get('/academia/{video}', [Portal\AcademyController::class, 'show'])->name('academy.show');
    Route::post('/academia/{video}/visto', [Portal\AcademyController::class, 'complete'])->name('academy.complete');
    Route::post('/academia/{video}/inscribirme', [Portal\AcademyController::class, 'register'])->name('academy.register');
    Route::get('/academia/{video}/calendario.ics', [Portal\AcademyController::class, 'ics'])->name('academy.ics');

    Route::get('/facturacion', [Portal\BillingController::class, 'index'])->name('billing.index');
    Route::get('/facturacion/factura/{invoice}/{type}', [Portal\BillingController::class, 'invoiceFile'])
        ->whereIn('type', ['pdf', 'xml'])->name('billing.invoice');
    Route::get('/facturacion/contrato/{contract}', [Portal\BillingController::class, 'contractFile'])->name('billing.contract');

    Route::get('/bolsa-de-horas', [Portal\HoursController::class, 'index'])->name('hours.index');
    Route::post('/bolsa-de-horas', [Portal\HoursController::class, 'store'])->middleware('throttle:5,1')->name('hours.store');

    Route::get('/credencial', [Portal\CredentialController::class, 'show'])->name('credential');

    Route::get('/perfil', [Portal\ProfileController::class, 'edit'])->name('profile');
    Route::put('/perfil/contrasena', [Portal\ProfileController::class, 'password'])->name('profile.password');
});

// --- CMS (staff Blockfinity) ----------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/solicitudes-horas', [Admin\HourRequestController::class, 'index'])->name('hour-requests.index');
    Route::put('/solicitudes-horas/{hourRequest}', [Admin\HourRequestController::class, 'update'])->name('hour-requests.update');

    Route::get('/auditoria', Admin\AuditLogController::class)->name('audit');

    Route::get('/archivo/{resource}/{id}/{field}', [Admin\ResourceController::class, 'file'])->name('resources.file');
    Route::get('/{resource}', [Admin\ResourceController::class, 'index'])->name('resources.index');
    Route::get('/{resource}/crear', [Admin\ResourceController::class, 'create'])->name('resources.create');
    Route::post('/{resource}', [Admin\ResourceController::class, 'store'])->name('resources.store');
    Route::get('/{resource}/{id}/editar', [Admin\ResourceController::class, 'edit'])->name('resources.edit');
    // POST (no PUT) para que funcione la subida de archivos con multipart
    Route::post('/{resource}/{id}', [Admin\ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/{resource}/{id}', [Admin\ResourceController::class, 'destroy'])->name('resources.destroy');
});

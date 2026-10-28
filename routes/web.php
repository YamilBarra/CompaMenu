<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\RemateController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ResenaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de CompaMenu (arquitectura MVC: ruta -> controlador -> modelo -> vista)
|--------------------------------------------------------------------------
*/

// ---------- Páginas públicas ----------
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('/menus/{menu}', [MenuController::class, 'show'])->name('menus.show');
Route::get('/locales', [LocalController::class, 'index'])->name('locales.index');
Route::get('/locales/{local}', [LocalController::class, 'show'])->name('locales.show');
Route::get('/remates', [RemateController::class, 'index'])->name('remates.index');
Route::get('/resenas', [ResenaController::class, 'index'])->name('resenas.index');
Route::view('/proyecto', 'proyecto')->name('proyecto');

// ---------- Registro e ingreso ----------
Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [LoginController::class, 'show'])->name('login');
    Route::post('/ingresar', [LoginController::class, 'login'])->name('login.enviar');
    Route::get('/registro', [RegistroController::class, 'show'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'store'])->name('registro.enviar');
});
Route::post('/salir', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Reseñas: borrar la propia (o cualquiera si es administrador) ----------
Route::delete('/resenas/{valoracion}', [ResenaController::class, 'destroy'])
    ->middleware('rol:estudiante,administrador')
    ->name('resenas.destroy');

// ---------- Estudiante ----------
Route::middleware('rol:estudiante')->group(function () {
    Route::post('/resenas', [ResenaController::class, 'store'])->name('resenas.store');

    Route::get('/mis-reservas', [ReservaController::class, 'mis'])->name('reservas.mis');
    Route::get('/reservar/menu/{menu}', [ReservaController::class, 'crearMenu'])->name('reservas.menu');
    Route::get('/reservar/remate/{remate}', [ReservaController::class, 'crearRemate'])->name('reservas.remate');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::patch('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
});

// ---------- Establecimiento: panel "Mi local" ----------
Route::middleware('rol:establecimiento')->prefix('mi-local')->name('panel.')->group(function () {
    Route::get('/datos', [Panel\LocalController::class, 'edit'])->name('local.edit');
    Route::put('/datos', [Panel\LocalController::class, 'update'])->name('local.update');

    Route::get('/menus', [Panel\MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [Panel\MenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{menu}/editar', [Panel\MenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{menu}', [Panel\MenuController::class, 'update'])->name('menus.update');
    Route::patch('/menus/{menu}/disponibilidad', [Panel\MenuController::class, 'disponibilidad'])->name('menus.disponibilidad');
    Route::delete('/menus/{menu}', [Panel\MenuController::class, 'destroy'])->name('menus.destroy');

    Route::get('/remates', [Panel\RemateController::class, 'index'])->name('remates.index');
    Route::post('/remates', [Panel\RemateController::class, 'store'])->name('remates.store');
    Route::get('/remates/{remate}/editar', [Panel\RemateController::class, 'edit'])->name('remates.edit');
    Route::put('/remates/{remate}', [Panel\RemateController::class, 'update'])->name('remates.update');
    Route::delete('/remates/{remate}', [Panel\RemateController::class, 'destroy'])->name('remates.destroy');

    Route::get('/reservas', [Panel\ReservaController::class, 'index'])->name('reservas.index');
    Route::patch('/reservas/{reserva}/estado', [Panel\ReservaController::class, 'estado'])->name('reservas.estado');
});

// ---------- Administrador ----------
Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\ResumenController::class, 'index'])->name('resumen');

    Route::get('/usuarios', [Admin\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::patch('/usuarios/{usuario}/alternar', [Admin\UsuarioController::class, 'alternar'])->name('usuarios.alternar');
    Route::delete('/usuarios/{usuario}', [Admin\UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    Route::get('/locales', [Admin\LocalController::class, 'index'])->name('locales.index');
    Route::patch('/locales/{local}/alternar', [Admin\LocalController::class, 'alternar'])->name('locales.alternar');
    Route::delete('/locales/{local}', [Admin\LocalController::class, 'destroy'])->name('locales.destroy');

    Route::get('/menus', [Admin\MenuController::class, 'index'])->name('menus.index');
    Route::delete('/menus/{menu}', [Admin\MenuController::class, 'destroy'])->name('menus.destroy');

    Route::get('/valoraciones', [Admin\ValoracionController::class, 'index'])->name('valoraciones.index');
    Route::patch('/valoraciones/{valoracion}/alternar', [Admin\ValoracionController::class, 'alternar'])->name('valoraciones.alternar');
    Route::delete('/valoraciones/{valoracion}', [Admin\ValoracionController::class, 'destroy'])->name('valoraciones.destroy');

    Route::get('/reservas', [Admin\ReservaController::class, 'index'])->name('reservas.index');
});

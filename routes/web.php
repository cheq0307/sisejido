<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParcelaController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\RecursosController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\ApoyoSocialController;


Route::get('/', function () {
    return redirect('/principal');
});

Route::get('/principal', function () {
    return view('AuthViews.principal');
})->name('principal');

Route::get('/nuevoE', function () {
    return view('RegisterViews/nuevoEjidatario');
});

Route::get('/listadoEjidatarios', function () {
    return view('ListViews/listadoEjidatarios');
});


// ══════════════════════════════════════════════════════════════════════════════
// PARCELAS
// IMPORTANTE: las rutas con segmentos fijos (/mapa, /geojson, etc.) DEBEN ir
// ANTES de las rutas con parámetros dinámicos ({parcela}, {id}), de lo contrario
// Laravel interpreta el segmento fijo como el valor del parámetro.
// ══════════════════════════════════════════════════════════════════════════════

// -- Rutas de vistas (segmentos fijos primero) --
Route::get('/parcelas',                      [ParcelaController::class, 'index'])->name('parcelas.index');
Route::get('/parcelas/mapa',                 [ParcelaController::class, 'mapa'])->name('parcelas.mapa');
Route::get('/nuevaParcela',                  [ParcelaController::class, 'create'])->name('parcelas.create');
Route::post('/nuevaParcela',                 [ParcelaController::class, 'store'])->name('parcelas.store');
Route::get('/verParcela',                    [ParcelaController::class, 'verParcela'])->name('parcelas.ver');

// -- Rutas con parámetro dinámico --
Route::get('/parcelas/{parcela}',            [ParcelaController::class, 'show'])->name('parcelas.show');
Route::get('/parcelas/{parcela}/editar',     [ParcelaController::class, 'edit'])->name('parcelas.edit');
Route::put('/parcelas/{parcela}',            [ParcelaController::class, 'update'])->name('parcelas.update');
Route::delete('/parcelas/{parcela}',         [ParcelaController::class, 'destroy'])->name('parcelas.destroy');

Route::get('/editarParcela/{id}',            [ParcelaController::class, 'editarParcela'])->name('parcelas.editar');
Route::post('/parcela/actualizar',           [ParcelaController::class, 'actualizarParcela'])->name('parcelas.actualizar');


// ══════════════════════════════════════════════════════════════════════════════
// API MAPA — polígonos y GeoJSON
// Sin CSRF: el JS envía X-CSRF-TOKEN en el header, pero para DELETE/POST desde
// fetch es más seguro excluir el middleware VerifyCsrfToken en estas rutas
// y validar el token manualmente (ya lo hace Laravel vía header automáticamente).
// withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class) permite que
// el fetch con header X-CSRF-TOKEN funcione sin el campo _token en el body.
// El orden es crítico: /geojson ANTES de /{id}/poligono
// ══════════════════════════════════════════════════════════════════════════════
Route::get('/api/parcelas/geojson',
    [ParcelaController::class, 'apiGeoJSON']
)->name('parcelas.api');

Route::patch('/api/parcelas/{parcela}/coordenadas',
    [ParcelaController::class, 'actualizarCoordenadas']
)->name('parcelas.api.coordenadas')
 ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/api/parcelas/{id}/poligono',
    [ParcelaController::class, 'guardarPoligono']
)->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::delete('/api/parcelas/{id}/poligono',
    [ParcelaController::class, 'borrarPoligono']
)->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


// ══════════════════════════════════════════════════════════════════════════════
// GASTOS
// ══════════════════════════════════════════════════════════════════════════════
Route::get('/gastos',                [GastoController::class, 'index'])->name('gastos.index');
Route::get('/gastos/nuevo',          [GastoController::class, 'create'])->name('gastos.create');
Route::post('/gastos',               [GastoController::class, 'store'])->name('gastos.store');
Route::post('/gastos/buscar',        [GastoController::class, 'buscar'])->name('gastos.buscar');
Route::get('/gastos/{id}/editar',    [GastoController::class, 'edit'])->name('gastos.edit');
Route::post('/gastos/{id}',          [GastoController::class, 'update'])->name('gastos.update');
Route::post('/gastos/{id}/eliminar', [GastoController::class, 'destroy'])->name('gastos.destroy');


// ══════════════════════════════════════════════════════════════════════════════
// ARTÍCULOS
// ══════════════════════════════════════════════════════════════════════════════
Route::get('/articulos',                 [ArticuloController::class, 'index'])->name('articulos.index');
Route::get('/articulos/nuevo',           [ArticuloController::class, 'create'])->name('articulos.create');
Route::get('/articulos/buscar',          [ArticuloController::class, 'buscar'])->name('articulos.buscar');
Route::get('/articulos/reporte',         [ArticuloController::class, 'reporte'])->name('articulos.reporte');
Route::post('/articulos',                [ArticuloController::class, 'store'])->name('articulos.store');
Route::get('/articulos/{id}/editar',     [ArticuloController::class, 'edit'])->name('articulos.edit');
Route::post('/articulos/{id}',           [ArticuloController::class, 'update'])->name('articulos.update');
Route::post('/articulos/{id}/eliminar',  [ArticuloController::class, 'destroy'])->name('articulos.destroy');


// ══════════════════════════════════════════════════════════════════════════════
// ENTRADAS
// ══════════════════════════════════════════════════════════════════════════════
Route::get('/entradas',                      [EntradaController::class, 'index'])->name('entradas.index');
Route::get('/entradas/nueva',                [EntradaController::class, 'create'])->name('entradas.create');
Route::post('/entradas/guardar',             [EntradaController::class, 'store'])->name('entradas.store');
Route::get('/entradas/editar/{id}',          [EntradaController::class, 'edit'])->name('entradas.edit');
Route::put('/entradas/actualizar/{id}',      [EntradaController::class, 'update'])->name('entradas.update');
Route::delete('/entradas/eliminar/{id}',     [EntradaController::class, 'destroy'])->name('entradas.destroy');


// ══════════════════════════════════════════════════════════════════════════════
// SALIDAS
// ══════════════════════════════════════════════════════════════════════════════
Route::get('salidas',                    [SalidaController::class, 'index'])->name('salidas.index');
Route::get('salidas/nueva',              [SalidaController::class, 'create'])->name('salidas.create');
Route::post('salidas/guardar',           [SalidaController::class, 'store'])->name('salidas.store');
Route::get('salidas/editar/{id}',        [SalidaController::class, 'edit'])->name('salidas.edit');
Route::put('salidas/actualizar/{id}',    [SalidaController::class, 'update'])->name('salidas.update');
Route::delete('salidas/eliminar/{id}',   [SalidaController::class, 'destroy'])->name('salidas.destroy');
Route::get('reporte/entradas-salidas',   [SalidaController::class, 'reporteEyS'])->name('reporte.eys');


// ══════════════════════════════════════════════════════════════════════════════
// APOYOS SOCIALES
// ══════════════════════════════════════════════════════════════════════════════
Route::get('/apoyos/reporte',        [ApoyoSocialController::class, 'reporte'])->name('apoyos.reporte');
Route::get('/apoyos',                [ApoyoSocialController::class, 'index'])->name('apoyos.index');
Route::get('/apoyos/nuevo',          [ApoyoSocialController::class, 'create'])->name('apoyos.create');
Route::post('/apoyos',               [ApoyoSocialController::class, 'store'])->name('apoyos.store');
Route::get('/apoyos/{id}/editar',    [ApoyoSocialController::class, 'edit'])->name('apoyos.edit');
Route::put('/apoyos/{id}',           [ApoyoSocialController::class, 'update'])->name('apoyos.update');
Route::delete('/apoyos/{id}',        [ApoyoSocialController::class, 'destroy'])->name('apoyos.destroy');
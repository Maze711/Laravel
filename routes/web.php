<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


// TO-DO LIST Web
Route::get('/tasks', 'App\Http\Controllers\TaskController@index');
Route::post('/tasks', 'App\Http\Controllers\TaskController@store');
Route::get('/tasks/{id}/edit', 'App\Http\Controllers\TaskController@edit');
Route::put('/tasks/{id}', 'App\Http\Controllers\TaskController@update');
Route::delete('/tasks/{id}', 'App\Http\Controllers\TaskController@destroy');

// Registration
Route::get('/form', [FormController::class, 'index'])->name('form');
Route::post('store-form', [FormController::class, 'store']);

Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');

Route::namespace('Auth')->group(function () {
    Route::get('/login',[LoginController::class,'login'])->name('login');
    Route::post('login',[LoginController::class,'processLogin']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserFormController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\FileController;
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


// TO-DO LIST Web (on going)
Route::get('/tasks', 'App\Http\Controllers\TaskController@index');
Route::post('/tasks', 'App\Http\Controllers\TaskController@store');
Route::get('/tasks/{id}/edit', 'App\Http\Controllers\TaskController@edit');
Route::put('/tasks/{id}', 'App\Http\Controllers\TaskController@update');
Route::delete('/tasks/{id}', 'App\Http\Controllers\TaskController@destroy');

// Registration
Route::get('/form', [FormController::class, 'index'])->name('form');
Route::post('store-form', [FormController::class, 'store']);

Route::get('/home', [UserFormController::class, 'index'])->name('home');

Route::namespace('Auth')->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('login', [LoginController::class, 'processLogin']);
});

Route::get('logout', [LoginController::class, 'logout'])->name('logout');

// Edit User Function
Route::get('/edit/{id}', [UserFormController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserFormController::class, 'update'])->name('users.update');
// Route::post('/edit', [FormController::class, 'store'])->name('edit.store');

// Add User Function
Route::get('/add', [UserFormController::class, 'create'])->name('users.add');
Route::post('/add', [UserFormController::class, 'add'])->name('users.add');

Route::delete('/users/{user}', [UserFormController::class, 'destroy'])->name('users.destroy');

Route::get('/export', [FileController::class, 'export'])->name('export.excel');

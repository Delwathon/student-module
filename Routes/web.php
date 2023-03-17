<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

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

// Route::prefix('student')->group(function() {
//     Route::get('/', 'StudentController@index');
// });

Route::prefix('student')->group(function() {
    Route::get('/', [StudentController::class, 'index'])->name('student.index');
    Route::get('/student-filter/{branch}/{class}/{section}', [StudentController::class, 'studentFilter'])->name('student.filter');
    Route::get('/inactive', [StudentController::class, 'inactive'])->name('student.inactive');
    Route::get('/inactive/{session}/{status}', [StudentController::class, 'inactiveStudent'])->name('inactive.list');
    Route::get('/profile/{id?}', [StudentController::class, 'show'])->name('student.profile');
    Route::put('/profile/updtate/{id}', [StudentController::class, 'update'])->name('student.update');


});
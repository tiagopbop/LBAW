<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::redirect('/', '/login');

// Cards

// Route to show user details (username and email).
Route::get('/home', [HomeController::class, 'showUserDetails'])->name('home');

// Route to log the user out.
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');


//Admin
Route::middleware('admin.auth')->get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/supersecretlogin', [AdminController::class, 'showLoginForm'])->name('admin.loginForm');
Route::post('/supersecretlogin', [AdminController::class, 'login'])->name('admin.login');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');



//Profile
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::delete('/profile/remove-image', [ProfileController::class, 'removeImage'])->name('profile.removeImage');


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
});

Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');

Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.myProjects');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::get('/search-projects', [HomeController::class, 'searchProjects']);

Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

Route::get('/projects/{project}/tasks', [TaskController::class, 'viewTasks'])->name('tasks.viewTasks');
Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

Route::post('/projects/{project}/invite', [ProjectController::class, 'invite'])->name('projects.invite');
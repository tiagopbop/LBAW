<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\InfoController;
use App\Http\Controllers\MyTasksController;
use App\Http\Controllers\PleaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;


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

// Info
Route::get('/about', [InfoController::class, 'about']);
Route::get('/contact', [InfoController::class, 'contact']);
Route::get('/faq', [InfoController::class, 'faq']);

// Cards

// Route to show user details (username and email).
Route::get('/home', [HomeController::class, 'showUserDetails'])->name('home');

// Route to log the user out.


//Admin
Route::middleware('admin.auth')->group(function () {
    Route::get('/admin/unsuspended_users', [AdminController::class, 'unsuspendedUsers'])->name('admin.unsuspended_users');
    Route::get('/admin/suspended_users', [AdminController::class, 'suspendedUsers'])->name('admin.suspended_users');
    Route::get('/admin/pleas_dashboard', [AdminController::class, 'pleasDashboard'])->name('admin.pleas_dashboard');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.delete_users');
    Route::get('/admin/create_user', [AdminController::class, 'showCreateUserForm'])->name('admin.create_user');
    Route::post('/admin/create_user', [AdminController::class, 'create_user'])->name('admin.storeUser');

});

// Admin login/logout routes
Route::get('/supersecretlogin', [AdminController::class, 'showLoginForm'])->name('admin.loginForm');
Route::post('/supersecretlogin', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::patch('/admin/user/{id}/suspend', [AdminController::class, 'toggleSuspend'])->name('admin.toggleSuspend');
Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/home', [HomeController::class, 'showUserDetails'])->name('home');
});
Route::get('/pleading', [HomeController::class, 'pleading'])->name('pleading.page');
Route::post('/pleading', [PleaController::class, 'submit'])->name('pleading.submit');
Route::patch('/admin/toggle_suspend/{id}', [AdminController::class, 'toggleProjectSuspend'])->name('admin.toggleProjectSuspend');
Route::delete('/admin/delete_project/{id}', [AdminController::class, 'deleteProject'])->name('admin.delete_project');
Route::get('/admin/unsuspended_projects', [AdminController::class, 'unsuspendedProjects'])->name('admin.unsuspended_projects');
Route::get('/admin/suspended_projects', [AdminController::class, 'suspendedProjects'])->name('admin.suspended_projects');





//Profile
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::delete('/profile/remove-image', [ProfileController::class, 'removeImage'])->name('profile.removeImage');
Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');

});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register.form');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});


// Reset Password Routes
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::post('/forgot-password', [ResetPasswordController::class, 'passwordEmail']);
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'passwordReset'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'passwordUpdate'])->name('password.update');

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
Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{task}/assigned-users', [TaskController::class, 'getAssignedUsers'])->name('tasks.assigned_users');
Route::post('/projects/{project}/assign-manager', [ProjectController::class, 'assignManager'])->name('projects.assignManager');
Route::post('/projects/{project}/revertManager', [ProjectController::class, 'revertManager'])->name('projects.revertManager');
Route::delete('/projects/{project}/removeMember', [ProjectController::class, 'removeMember'])->name('projects.removeMember');

Route::post('/projects/{project}/invite', [ProjectController::class, 'invite'])->name('projects.invite');
Route::get('/tasks/search', [TaskController::class, 'searchTasks'])->name('tasks.search');

Route::get('/mytasks', [MyTasksController::class, 'myTasks'])->name('tasks.mytasks');

Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');

Route::post('/projects/{project}/favorite', [FavoriteController::class, 'toggleFavorite'])->name('projects.favorite');

Route::post('/projects/{project}/leave', [ProjectController::class, 'leaveProject'])->name('projects.leave');
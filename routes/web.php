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
use App\Http\Controllers\TaskCommentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SearchUsersController;

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

// Public home route
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/search-projects', [HomeController::class, 'searchProjects']);

// Home route accessible to guests
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware(['auth', 'check.suspension']);
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/projects/{project}/tasks', [TaskController::class, 'viewTasks'])->name('tasks.viewTasks');

// Home routes
Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/pleading', [HomeController::class, 'pleading'])->name('pleading.page');
    Route::post('/pleading', [PleaController::class, 'submit'])->name('pleading.submit');
});

// Info
Route::get('/about', [InfoController::class, 'about']);
Route::get('/contact', [InfoController::class, 'contact']);
Route::get('/faq', [InfoController::class, 'faq']);


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register.form');
    Route::post('/register', 'register')->name('register.submit');
});

// Reset Password Routes
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::post('/forgot-password', [ResetPasswordController::class, 'passwordEmail']);
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'passwordReset'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'passwordUpdate'])->name('password.update');

// Admin routes
Route::middleware('admin.auth')->group(function () {
    Route::get('/admin/unsuspended_users', [AdminController::class, 'unsuspendedUsers'])->name('admin.unsuspended_users');
    Route::get('/admin/suspended_users', [AdminController::class, 'suspendedUsers'])->name('admin.suspended_users');
    Route::get('/admin/pleas_dashboard', [AdminController::class, 'pleasDashboard'])->name('admin.pleas_dashboard');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.delete_users');
    Route::get('/admin/create_user', [AdminController::class, 'showCreateUserForm'])->name('admin.create_user');
    Route::post('/admin/create_user', [AdminController::class, 'create_user'])->name('admin.storeUser');
    Route::patch('/admin/user/{id}/suspend', [AdminController::class, 'toggleSuspend'])->name('admin.toggleSuspend');
    Route::patch('/admin/toggle_suspend/{id}', [AdminController::class, 'toggleProjectSuspend'])->name('admin.toggleProjectSuspend');
    Route::delete('/admin/delete_project/{id}', [AdminController::class, 'deleteProject'])->name('admin.delete_project');
    Route::get('/admin/unsuspended_projects', [AdminController::class, 'unsuspendedProjects'])->name('admin.unsuspended_projects');
    Route::get('/admin/suspended_projects', [AdminController::class, 'suspendedProjects'])->name('admin.suspended_projects');
});

// Admin login/logout routes
Route::get('/supersecretlogin', [AdminController::class, 'showLoginForm'])->name('admin.loginForm');
Route::post('/supersecretlogin', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Profile routes
Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/remove-image', [ProfileController::class, 'removeImage'])->name('profile.removeImage');
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    Route::post('/profile/{username}/follow', [ProfileController::class, 'follow'])->name('profile.follow');
    Route::post('/profile/{username}/unfollow', [ProfileController::class, 'unfollow'])->name('profile.unfollow');
    Route::get('/profile/{username}/followers', [ProfileController::class, 'followers'])->name('profile.followers');
    Route::get('/profile/{username}/following', [ProfileController::class, 'following'])->name('profile.following');
});

// Project routes
Route::middleware(['auth'])->group(function () {
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.myProjects');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/favorite', [FavoriteController::class, 'toggleFavorite'])->name('projects.favorite');
    Route::post('/projects/{project}/leave', [ProjectController::class, 'leaveProject'])->name('projects.leave');
    Route::post('/projects/{project}/assign-manager', [ProjectController::class, 'assignManager'])->name('projects.assignManager');
    Route::post('/projects/{project}/revertManager', [ProjectController::class, 'revertManager'])->name('projects.revertManager');
    Route::delete('/projects/{project}/removeMember', [ProjectController::class, 'removeMember'])->name('projects.removeMember');
    Route::post('/projects/{project}/invite', [ProjectController::class, 'invite'])->name('projects.invite');
});

// Task routes
Route::middleware(['auth'])->group(function () {
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::get('/tasks/{id}/assigned-users', [TaskController::class, 'getAssignedUsers']);
    Route::get('/tasks/search', [TaskController::class, 'searchTasks'])->name('tasks.search');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/assigned-users', [TaskController::class, 'getAssignedUsers'])->name('tasks.assigned_users');
    Route::put('/tasks/{task_id}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('taskComments.store');
    Route::get('/mytasks', [MyTasksController::class, 'myTasks'])->name('tasks.mytasks');
});

// Search Users
Route::get('/searchusers', [SearchUsersController::class, 'index'])->name('searchusers');
Route::get('/searchusers/ajax', [SearchUsersController::class, 'search'])->name('searchusers.ajax');
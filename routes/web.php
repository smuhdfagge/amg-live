<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
| Public website
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/our-work', [PageController::class, 'work'])->name('work');
Route::get('/our-work/{area}', [PageController::class, 'area'])->name('work.area');
Route::get('/programmes', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/programmes/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/partners', [PageController::class, 'partners'])->name('partners');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [NewsController::class, 'show'])->name('news.show');
Route::get('/get-involved', [PageController::class, 'involved'])->name('involved');
Route::get('/support-us', [PageController::class, 'support'])->name('support');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::middleware('throttle:forms')->group(function () {
    Route::post('/contact', [FormController::class, 'contact'])->name('contact.send');
    Route::post('/get-involved/volunteer', [FormController::class, 'volunteer'])->name('volunteer.send');
    Route::post('/subscribe', [FormController::class, 'subscribe'])->name('subscribe');
});

// Old WordPress addresses
Route::redirect('/about-us', '/about', 301);
Route::redirect('/projects', '/programmes', 301);
Route::redirect('/sample-page', '/', 301);

Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

/*
| Admin
*/
Route::get('/admin/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
Route::post('/admin/login', [LoginController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login.attempt');
Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::resource('posts', Admin\PostController::class)->except('show');
    Route::resource('projects', Admin\ProjectController::class)->except('show');
    Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('volunteers', [Admin\VolunteerController::class, 'index'])->name('volunteers.index');
    Route::get('volunteers/export', [Admin\VolunteerController::class, 'export'])->name('volunteers.export');
    Route::get('volunteers/{volunteer}', [Admin\VolunteerController::class, 'show'])->name('volunteers.show');
    Route::delete('volunteers/{volunteer}', [Admin\VolunteerController::class, 'destroy'])->name('volunteers.destroy');
    Route::get('subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');
    Route::delete('subscribers/{subscriber}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('account', [Admin\AccountController::class, 'edit'])->name('account');
    Route::put('account', [Admin\AccountController::class, 'update'])->name('account.update');
});

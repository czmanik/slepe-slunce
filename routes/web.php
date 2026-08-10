<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\QuickRoutePointController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberLocationController;
use App\Http\Controllers\MapPhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/denik', [PostController::class, 'index'])->name('posts.index');
Route::get('/trasa', RouteController::class)->name('route.index');
Route::get('/clenove', [MemberController::class, 'index'])->name('members.index');
Route::middleware('auth')->group(function (): void {
    Route::get('/admin/trasa/rychle-pridat', [QuickRoutePointController::class, 'create'])->name('route.quick.create');
    Route::post('/admin/trasa/rychle-pridat', [QuickRoutePointController::class, 'store'])->name('route.quick.store');
    Route::get('/admin/poloha', [MemberLocationController::class, 'create'])->name('tracking.location.create');
    Route::post('/admin/poloha', [MemberLocationController::class, 'store'])->name('tracking.location.store');
    Route::get('/admin/fotka-na-mapu', [MapPhotoController::class, 'create'])->name('tracking.photo.create');
    Route::post('/admin/fotka-na-mapu', [MapPhotoController::class, 'store'])->name('tracking.photo.store');
});
Route::get('/denik/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/nahled/{post}', [PostController::class, 'preview'])->middleware('auth')->name('posts.preview');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

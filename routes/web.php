<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\QuickRoutePointController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/denik', [PostController::class, 'index'])->name('posts.index');
Route::get('/trasa', RouteController::class)->name('route.index');
Route::middleware('auth')->group(function (): void {
    Route::get('/admin/trasa/rychle-pridat', [QuickRoutePointController::class, 'create'])->name('route.quick.create');
    Route::post('/admin/trasa/rychle-pridat', [QuickRoutePointController::class, 'store'])->name('route.quick.store');
});
Route::get('/denik/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/nahled/{post}', [PostController::class, 'preview'])->middleware('auth')->name('posts.preview');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

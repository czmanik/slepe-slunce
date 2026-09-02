<?php

use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\ExpeditionRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapPhotoController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberLocationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuickRoutePointController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ShopCheckoutController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/denik', [PostController::class, 'index'])->name('posts.index');
Route::get('/trasa', [RouteController::class, 'legacy'])->name('route.index');
Route::get('/clenove', [MemberController::class, 'index'])->name('members.index');
Route::get('/expedice', [ExpeditionController::class, 'index'])->name('expeditions.index');
Route::get('/expedice/{expedition}', [ExpeditionController::class, 'show'])->name('expeditions.show');
Route::get('/expedice/{expedition}/program-a-trasa', [RouteController::class, 'show'])->name('expeditions.route');
Route::get('/expedice/{expedition}/denik', [PostController::class, 'index'])->name('expeditions.posts');
Route::get('/expedice/{expedition}/clenove', [MemberController::class, 'index'])->name('expeditions.members');
Route::get('/expedice/{expedition}/prihlaska', [ExpeditionRegistrationController::class, 'create'])->name('expeditions.register');
Route::post('/expedice/{expedition}/prihlaska', [ExpeditionRegistrationController::class, 'store'])->middleware('throttle:10,1')->name('expeditions.register.store');
Route::post('/odber', [SubscriberController::class, 'store'])->middleware('throttle:5,1')->name('subscriptions.store');
Route::get('/odber/potvrdit/{token}', [SubscriberController::class, 'confirm'])->name('subscriptions.confirm');
Route::get('/odber/odhlasit/{token}', [SubscriberController::class, 'unsubscribe'])->name('subscriptions.unsubscribe');
$shopRoutes = function (): void {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/vino/{product}', [ShopController::class, 'show'])->name('show');
    Route::post('/kosik/{variant}', [ShopController::class, 'add'])->name('cart.add');
    Route::get('/kosik', [ShopController::class, 'cart'])->name('cart');
    Route::delete('/kosik/{variant}', [ShopController::class, 'remove'])->name('cart.remove');
    Route::get('/pokladna', [ShopCheckoutController::class, 'create'])->name('checkout');
    Route::post('/pokladna', [ShopCheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/objednavka/{order}', [ShopCheckoutController::class, 'show'])->name('order');
    Route::get('/doklad/{order}/{token}', [ShopCheckoutController::class, 'invoice'])->name('invoice');
    Route::get('/platba/{order}/navrat', [ShopCheckoutController::class, 'paymentReturn'])->name('payment.return');
    Route::post('/platba/comgate/callback', [ShopCheckoutController::class, 'callback'])->withoutMiddleware('auth')->name('payment.callback');
};

Route::prefix('obchod')
    ->middleware(['shop.testing', 'auth'])
    ->name('shop.')
    ->group($shopRoutes);
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

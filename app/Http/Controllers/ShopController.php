<?php

namespace App\Http\Controllers;

use App\Models\WineProduct;
use App\Models\WineVariant;
use App\Services\ShopCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request, ShopCart $cart): View
    {
        if (in_array($request->query('currency'), ['CZK', 'EUR'], true)) {
            $cart->setCurrency($request->query('currency'));
        }$products = WineProduct::available()->with(['variants' => fn ($q) => $q->where('is_active', true)->whereColumn('stock_quantity', '>', 'reserved_quantity')->orderByDesc('vintage')])->orderBy('winery')->orderBy('name')->paginate(24);

        return view('shop.index', compact('products', 'cart'));
    }

    public function show(WineProduct $product, ShopCart $cart): View
    {
        abort_unless($product->is_active, 404);
        $product->load(['variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('vintage')]);

        return view('shop.show', compact('product', 'cart'));
    }

    public function add(Request $request, WineVariant $variant, ShopCart $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:100'], 'age_confirmed' => ['accepted']]);
        $cart->add($variant, $data['quantity']);

        return redirect()->route('shop.cart')->with('message', 'Víno bylo přidáno do košíku.');
    }

    public function cart(ShopCart $cart): View
    {
        return view('shop.cart', compact('cart'));
    }

    public function remove(WineVariant $variant, ShopCart $cart): RedirectResponse
    {
        $cart->remove($variant);

        return back()->with('message','Položka byla odebrána z košíku.');
    }
}

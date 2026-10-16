<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        $products = Product::active()->with(['variants', 'images'])->orderBy('sort')->orderByDesc('id')->get();

        return view('store.home', compact('products'));
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load(['variants.images', 'images']);

        return view('store.product', compact('product'));
    }

    public function locale(Request $request, string $locale)
    {
        if (in_array($locale, config('valletta.locales'), true)) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back(fallback: route('home'));
    }
}

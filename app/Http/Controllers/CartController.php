<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function show()
    {
        return view('store.cart', ['lines' => $this->cart->lines(), 'subtotal' => $this->cart->subtotal()]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QTY],
        ]);
        $variant = ProductVariant::with('product')->findOrFail($data['variant_id']);
        abort_unless($variant->product->is_active, 404);

        if ($variant->stock < 1) {
            return back()->withErrors(['variant_id' => __('product.sold_out')]);
        }

        $this->cart->add($variant, (int) ($data["qty"] ?? 1));

        return $request->boolean('buy_now')
            ? redirect()->route('checkout')
            : redirect()->route('cart')->with('status', __('cart.added'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QTY],
        ]);
        $this->cart->set((int) $data['variant_id'], (int) $data['qty']);

        return redirect()->route('cart');
    }
}

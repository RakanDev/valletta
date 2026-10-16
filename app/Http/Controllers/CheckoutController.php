<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Cart;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart, private OrderService $orders) {}

    public function show()
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart');
        }

        return view('store.checkout', [
            'lines' => $lines,
            'subtotal' => $this->cart->subtotal(),
            'fee' => Setting::deliveryFee(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            // Jordanian mobile: 07X XXX XXXX or +9627X XXX XXXX
            'phone' => ['required', 'string', 'regex:/^(\+?962|0)7[789]\d{7}$/'],
            'city' => ['required', Rule::in(array_keys(config('valletta.cities')))],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'customer_name' => __('checkout.name'),
            'phone' => __('checkout.phone'),
            'city' => __('checkout.city'),
            'address' => __('checkout.address'),
            'notes' => __('checkout.notes'),
        ]);
        $data['phone'] = preg_replace('/\s+/', '', $data['phone']);

        $order = $this->orders->placeFromCart($data);
        $request->session()->put('last_order', $order->id);

        return redirect()->route('order.thanks', $order->number);
    }

    public function thanks(Request $request, string $number)
    {
        $order = Order::with('items.variant.product')->where('number', $number)->firstOrFail();
        // Only the browser that placed the order can see it.
        abort_unless($request->session()->get('last_order') === $order->id, 404);

        return view('store.thanks', [
            'order' => $order,
            'whatsappUrl' => $this->orders->whatsappUrl($order),
        ]);
    }
}

@extends('layouts.store')
@section('title', __('cart.title'))

@section('content')
@php use App\Services\OrderService; @endphp
<div class="mx-auto max-w-3xl px-4 pt-10">
    <h1 class="text-3xl font-extrabold">{{ __('cart.title') }}</h1>

    @error('cart')<p class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ $message }}</p>@enderror

    @if ($lines->isEmpty())
        <div class="mt-10 rounded-3xl border border-dashed border-line p-10 text-center">
            <p class="text-ink-soft">{{ __('cart.empty') }}</p>
            <a href="{{ route('home') }}#collection" class="btn-primary mt-6">{{ __('cart.browse') }}</a>
        </div>
    @else
        <ul class="mt-8 divide-y divide-line border-y border-line">
            @foreach ($lines as $line)
                @php $v = $line->variant; @endphp
                <li class="flex gap-4 py-5">
                    <a href="{{ route('product', $v->product) }}" class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-white">
                        @if ($line->image)<img src="{{ $line->image }}" alt="" class="h-full w-full object-contain p-1">@endif
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('product', $v->product) }}" class="font-bold hover:text-gold">{{ $v->product->tr('name') }}</a>
                        <p class="text-sm text-ink-soft">{{ $v->tr('name') }}</p>
                        @if ($v->stock < $line->qty)
                            <p class="mt-1 text-xs font-bold text-red-700">{{ __('cart.not_enough_short', ['stock' => $v->stock]) }}</p>
                        @endif
                        <div class="mt-3 flex items-center gap-3">
                            <form method="POST" action="{{ route('cart.update') }}" class="flex items-center rounded-full border border-line bg-white">
                                @csrf
                                <input type="hidden" name="variant_id" value="{{ $v->id }}">
                                <button name="qty" value="{{ $line->qty - 1 }}" class="h-9 w-9" aria-label="−">−</button>
                                <span class="w-6 text-center text-sm font-bold">{{ $line->qty }}</span>
                                <button name="qty" value="{{ $line->qty + 1 }}" class="h-9 w-9 disabled:opacity-30" aria-label="+" @disabled($line->qty >= min(10, $v->stock))>+</button>
                            </form>
                            <form method="POST" action="{{ route('cart.update') }}">
                                @csrf
                                <input type="hidden" name="variant_id" value="{{ $v->id }}">
                                <button name="qty" value="0" class="text-sm text-ink-soft underline hover:text-red-700">{{ __('cart.remove') }}</button>
                            </form>
                        </div>
                    </div>
                    <p class="price font-bold">{{ OrderService::money($line->line) }} {{ __('common.jd') }}</p>
                </li>
            @endforeach
        </ul>

        <div class="mt-6 flex items-center justify-between text-lg">
            <span>{{ __('cart.subtotal') }}</span>
            <span class="price font-extrabold">{{ OrderService::money($subtotal) }} {{ __('common.jd') }}</span>
        </div>
        <p class="mt-1 text-sm text-ink-soft">{{ __('cart.delivery_hint', ['fee' => App\Models\Setting::get('delivery_fee')]) }}</p>

        <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3 sm:justify-between">
            <a href="{{ route('home') }}#collection" class="btn-ghost">{{ __('cart.continue') }}</a>
            <a href="{{ route('checkout') }}" class="btn-primary">{{ __('cart.checkout') }}</a>
        </div>
    @endif
</div>
@endsection

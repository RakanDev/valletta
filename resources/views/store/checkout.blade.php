@extends('layouts.store')
@section('title', __('checkout.title'))

@section('content')
@php use App\Services\OrderService; @endphp
<div class="mx-auto max-w-5xl px-4 pt-10">
    <h1 class="text-3xl font-extrabold">{{ __('checkout.title') }}</h1>
    <p class="mt-2 text-ink-soft">{{ __('checkout.intro') }}</p>

    @error('cart')<p class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ $message }} <a class="underline font-bold" href="{{ route('cart') }}">{{ __('nav.cart') }}</a></p>@enderror

    <div class="mt-8 grid gap-10 md:grid-cols-5">
        <form method="POST" action="{{ route('checkout.store') }}" class="md:col-span-3 space-y-5" x-data="{ sending: false }" @submit="sending = true">
            @csrf
            <div>
                <label for="customer_name" class="label">{{ __('checkout.name') }}</label>
                <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100" autocomplete="name" class="field">
                @error('customer_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="label">{{ __('checkout.phone') }}</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" placeholder="07XXXXXXXX" dir="ltr" class="field {{ $dir === 'rtl' ? 'text-right' : '' }}">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ __('checkout.phone_invalid') }}</p>@enderror
            </div>
            <div>
                <label for="city" class="label">{{ __('checkout.city') }}</label>
                <select id="city" name="city" required class="field">
                    <option value="">{{ __('checkout.choose_city') }}</option>
                    @foreach (config('valletta.cities') as $key => $names)
                        <option value="{{ $key }}" @selected(old('city') === $key)>{{ $names[$locale === 'ar' ? 0 : 1] }}</option>
                    @endforeach
                </select>
                @error('city')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="address" class="label">{{ __('checkout.address') }}</label>
                <textarea id="address" name="address" required rows="2" maxlength="500" placeholder="{{ __('checkout.address_ph') }}" class="field">{{ old('address') }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notes" class="label">{{ __('checkout.notes') }} <span class="font-normal text-ink-soft">({{ __('common.optional') }})</span></label>
                <textarea id="notes" name="notes" rows="2" maxlength="1000" class="field">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn-primary w-full py-4 text-base" :disabled="sending">
                <span x-show="!sending">{{ __('checkout.submit') }}</span>
                <span x-show="sending" x-cloak>{{ __('checkout.sending') }}</span>
            </button>
            <p class="text-xs text-ink-soft text-center">{{ __('checkout.whatsapp_hint') }}</p>
        </form>

        <aside class="md:col-span-2">
            <div class="rounded-3xl bg-white border border-line p-6 md:sticky md:top-28">
                <h2 class="font-extrabold">{{ __('checkout.summary') }}</h2>
                <ul class="mt-4 space-y-4">
                    @foreach ($lines as $line)
                        <li class="flex gap-3 text-sm">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-cream">
                                @if ($line->image)<img src="{{ $line->image }}" alt="" class="h-full w-full object-contain">@endif
                            </div>
                            <div class="flex-1">
                                <p class="font-bold">{{ $line->variant->product->tr('name') }}</p>
                                <p class="text-ink-soft">{{ $line->variant->tr('name') }} × {{ $line->qty }}</p>
                            </div>
                            <p class="price font-bold">{{ OrderService::money($line->line) }}</p>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-6 space-y-2 border-t border-line pt-4 text-sm">
                    <div class="flex justify-between"><dt>{{ __('cart.subtotal') }}</dt><dd class="price">{{ OrderService::money($subtotal) }} {{ __('common.jd') }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('checkout.delivery') }}</dt><dd class="price">{{ OrderService::money($fee) }} {{ __('common.jd') }}</dd></div>
                    <div class="flex justify-between text-lg font-extrabold pt-2"><dt>{{ __('checkout.total') }}</dt><dd class="price text-gold">{{ OrderService::money($subtotal + $fee) }} {{ __('common.jd') }}</dd></div>
                </dl>
                <p class="mt-4 rounded-xl bg-cream px-4 py-3 text-sm">💵 {{ __('checkout.cod') }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection

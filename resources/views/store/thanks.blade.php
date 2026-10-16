@extends('layouts.store')
@section('title', __('thanks.title'))

@section('content')
@php use App\Services\OrderService; @endphp
<div class="mx-auto max-w-xl px-4 pt-14 text-center" x-data x-init="setTimeout(() => window.location.href = @js($whatsappUrl), 2500)">
    <div class="mx-auto h-16 w-16 rounded-full bg-gold-pale grid place-items-center text-3xl">✓</div>
    <h1 class="mt-6 text-3xl font-extrabold">{{ __('thanks.title') }}</h1>
    <p class="mt-3 text-ink-soft">{{ __('thanks.number') }} <b class="text-ink" dir="ltr">{{ $order->number }}</b></p>
    <p class="mt-6 leading-relaxed">{{ __('thanks.text') }}</p>

    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn mt-8 w-full bg-[#25D366] text-white hover:bg-[#1ebe5b] py-4 text-base">
        {{ __('thanks.open_whatsapp') }}
    </a>
    <p class="mt-3 text-xs text-ink-soft">{{ __('thanks.auto') }}</p>

    <div class="mt-10 rounded-3xl bg-white border border-line p-6 text-start text-sm">
        <ul class="space-y-2">
            @foreach ($order->items as $item)
                <li class="flex justify-between gap-3">
                    <span>{{ $item->variant?->product?->tr('name') ?? $item->product_name }} — {{ $item->variant?->tr('name') ?? $item->variant_name }} × {{ $item->qty }}</span>
                    <span class="price font-bold">{{ OrderService::money($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-4 border-t border-line pt-3 flex justify-between font-extrabold">
            <span>{{ __('checkout.total') }}</span><span class="price text-gold">{{ OrderService::money($order->total) }} {{ __('common.jd') }}</span>
        </div>
    </div>
    <a href="{{ route('home') }}" class="btn-ghost mt-8">{{ __('thanks.back') }}</a>
</div>
@endsection

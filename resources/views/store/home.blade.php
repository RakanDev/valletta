@extends('layouts.store')
@section('title', 'Valletta')

@section('content')
    @php
        use App\Models\Setting;
        $heroImg = optional(App\Models\ProductImage::where('path', 'like', '%lifestyle%')->orderBy('id')->first())->url();
    @endphp

    <section class="mx-auto max-w-6xl px-4 pt-8 sm:pt-14 grid items-center gap-8 md:grid-cols-2">
        <div class="order-2 md:order-1">
            <p class="eyebrow">{{ __('home.eyebrow') }}</p>
            <h1 class="mt-4 font-display text-4xl sm:text-6xl leading-[1.1] {{ $locale === 'ar' ? 'font-sans font-extrabold sm:text-5xl leading-[1.3]' : '' }}">
                {{ __('home.hero_title') }}
            </h1>
            <p class="mt-5 text-lg text-ink-soft leading-relaxed max-w-md">{{ __('home.hero_text') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#collection" class="btn-primary">{{ __('home.shop_now') }}</a>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', Setting::get('whatsapp')) }}" target="_blank" rel="noopener" class="btn-ghost">{{ __('home.ask_whatsapp') }}</a>
            </div>
        </div>
        @if ($heroImg)
            <div class="order-1 md:order-2 relative">
                <div class="aspect-[4/5] overflow-hidden rounded-[2rem] bg-cream-deep">
                    <img src="{{ $heroImg }}" alt="" class="h-full w-full object-cover">
                </div>
                <div class="absolute -bottom-4 {{ $dir === 'rtl' ? '-right-2 sm:-right-6' : '-left-2 sm:-left-6' }} rounded-2xl bg-white px-5 py-4 shadow-xl shadow-ink/10">
                    <p class="font-display text-2xl text-gold" dir="ltr">Carry elegance</p>
                    <p class="text-xs text-ink-soft">{{ __('home.badge') }}</p>
                </div>
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-4 mt-16">
        <ul class="grid gap-4 sm:grid-cols-3 rounded-2xl border border-line bg-white/60 p-6 text-sm">
            <li class="flex items-center gap-3"><span class="text-2xl">🚚</span><span><b class="block">{{ __('home.usp_delivery') }}</b>{{ __('home.usp_delivery_sub', ['fee' => Setting::get('delivery_fee')]) }}</span></li>
            <li class="flex items-center gap-3"><span class="text-2xl">💵</span><span><b class="block">{{ __('home.usp_cod') }}</b>{{ __('home.usp_cod_sub') }}</span></li>
            <li class="flex items-center gap-3"><span class="text-2xl">💬</span><span><b class="block">{{ __('home.usp_support') }}</b>{{ __('home.usp_support_sub') }}</span></li>
        </ul>
    </section>

    <section id="collection" class="mx-auto max-w-6xl px-4 mt-20 scroll-mt-24">
        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <p class="eyebrow">{{ __('home.collection_eyebrow') }}</p>
                <h2 class="mt-2 text-3xl font-extrabold">{{ __('home.collection_title') }}</h2>
            </div>
            <p class="text-sm text-ink-soft">{{ trans_choice('home.models_count', $products->count(), ['count' => $products->count()]) }}</p>
        </div>

        @if ($products->isEmpty())
            <p class="text-ink-soft">{{ __('home.empty') }}</p>
        @else
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">
                @foreach ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>
@endsection

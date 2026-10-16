@extends('layouts.store')
@section('title', $product->tr('name'))
@section('description', $product->tr('short'))
@section('og_image', (string) $product->coverUrl())

@section('content')
@php
    use App\Services\OrderService;
    $general = $product->images->whereNull('product_variant_id')->map(fn ($i) => $i->url())->values();
    $variants = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'name' => $v->tr('name'),
        'stock' => $v->stock,
        'hex' => $v->color_hex,
        'thumb' => $v->images->first()?->url(),
        'images' => $v->images->map(fn ($i) => $i->url())->values()->concat($general)->values(),
    ])->values();
    $first = $variants->firstWhere('stock', '>', 0) ?? $variants->first();
@endphp

<div class="mx-auto max-w-6xl px-4 pt-6 sm:pt-10"
     x-data="{
        variants: @js($variants),
        general: @js($general),
        selected: @js($first['id'] ?? null),
        active: 0,
        qty: 1,
        get v() { return this.variants.find(x => x.id === this.selected) },
        get images() { return this.v ? this.v.images : this.general },
        pick(id) { this.selected = id; this.active = 0; this.qty = 1 },
     }">

    <nav class="text-sm text-ink-soft mb-6"><a href="{{ route('home') }}" class="hover:text-gold">{{ __('nav.home') }}</a> <span class="mx-1">/</span> {{ $product->tr('name') }}</nav>

    <div class="grid gap-10 md:grid-cols-2">
        {{-- Gallery --}}
        <div>
            <div class="aspect-square overflow-hidden rounded-3xl bg-white">
                <template x-for="(src, i) in images" :key="src">
                    <img x-show="active === i" :src="src" alt="{{ $product->tr('name') }}" class="h-full w-full object-contain">
                </template>
            </div>
            <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                <template x-for="(src, i) in images" :key="'t'+src">
                    <button type="button" @click="active = i" class="h-16 w-16 sm:h-20 sm:w-20 shrink-0 overflow-hidden rounded-xl bg-white border-2 transition"
                            :class="active === i ? 'border-gold' : 'border-transparent opacity-70 hover:opacity-100'" :aria-label="@js(__('product.photo')) + ' ' + (i + 1)">
                        <img :src="src" alt="" class="h-full w-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Details --}}
        <div>
            @if ($product->sku)<p class="eyebrow">{{ __('product.model') }} {{ $product->sku }}</p>@endif
            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">{{ $product->tr('name') }}</h1>
            <p class="price mt-4 text-3xl font-bold text-gold">{{ OrderService::money($product->price) }} <span class="text-lg">{{ __('common.jd') }}</span></p>
            @if ($product->tr('short'))
                <p class="mt-5 text-ink-soft leading-relaxed">{{ $product->tr('short') }}</p>
            @endif

            <form method="POST" action="{{ route('cart.add') }}" class="mt-8">
                @csrf
                <input type="hidden" name="variant_id" :value="selected">
                <p class="label">{{ __('product.color') }}: <span class="font-medium text-ink-soft" x-text="v?.name"></span></p>
                <div class="flex flex-wrap gap-3">
                    <template x-for="item in variants" :key="item.id">
                        <button type="button" @click="pick(item.id)" :title="item.name" :aria-label="item.name" :aria-pressed="selected === item.id"
                                class="relative h-14 w-14 rounded-full border-2 p-0.5 transition"
                                :class="selected === item.id ? 'border-gold' : 'border-line hover:border-ink/40'">
                            <span class="block h-full w-full overflow-hidden rounded-full bg-white" :style="item.thumb ? '' : 'background:' + (item.hex || '#ccc')">
                                <img x-show="item.thumb" :src="item.thumb" alt="" class="h-full w-full object-cover scale-150">
                            </span>
                            <span x-show="item.stock < 1" class="absolute inset-0 grid place-items-center">
                                <span class="block h-0.5 w-[110%] rotate-45 bg-ink/60"></span>
                            </span>
                        </button>
                    </template>
                </div>

                <p class="mt-4 text-sm font-medium" :class="v && v.stock < 1 ? 'text-red-700' : (v && v.stock <= 3 ? 'text-gold' : 'text-green-700')">
                    <span x-show="v && v.stock < 1">{{ __('product.sold_out') }}</span>
                    <span x-show="v && v.stock > 0 && v.stock <= 3" x-text="@js(__('product.only_left')).replace(':count', v?.stock)"></span>
                    <span x-show="v && v.stock > 3">{{ __('product.in_stock') }}</span>
                </p>

                @error('variant_id')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror

                <div class="mt-6 flex items-center gap-3">
                    <div class="flex items-center rounded-full border border-line bg-white" role="group" aria-label="{{ __('product.qty') }}">
                        <button type="button" class="h-12 w-11 text-xl" @click="qty = Math.max(1, qty - 1)" aria-label="−">−</button>
                        <input type="number" name="qty" x-model.number="qty" min="1" :max="Math.min(10, v?.stock || 1)" class="w-10 border-0 bg-transparent text-center font-bold p-0 focus:ring-0" readonly>
                        <button type="button" class="h-12 w-11 text-xl" @click="qty = Math.min(Math.min(10, v?.stock || 1), qty + 1)" aria-label="+">+</button>
                    </div>
                    <button type="submit" name="buy_now" value="1" class="btn-primary flex-1 py-3.5" :disabled="!v || v.stock < 1">{{ __('product.order_now') }}</button>
                </div>
                <button type="submit" class="btn-ghost mt-3 w-full" :disabled="!v || v.stock < 1">{{ __('product.add_to_cart') }}</button>
            </form>

            <ul class="mt-8 space-y-2 rounded-2xl bg-white/70 border border-line p-5 text-sm">
                <li>🚚 {{ __('product.delivery_note', ['fee' => App\Models\Setting::get('delivery_fee')]) }}</li>
                <li>💵 {{ __('product.cod_note') }}</li>
            </ul>

            @if ($product->tr('description'))
                <div class="mt-10">
                    <h2 class="text-lg font-extrabold">{{ __('product.description') }}</h2>
                    <div class="mt-3 text-ink-soft leading-relaxed space-y-3">
                        @foreach (preg_split('/\R{2,}/u', $product->tr('description')) as $para)
                            <p>{{ $para }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($product->trLines('features'))
                <div class="mt-8">
                    <h2 class="text-lg font-extrabold">{{ __('product.features') }}</h2>
                    <ul class="mt-3 space-y-2 text-ink-soft">
                        @foreach ($product->trLines('features') as $feature)
                            <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span><span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($product->dimensions)
                <p class="mt-6 text-sm"><b>{{ __('product.dimensions') }}:</b> <span dir="ltr">{{ $product->dimensions }}</span></p>
            @endif
        </div>
    </div>
</div>
@endsection

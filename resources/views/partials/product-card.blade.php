@php
    $soldOut = $product->totalStock() === 0;
    $swatches = $product->variants;
@endphp
<a href="{{ route('product', $product) }}" class="group block">
    <div class="relative aspect-square overflow-hidden rounded-2xl bg-white">
        @if ($product->coverUrl())
            <img src="{{ $product->coverUrl() }}" alt="{{ $product->tr('name') }}" loading="lazy"
                 class="h-full w-full object-contain p-3 transition duration-500 group-hover:scale-105 {{ $soldOut ? 'opacity-50' : '' }}">
        @endif
        @if ($soldOut)
            <span class="absolute top-3 start-3 rounded-full bg-ink px-3 py-1 text-xs font-bold text-cream">{{ __('product.sold_out') }}</span>
        @endif
    </div>
    <div class="mt-4 flex items-start justify-between gap-3">
        <div>
            <h3 class="font-bold leading-snug group-hover:text-gold transition">{{ $product->tr('name') }}</h3>
            <p class="mt-1 text-sm text-ink-soft">{{ trans_choice('product.colors_count', $swatches->count(), ['count' => $swatches->count()]) }}</p>
        </div>
        <p class="price shrink-0 font-bold text-gold">{{ App\Services\OrderService::money($product->price) }} {{ __('common.jd') }}</p>
    </div>
</a>

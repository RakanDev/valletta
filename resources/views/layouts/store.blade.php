@php
    use App\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('whatsapp'));
    $other = $locale === 'ar' ? 'en' : 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Valletta') · {{ __('brand.tagline_short') }}</title>
    <meta name="description" content="@yield('description', __('home.meta'))">
    <meta property="og:title" content="@yield('title', 'Valletta')">
    <meta property="og:description" content="@yield('description', __('home.meta'))">
    @if (trim($__env->yieldContent('og_image')))<meta property="og:image" content="@yield('og_image')">@endif
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <div class="bg-ink text-cream text-center text-xs sm:text-sm py-2 px-4">
        {{ __('brand.topbar', ['fee' => Setting::get('delivery_fee')]) }}
    </div>

    <header class="sticky top-0 z-30 bg-cream/95 backdrop-blur border-b border-line">
        <div class="mx-auto max-w-6xl px-4 h-16 sm:h-20 grid grid-cols-3 items-center">
            <div class="flex items-center gap-1">
                <a href="{{ route('locale', $other) }}" class="rounded-full px-3 py-1.5 text-sm font-bold hover:bg-cream-deep" hreflang="{{ $other }}">
                    {{ __('nav.switch_lang') }}
                </a>
            </div>
            <a href="{{ route('home') }}" class="justify-self-center" aria-label="Valletta">
                <img src="{{ asset('images/logo-gold.png') }}" alt="Valletta" class="h-10 sm:h-14 w-auto">
            </a>
            <div class="justify-self-end">
                <a href="{{ route('cart') }}" class="relative inline-flex items-center gap-2 rounded-full px-3 py-2 hover:bg-cream-deep" aria-label="{{ __('nav.cart') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                    <span class="hidden sm:inline text-sm font-bold">{{ __('nav.cart') }}</span>
                    @if ($cartCount > 0)
                        <span class="absolute -top-0.5 {{ $dir === 'rtl' ? '-left-0.5' : '-right-0.5' }} min-w-5 h-5 px-1 rounded-full bg-gold text-white text-[11px] font-bold grid place-items-center">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="mx-auto max-w-6xl w-full px-4 mt-4">
            <div class="rounded-xl bg-gold-pale/60 border border-gold/30 px-4 py-3 text-sm font-medium" role="status">{{ session('status') }}</div>
        </div>
    @endif

    <main class="flex-1">@yield('content')</main>

    <footer class="bg-ink text-cream/80 mt-20">
        <div class="mx-auto max-w-6xl px-4 py-12 grid gap-10 sm:grid-cols-3">
            <div>
                <img src="{{ asset('images/logo-light.png') }}" alt="Valletta" class="h-12 w-auto mb-4">
                <p class="text-sm leading-relaxed">{{ Setting::get('slogan_'.$locale) }}</p>
            </div>
            <div class="text-sm space-y-2">
                <p class="font-bold text-cream">{{ __('footer.how') }}</p>
                <p>🚚 {{ __('footer.delivery', ['fee' => Setting::get('delivery_fee')]) }}</p>
                <p>💵 {{ __('footer.cod') }}</p>
                <p>🕐 {{ __('footer.hours') }}</p>
            </div>
            <div class="text-sm space-y-2">
                <p class="font-bold text-cream">{{ __('footer.contact') }}</p>
                <a class="block hover:text-gold-pale" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">WhatsApp · <span dir="ltr">+{{ $wa }}</span></a>
                <a class="block hover:text-gold-pale" href="{{ Setting::get('instagram') }}" target="_blank" rel="noopener">Instagram</a>
                <a class="block hover:text-gold-pale" href="{{ Setting::get('facebook') }}" target="_blank" rel="noopener">Facebook</a>
            </div>
        </div>
        <div class="border-t border-cream/10 py-5 text-center text-xs text-cream/50">© {{ date('Y') }} Valletta. {{ __('footer.rights') }}</div>
    </footer>

    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" aria-label="{{ __('nav.whatsapp') }}"
       class="fixed bottom-5 {{ $dir === 'rtl' ? 'left-5' : 'right-5' }} z-40 h-14 w-14 rounded-full bg-[#25D366] text-white shadow-lg grid place-items-center hover:scale-105 transition">
        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.1 4.9 4.3 2.4 1 2.9.8 3.4.7.5-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.3ZM12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Z"/></svg>
    </a>
</body>
</html>

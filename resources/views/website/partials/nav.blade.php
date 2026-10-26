@php
    $mbLinks = [
        ['/', 'Home', ''],
        ['/process', 'Logistics', 'process'],
        ['/inventory', 'Inventory', 'inventory'],
        ['/services', 'Clearance', 'services'],
        ['/tracking', 'Tracking', 'tracking'],
        ['/about', 'About', 'about'],
        ['/contact', 'Contact', 'contact'],
    ];
    $mbActive = fn ($pattern) => $pattern === '' ? request()->is('/') : request()->is($pattern, $pattern . '/*');
@endphp
<a href="#main" class="mb-skip">Skip to content</a>

<header class="mb-header" id="mb-header">
    <a href="/" class="mb-brand" aria-label="Mapleby Inc. home">
        <img src="/brand/badge-96.png" srcset="/brand/badge-96.png 1x, /brand/badge-192.png 2x" width="48" height="48" alt="" class="mb-brand__badge">
        <span class="mb-brand__word">
            <span class="mb-brand__name">MAPLEBY</span>
            <span class="mb-brand__tag">Global Procurement</span>
        </span>
    </a>

    <nav class="mb-nav" aria-label="Main">
        @foreach (array_slice($mbLinks, 1) as $i => [$href, $label, $pattern])
            <a href="{{ $href }}" @class(['mb-nav__link', 'is-active' => $mbActive($pattern)]) @if($mbActive($pattern)) aria-current="page" @endif><span>0{{ $i + 1 }}</span>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="mb-header__actions">
        <a href="/app/login" class="mb-btn mb-btn--ghost mb-hide-sm">Staff Login</a>
        <a href="/shipment-request" class="mb-btn mb-btn--red mb-hide-md">Ship a Car</a>
        <button type="button" class="mb-burger" id="mb-burger" aria-label="Open menu" aria-expanded="false" aria-controls="mb-drawer">
            <span></span><span></span>
        </button>
    </div>
</header>

<div class="mb-drawer" id="mb-drawer" role="dialog" aria-modal="true" aria-label="Site menu" hidden>
    <div class="mb-drawer__leaf" aria-hidden="true">{!! file_get_contents(public_path('brand/leaf.svg')) !!}</div>
    <div class="mb-drawer__inner">
        <div class="mb-drawer__top">
            <img src="/brand/badge-192.png" width="64" height="64" alt="Mapleby" class="mb-drawer__badge">
            <span class="mb-drawer__route">Toronto <i>&rarr;</i> Lagos</span>
        </div>
        <nav class="mb-drawer__links" aria-label="Mobile">
            @foreach ($mbLinks as $i => [$href, $label, $pattern])
                <a href="{{ $href }}" @class(['is-active' => $mbActive($pattern)]) style="--i: {{ $i }}"><span>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</span>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mb-drawer__cta">
            <a href="/shipment-request" class="mb-btn mb-btn--red">Start a Shipment</a>
            <a href="/tracking" class="mb-btn mb-btn--ghost">Track a Vehicle</a>
        </div>
        <div class="mb-drawer__meta">
            <a href="tel:+14165550199">+1 (416) 555-0199</a>
            <a href="mailto:export@maplebyinc.com">export@maplebyinc.com</a>
            <a href="/app/login" class="mb-drawer__staff">Staff Login &rarr;</a>
        </div>
    </div>
</div>

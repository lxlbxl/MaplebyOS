{{-- Shared brand head: icons, social card, brand fonts and site-wide layout styles --}}
<meta name="theme-color" content="#050505">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="/brand/badge-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="/brand/badge-192.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.json">
<meta property="og:site_name" content="Mapleby Inc.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ url('/brand/og-image.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="preload" as="image" href="/brand/badge-96.png">
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;900&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/website/brand.css?v={{ filemtime(public_path('website/brand.css')) }}">

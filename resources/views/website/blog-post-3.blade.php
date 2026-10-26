<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Why Buy From Canada? — Mapleby Inc.</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600&family=Oswald:wght@400;500;700&display=swap"
        rel="stylesheet">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/website/styles.css">
    @include('website.partials.head')
</head>

<body class="bg-[#0A0F14] text-white overflow-x-hidden selection:bg-[#D43F3A] selection:text-white font-['Archivo']">

    @include('website.partials.nav')


    <article class="pt-40 pb-20 px-6 md:px-0 max-w-3xl mx-auto">
        <header id="main" class="mb-12 text-center">
            <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block">Insight • Jan 02,
                2026</span>
            <h1 class="font-['Oswald'] text-4xl md:text-6xl font-bold uppercase leading-none mb-8">Why Buy From Canada?
            </h1>
        </header>

        <img src="https://images.unsplash.com/photo-1580674684081-7617fbf3d745?q=80&w=2000"
            class="w-full aspect-video object-cover mb-12 grayscale border border-white/10">

        <div class="prose prose-invert prose-lg mx-auto">
            <p>Many Nigerian importers look to the US for cars (IAAI, Copart), often ending up with salvaged wrecks.
                Here is why the Canadian market is a hidden gem.</p>
            <h3>1. Metric System</h3>
            <p>Canadian cars use Kilometers (KM), just like Nigeria. No need to mentally convert miles or guess service
                intervals.</p>
            <h3>2. Maintenance Standards</h3>
            <p>Canadians generally maintain vehicles strictly to dealer standards due to leasing agreements. You get a
                healthier engine.</p>
            <h3>3. Clean Titles</h3>
            <p>We focus on clean title vehicles from dealer auctions, not accident wrecks. The body integrity is
                preserved.</p>
            <h3>4. Currency Advantage</h3>
            <p>The CAD is often weaker than the USD, giving you more purchasing power for the same amount of Naira
                converted.</p>
        </div>
    </article>

    <script src="/website/script.js"></script>
    @include('website.partials.footer')
</body>

</html>
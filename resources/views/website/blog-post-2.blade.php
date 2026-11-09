<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearing Costs — Mapleby Inc.</title>
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
            <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block">Logistics • Jan 15,
                2026</span>
            <h1 class="font-['Oswald'] text-4xl md:text-6xl font-bold uppercase leading-none mb-8">Understanding
                Clearing Costs</h1>
        </header>

        <img src="https://images.unsplash.com/photo-1616401784845-180882ba9ba8?q=80&w=2000"
            class="w-full aspect-video object-cover mb-12 grayscale border border-white/10">

        <div class="prose prose-invert prose-lg mx-auto">
            <p>Clearing a vehicle at Tincan Island is often the most confusing part of the importation process. Here is
                what you need to know for 2026.</p>
            <h3>Duty Calculation</h3>
            <p>Duty is calculated based on the year of manufacture and the engine capacity. Newer vehicles (2020+)
                attract a higher valuation but a lower surcharge percentage in some categories.</p>
            <h3>Terminal Charges & Shipping Line Agencies</h3>
            <p>These are local charges paid to the port operator and the shipping line. They cover the handling of the
                container or vehicle while at the port.</p>
            <h3>Our Advice</h3>
            <p>Always budget 10% above the estimate for ancillary costs like gate fees and demurrage buffers. </p>
        </div>
    </article>

    <script src="/website/script.js"></script>
    @include('website.partials.footer')
</body>

</html>
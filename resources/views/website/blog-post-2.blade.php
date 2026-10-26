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
    <link rel="stylesheet" href="styles.css">
</head>

<body class="bg-[#0A0F14] text-white overflow-x-hidden selection:bg-[#D43F3A] selection:text-white font-['Archivo']">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 px-6 py-4 flex justify-between items-center transition-all duration-300" id="navbar">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-[#D43F3A] clip-corner"></div>
            <a href="index.html" class="font-['Oswald'] text-2xl font-bold tracking-tight text-white">MAPLEBY</a>
        </div>
        <div class="hidden md:flex gap-8 items-center font-mono text-xs uppercase tracking-widest text-gray-300">
            <a href="index.html" class="hover:text-[#D43F3A] transition-colors">Home</a>
            <a href="blog.html"
                class="hover:text-[#D43F3A] transition-colors border-b border-[#D43F3A] text-white">Blog</a>
        </div>
        <button onclick="toggleModal()" class="hidden md:block btn-primary text-xs">Get Quote</button>
        <button class="md:hidden text-white" onclick="toggleMobileMenu()"><i data-lucide="menu"
                class="w-8 h-8"></i></button>
    </nav>
    <div id="mobile-menu"
        class="fixed inset-0 bg-[#0A0F14] z-40 translate-x-full transition-transform duration-500 flex flex-col justify-center px-8 border-l border-white/10">
        <button onclick="toggleMobileMenu()" class="absolute top-6 right-6"><i data-lucide="x"
                class="w-8 h-8"></i></button>
        <div class="flex flex-col gap-6 font-['Oswald'] text-4xl uppercase">
            <a href="index.html" class="hover:text-[#D43F3A]">Home</a>
            <a href="blog.html" class="text-[#D43F3A]">Back to Blog</a>
        </div>
    </div>

    <article class="pt-40 pb-20 px-6 md:px-0 max-w-3xl mx-auto">
        <header class="mb-12 text-center">
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

    <script src="script.js"></script>
</body>

</html>
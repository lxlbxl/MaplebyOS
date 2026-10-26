<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory — Mapleby Inc.</title>

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
  <nav id="navbar"
    class="fixed w-full z-[90] px-6 md:px-16 py-6 flex justify-between items-start transition-all duration-700 bg-black/80 backdrop-blur-xl border-b border-white/5">
    <div class="flex flex-col">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-2 h-2 bg-[var(--maple-crimson)]"></div>
        <a href="index.html"
          class="font-['Oswald'] text-4xl leading-none tracking-tighter font-bold text-white decoration-0">MAPLEBY</a>
      </div>
      <span class="font-mono text-[9px] tracking-[0.5em] text-[var(--maple-crimson)] uppercase ml-5">Global
        Procurement</span>
    </div>

    <div class="hidden lg:flex gap-16 items-center">
      <div class="flex gap-12 font-mono text-[10px] uppercase tracking-widest text-gray-400">
        <a href="process.html" class="hover:text-white transition-colors flex items-center gap-2"><span
            class="text-[var(--maple-crimson)]">01</span> Logistics</a>
        <a href="inventory.html" class="hover:text-white transition-colors flex items-center gap-2"><span
            class="text-[var(--maple-crimson)]">02</span> Inventory</a>
        <a href="services.html" class="hover:text-white transition-colors flex items-center gap-2"><span
            class="text-[var(--maple-crimson)]">03</span> Clearance</a>
        <a href="about.html" class="hover:text-white transition-colors flex items-center gap-2"><span
            class="text-[var(--maple-crimson)]">04</span> About</a>
        <a href="contact.html" class="hover:text-white transition-colors flex items-center gap-2"><span
            class="text-[var(--maple-crimson)]">05</span> Network</a>
      </div>
      <a href="/app/login" class="btn-command text-lg flex items-center justify-center">Staff Login</a>
    </div>

    <button class="lg:hidden text-white mt-2" onclick="toggleMobileMenu()">
      <i data-lucide="menu" class="w-8 h-8"></i>
    </button>
  </nav>

  <!-- Mobile Menu -->
  <div id="mobile-menu"
    class="fixed inset-0 bg-[#0A0F14] z-[100] translate-x-full transition-transform duration-500 flex flex-col justify-center px-8 border-l border-white/10">
    <button onclick="toggleMobileMenu()" class="absolute top-6 right-6"><i data-lucide="x"
        class="w-8 h-8 text-white"></i></button>
    <div class="flex flex-col gap-8 font-['Oswald'] text-5xl uppercase text-white">
      <a href="index.html" class="hover:text-[var(--maple-crimson)] transition-colors">Home</a>
      <a href="process.html" class="hover:text-[var(--maple-crimson)] transition-colors">01. Logistics</a>
      <a href="inventory.html" class="hover:text-[var(--maple-crimson)] transition-colors">02. Inventory</a>
      <a href="services.html" class="hover:text-[var(--maple-crimson)] transition-colors">03. Clearance</a>
      <a href="about.html" class="hover:text-[var(--maple-crimson)] transition-colors">04. About</a>
      <a href="contact.html" class="hover:text-[var(--maple-crimson)] transition-colors">05. Network</a>
      <a href="/app/login" class="text-[var(--maple-crimson)] mt-4 text-3xl">Staff Login</a>
    </div>
  </div>

  <!-- Header -->
  <header class="pt-40 pb-12 px-6 md:px-20 border-b border-white/10">
    <div class="flex flex-col md:flex-row justify-between items-end gap-8">
      <div>
        <h1 class="font-['Oswald'] text-5xl md:text-7xl font-bold uppercase leading-none mb-4 reveal-on-scroll">
          Available <span class="text-outline-w">Stock</span>
        </h1>
        <p class="text-gray-400 reveal-on-scroll delay-100">Verified vehicles ready for immediate export.</p>
      </div>

      <!-- Filters -->
      <div class="flex gap-4 reveal-on-scroll delay-200">
        <button
          class="px-4 py-2 bg-white/5 border border-white/10 text-xs font-mono uppercase hover:bg-[#D43F3A] hover:text-[#0A0F14] transition-colors">All
          Stock</button>
        <button
          class="px-4 py-2 bg-transparent border border-white/10 text-xs font-mono uppercase hover:bg-[#D43F3A] hover:text-[#0A0F14] transition-colors">SUVs</button>
        <button
          class="px-4 py-2 bg-transparent border border-white/10 text-xs font-mono uppercase hover:bg-[#D43F3A] hover:text-[#0A0F14] transition-colors">Sedans</button>
      </div>
    </div>
  </header>

  <!-- Inventory Grid -->
  <section class="py-20 px-6 md:px-20 bg-[#0A0F14] relative">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

      <!-- Car Card 1 -->
      <div
        class="group border border-white/10 bg-[#0F1419] hover:border-[#D43F3A] transition-all duration-300 reveal-on-scroll">
        <div class="relative aspect-[16/10] overflow-hidden">
          <img src="https://images.unsplash.com/photo-1533473359331-0135ef1bcfb0?q=80&w=2670&auto=format&fit=crop"
            class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700 grayscale group-hover:grayscale-0">
          <div class="absolute top-4 left-4 bg-[#D43F3A] text-white text-[10px] font-bold uppercase px-3 py-1">Ready to
            Ship</div>
        </div>
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase">2020 Lexus RX 350</h3>
            <span class="font-mono text-[#D43F3A] font-bold">$38,500</span>
          </div>
          <div
            class="grid grid-cols-2 gap-y-2 text-xs font-mono text-gray-500 uppercase mb-6 border-b border-white/10 pb-6">
            <span>Mileage: 45,000 KM</span>
            <span>Location: Toronto</span>
            <span>VIN: ...8X92</span>
            <span>Drive: AWD</span>
          </div>
          <button onclick="toggleModal()" class="w-full btn-secondary py-3 text-xs">Request Info</button>
        </div>
      </div>

      <!-- Inventory App -->
      <section class="min-h-screen px-6 md:px-20 py-12">
        <!-- Filters -->
        <div class="mb-12 bg-[#141414] border border-white/10 p-6 md:p-8">
          <div class="grid md:grid-cols-4 gap-6">
            <div class="space-y-2">
              <label class="font-mono text-[10px] uppercase text-gray-500 tracking-widest">Make</label>
              <select id="filter-make"
                class="w-full bg-black border border-white/10 px-4 py-3 font-mono text-xs uppercase text-white outline-none focus:border-[#D43F3A]">
                <option value="">All Makes</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="font-mono text-[10px] uppercase text-gray-500 tracking-widest">Year</label>
              <select id="filter-year"
                class="w-full bg-black border border-white/10 px-4 py-3 font-mono text-xs uppercase text-white outline-none focus:border-[#D43F3A]">
                <option value="">All Years</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="font-mono text-[10px] uppercase text-gray-500 tracking-widest">Sort By</label>
              <select id="sort-select"
                class="w-full bg-black border border-white/10 px-4 py-3 font-mono text-xs uppercase text-white outline-none focus:border-[#D43F3A]">
                <option value="created_at-desc">Newest Arrivals</option>
                <option value="sale_price-asc">Price: Low to High</option>
                <option value="sale_price-desc">Price: High to Low</option>
                <option value="year-desc">Year: New to Old</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="font-mono text-[10px] uppercase text-gray-500 tracking-widest">Search</label>
              <div class="relative">
                <input id="search-input" type="text" placeholder="VIN / MODEL"
                  class="w-full bg-black border border-white/10 pl-4 pr-10 py-3 font-mono text-xs text-white outline-none focus:border-[#D43F3A]">
                <i data-lucide="search" class="w-4 h-4 text-gray-500 absolute right-3 top-1/2 -translate-y-1/2"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Grid -->
        <div id="inventory-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
          <!-- Injected by inventory.js -->
        </div>

        <!-- Pagination -->
        <div id="pagination" class="flex justify-center items-center gap-4">
          <!-- Injected by inventory.js -->
        </div>
      </section>

      <!-- Footer -->
      <footer class="bg-black text-white/50 pt-20 pb-8 px-6 md:px-20 border-t border-white/10">
        <div
          class="flex flex-col md:flex-row justify-between items-center text-xs font-mono uppercase border-t border-white/10 pt-8">
          <span>&copy; 2026 Mapleby Inc. All rights reserved.</span>
          <div class="flex gap-6 mt-4 md:mt-0">
            <a href="#" class="hover:text-white">Privacy</a>
            <a href="#" class="hover:text-white">Terms</a>
          </div>
        </div>
      </footer>

    </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
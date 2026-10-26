<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services — Mapleby Inc.</title>

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
  <header class="pt-40 pb-20 px-6 md:px-20 border-b border-white/10 maple-grid">
    <div class="max-w-4xl">
      <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block reveal-on-scroll">///
        End-to-End Solutions</span>
      <h1 class="font-['Oswald'] text-6xl md:text-8xl font-bold uppercase leading-none mb-8 reveal-on-scroll delay-100">
        Logistics <span class="text-outline-w">Unbound</span>
      </h1>
      <p class="text-xl text-gray-400 max-w-2xl reveal-on-scroll delay-200">
        From the auction lot in Toronto to the port in Lagos. We handle procurement, transport, shipping, and clearing
        documentation.
      </p>
    </div>
  </header>

  <!-- Service 1: Procurement -->
  <section class="py-24 px-6 md:px-20 border-b border-white/10">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div class="order-2 md:order-1 reveal-on-scroll">
        <div class="bg-white/5 p-4 w-16 h-16 flex items-center justify-center rounded-none mb-6">
          <i data-lucide="shopping-cart" class="w-8 h-8 text-[#D43F3A]"></i>
        </div>
        <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-6">Vehicle Procurement</h2>
        <p class="text-gray-400 mb-6 leading-relaxed">
          Don't just buy a car; buy the right car. We have licensed access to major Canadian dealer auctions (Manheim,
          Adesa) and private dealer networks.
          We inspect, verify, and bid on your behalf to get you wholesale pricing.
        </p>
        <ul class="space-y-4 mb-8">
          <li class="flex items-start gap-4">
            <i data-lucide="check" class="text-[#D43F3A] w-5 h-5 mt-1"></i>
            <div>
              <strong class="block text-white uppercase text-sm tracking-wide">Auction Access</strong>
              <span class="text-gray-500 text-sm">Direct bidding at wholesale rates.</span>
            </div>
          </li>
          <li class="flex items-start gap-4">
            <i data-lucide="check" class="text-[#D43F3A] w-5 h-5 mt-1"></i>
            <div>
              <strong class="block text-white uppercase text-sm tracking-wide">Physical Inspection</strong>
              <span class="text-gray-500 text-sm"> detailed video & photo reports before purchase.</span>
            </div>
          </li>
        </ul>
        <button onclick="openTerminal('PROCUREMENT')" class="btn-secondary">Start Buying</button>
      </div>

      <div
        class="order-1 md:order-2 h-[400px] bg-white/5 relative group overflow-hidden border border-white/10 reveal-on-scroll delay-100 parallax-container">
        <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?q=80&w=2670&auto=format&fit=crop"
          class="absolute inset-0 w-full h-[120%] object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-700 grayscale group-hover:grayscale-0">
        <div
          class="absolute bottom-6 left-6 bg-[#D43F3A] px-4 py-1 text-xs font-bold uppercase tracking-widest text-[#0A0F14]">
          Source
        </div>
      </div>
    </div>
  </section>

  <!-- Service 2: Shipping -->
  <section class="py-24 px-6 md:px-20 border-b border-white/10 bg-[#0F1419]">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div
        class="h-[400px] bg-white/5 relative group overflow-hidden border border-white/10 reveal-on-scroll parallax-container">
        <img src="https://images.unsplash.com/photo-1577702312572-5b960b76a3a6?q=80&w=2670&auto=format&fit=crop"
          class="absolute inset-0 w-full h-[120%] object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-700 grayscale group-hover:grayscale-0">
        <div
          class="absolute bottom-6 right-6 bg-white px-4 py-1 text-xs font-bold uppercase tracking-widest text-[#0A0F14]">
          Transit
        </div>
      </div>

      <div class="reveal-on-scroll delay-100">
        <div class="bg-white/5 p-4 w-16 h-16 flex items-center justify-center rounded-none mb-6">
          <i data-lucide="container" class="w-8 h-8 text-[#D43F3A]"></i>
        </div>
        <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-6">Ocean Freight (RoRo & Container)</h2>
        <p class="text-gray-400 mb-6 leading-relaxed">
          Whether you need a single car shipped Roll-on/Roll-off (RoRo) or a secure 40ft container for luxury vehicles
          and goods, we handle the logistics from Montreal/Halifax to Lagos.
        </p>
        <div class="grid grid-cols-2 gap-4 mb-8">
          <div class="bg-white/5 p-4 border border-white/5">
            <h4 class="font-bold text-white mb-2">RORO</h4>
            <p class="text-xs text-gray-500">Best for running vehicles. Cost-effective standard shipping.</p>
          </div>
          <div class="bg-white/5 p-4 border border-white/5">
            <h4 class="font-bold text-white mb-2">CONTAINER</h4>
            <p class="text-xs text-gray-500">Best for luxury cars, non-runners, or personal goods.</p>
          </div>
        </div>
        <button onclick="openTerminal('LOGISTICS')" class="btn-primary">Get Shipping Quote</button>
      </div>
    </div>
  </section>

  <!-- Service 3: Clearing -->
  <section class="py-24 px-6 md:px-20 border-b border-white/10">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div class="order-2 md:order-1 reveal-on-scroll">
        <div class="bg-white/5 p-4 w-16 h-16 flex items-center justify-center rounded-none mb-6">
          <i data-lucide="file-text" class="w-8 h-8 text-[#D43F3A]"></i>
        </div>
        <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-6">Clearing & Documentation</h2>
        <p class="text-gray-400 mb-6 leading-relaxed">
          The biggest headache in shipping to Nigeria is customs. We prepare all necessary export documents and connect
          you with verified clearing agents to ensure a smooth release at Tincan.
        </p>
        <ul class="space-y-4 mb-8">
          <li class="flex items-start gap-4">
            <i data-lucide="check" class="text-[#D43F3A] w-5 h-5 mt-1"></i>
            <span class="text-gray-300">Bill of Lading Management</span>
          </li>
          <li class="flex items-start gap-4">
            <i data-lucide="check" class="text-[#D43F3A] w-5 h-5 mt-1"></i>
            <span class="text-gray-300">Export Declaration (B13A)</span>
          </li>
          <li class="flex items-start gap-4">
            <i data-lucide="check" class="text-[#D43F3A] w-5 h-5 mt-1"></i>
            <span class="text-gray-300">Duty Estimation & Agent Referral</span>
          </li>
        </ul>
      </div>

      <div
        class="order-1 md:order-2 h-[400px] bg-white/5 relative group overflow-hidden border border-white/10 reveal-on-scroll delay-100 parallax-container">
        <img src="https://images.unsplash.com/photo-1616401784845-180882ba9ba8?q=80&w=2670&auto=format&fit=crop"
          class="absolute inset-0 w-full h-[120%] object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-700 grayscale group-hover:grayscale-0">
        <div
          class="absolute bottom-6 left-6 bg-[#D43F3A] px-4 py-1 text-xs font-bold uppercase tracking-widest text-[#0A0F14]">
          Destination
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-black text-white/50 pt-20 pb-8 px-6 md:px-20 border-t border-white/10">
    <div class="grid md:grid-cols-4 gap-12 mb-20">
      <div class="col-span-1 md:col-span-2">
        <div class="flex items-center gap-2 mb-6">
          <div class="w-6 h-6 bg-[#D43F3A] clip-corner"></div>
          <span class="font-['Oswald'] text-xl font-bold text-white tracking-tight">MAPLEBY INC.</span>
        </div>
        <p class="max-w-md text-sm leading-relaxed">
          Specialized logistics solutions connecting Canada and West Africa. We simplify the complexities of
          international vehicle export and freight forwarding.
        </p>
      </div>
      <div>
        <h4 class="font-mono text-white text-xs uppercase tracking-widest mb-6">Quick Links</h4>
        <ul class="space-y-3 text-sm">
          <li><a href="index.html" class="hover:text-white">Home</a></li>
          <li><a href="inventory.html" class="hover:text-white">Available Cars</a></li>
          <li><a href="services.html" class="hover:text-white">Shipping Rates</a></li>
          <li><a href="contact.html" class="hover:text-white">Contact Us</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-mono text-white text-xs uppercase tracking-widest mb-6">Contact</h4>
        <ul class="space-y-3 text-sm">
          <li><i data-lucide="map-pin" class="w-4 h-4 inline mr-2"></i> 96 Millwick Dr, Toronto, ON</li>
          <li><i data-lucide="phone" class="w-4 h-4 inline mr-2"></i> +1 (416) 555-0199</li>
          <li><i data-lucide="mail" class="w-4 h-4 inline mr-2"></i> export@mapleby.com</li>
        </ul>
      </div>
    </div>
    <div
      class="flex flex-col md:flex-row justify-between items-center text-xs font-mono uppercase border-t border-white/10 pt-8">
      <span>&copy; 2026 Mapleby Inc. All rights reserved.</span>
      <div class="flex gap-6 mt-4 md:mt-0">
        <a href="#" class="hover:text-white">Privacy</a>
        <a href="#" class="hover:text-white">Terms</a>
      </div>
    </div>
  </footer>

  <!-- Dynamic Terminal Modal -->
  <div id="terminal-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeTerminal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl px-6">
      <div id="terminal-panel" class="bg-[#141414] border-t-[4px] border-t-[#D43F3A] overflow-hidden relative">
        <!-- Using inline styles/classes mapping to index.html logic but adapted for this file's simpler CSS if needed. 
             Actually index.html used .panel-steel. services.html uses bg-[#141414]. I will stick to services.html aesthetic but use the structure required by forms.js -->

        <!-- Terminal Header -->
        <div class="bg-white/5 p-6 flex justify-between items-center border-b border-white/5">
          <div>
            <span id="terminal-subtitle"
              class="font-mono text-[10px] text-[#D43F3A] uppercase tracking-[0.3em] block mb-1">System</span>
            <h3 id="terminal-title" class="font-['Oswald'] text-3xl uppercase leading-none text-white">Terminal</h3>
          </div>
          <button onclick="closeTerminal()" class="text-gray-500 hover:text-white transition-colors">
            <i data-lucide="x" class="w-6 h-6"></i>
          </button>
        </div>

        <!-- Progress Line -->
        <div class="w-full h-1 bg-white/5">
          <div id="terminal-progress" class="h-full bg-[#D43F3A] transition-all duration-500" style="width: 0%"></div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="terminal-content" class="p-8 md:p-12 min-h-[400px] flex flex-col justify-center text-white">
          <!-- Steps injected by forms.js -->
        </div>
      </div>
    </div>
  </div>

  <script src="forms.js"></script>

  <script src="script.js"></script>
</body>

</html>
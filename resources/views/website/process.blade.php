<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Process — Mapleby Inc.</title>

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


  <!-- Header -->
  <header id="main" class="pt-40 pb-20 px-6 md:px-20 border-b border-white/10 maple-grid">
    <div class="max-w-4xl">
      <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block reveal-on-scroll">///
        Workflow</span>
      <h1 class="font-['Oswald'] text-6xl md:text-8xl font-bold uppercase leading-none mb-8 reveal-on-scroll delay-100">
        System <span class="text-outline-w">Protocol</span>
      </h1>
      <p class="text-xl text-gray-400 max-w-2xl reveal-on-scroll delay-200">
        A transparent, streamlined path from selection to posession. We remove the uncertainty from international
        transactions.
      </p>
    </div>
  </header>

  <!-- Timeline Section -->
  <section class="py-24 px-6 md:px-20 relative">
    <div class="absolute inset-0 bg-gradient-to-b from-[#0A0F14] to-black -z-10"></div>
    <div class="absolute left-6 md:left-1/2 top-24 bottom-24 w-px bg-white/10 hidden md:block"></div>

    <div class="space-y-24 relative max-w-6xl mx-auto">

      <!-- Step 1 -->
      <div class="grid md:grid-cols-2 gap-12 items-center relative reveal-on-scroll">
        <div class="text-right pr-12 hidden md:block">
          <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-4 text-[#D43F3A]">01. Selection</h2>
          <p class="text-gray-400">Choose from our inventory or commission a custom search. <br> We verify VIN,
            condition, and history.</p>
        </div>
        <div
          class="hidden md:absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-[#D43F3A] rounded-full border-4 border-[#0A0F14]">
        </div>
        <div class="pl-0 md:pl-12">
          <div class="bg-white/5 border border-white/10 p-8 relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#D43F3A]/5 group-hover:bg-[#D43F3A]/20 transition-colors"></div>
            <i data-lucide="search" class="w-12 h-12 text-white mb-6"></i>
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase mb-2 md:hidden text-[#D43F3A]">01. Selection</h3>
            <p class="text-gray-400 md:hidden mb-4">Choose from our inventory or commission a custom search.</p>
            <ul class="text-xs font-mono uppercase text-gray-400 space-y-2">
              <li>- Auction Access Enabled</li>
              <li>- History Report Generated</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="grid md:grid-cols-2 gap-12 items-center relative reveal-on-scroll delay-100">
        <div class="pr-0 md:pr-12 order-2 md:order-1">
          <div class="bg-white/5 border border-white/10 p-8 relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#D43F3A]/5 group-hover:bg-[#D43F3A]/20 transition-colors"></div>
            <i data-lucide="credit-card" class="w-12 h-12 text-white mb-6"></i>
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase mb-2 md:hidden text-[#D43F3A]">02. Payment</h3>
            <p class="text-gray-400 md:hidden mb-4">Secure transaction processing.</p>
            <ul class="text-xs font-mono uppercase text-gray-400 space-y-2">
              <li>- Invoice Generation</li>
              <li>- CAD/NGN Exchange Support</li>
            </ul>
          </div>
        </div>
        <div
          class="hidden md:absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-[#2E3440] rounded-full border-4 border-[#0A0F14]">
        </div>
        <div class="text-left pl-12 hidden md:block order-1 md:order-2">
          <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-4">02. Secure Payment</h2>
          <p class="text-gray-400">Transparent invoicing. <br> We accept payments in Canada or via local partners in
            Lagos.</p>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="grid md:grid-cols-2 gap-12 items-center relative reveal-on-scroll delay-200">
        <div class="text-right pr-12 hidden md:block">
          <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-4">03. Transport</h2>
          <p class="text-gray-400">Trucking to port and loading onto the vessel. <br> Live tracking updates provided.
          </p>
        </div>
        <div
          class="hidden md:absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-[#2E3440] rounded-full border-4 border-[#0A0F14]">
        </div>
        <div class="pl-0 md:pl-12">
          <div class="bg-white/5 border border-white/10 p-8 relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#D43F3A]/5 group-hover:bg-[#D43F3A]/20 transition-colors"></div>
            <i data-lucide="container" class="w-12 h-12 text-white mb-6"></i>
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase mb-2 md:hidden text-[#D43F3A]">03. Transport</h3>
            <ul class="text-xs font-mono uppercase text-gray-400 space-y-2">
              <li>- Port Delivery</li>
              <li>- Bill of Lading Issued</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Step 4 -->
      <div class="grid md:grid-cols-2 gap-12 items-center relative reveal-on-scroll delay-300">
        <div class="pr-0 md:pr-12 order-2 md:order-1">
          <div class="bg-white/5 border border-white/10 p-8 relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#D43F3A]/5 group-hover:bg-[#D43F3A]/20 transition-colors"></div>
            <i data-lucide="check-circle" class="w-12 h-12 text-white mb-6"></i>
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase mb-2 md:hidden text-[#D43F3A]">04. Delivery</h3>
            <ul class="text-xs font-mono uppercase text-gray-400 space-y-2">
              <li>- Customs Clearing</li>
              <li>- Handover in Lagos</li>
            </ul>
          </div>
        </div>
        <div
          class="hidden md:absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-[#D43F3A] rounded-full border-4 border-[#0A0F14]">
        </div>
        <div class="text-left pl-12 hidden md:block order-1 md:order-2">
          <h2 class="font-['Oswald'] text-4xl font-bold uppercase mb-4 text-[#D43F3A]">04. Arrival</h2>
          <p class="text-gray-400">Arrival at Tincan Island/Apapa. <br> Our agents handle clearing and handover keys to
            you.</p>
        </div>
      </div>

    </div>
  </section>

    @include('website.partials.footer')
  <!-- Dynamic Terminal Modal -->
  <div id="terminal-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeTerminal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl px-6">
      <div id="terminal-panel" class="bg-[#141414] border-t-[4px] border-t-[#D43F3A] overflow-hidden relative">
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
        <div class="w-full h-1 bg-white/5">
          <div id="terminal-progress" class="h-full bg-[#D43F3A] transition-all duration-500" style="width: 0%"></div>
        </div>
        <div id="terminal-content" class="p-8 md:p-12 min-h-[400px] flex flex-col justify-center text-white"></div>
      </div>
    </div>
  </div>

  <script src="/website/forms.js"></script>

  <script src="/website/script.js"></script>
</body>

</html>
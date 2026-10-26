<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About — Mapleby Inc.</title>

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

  <!-- Hero -->
  <header class="pt-40 pb-20 px-6 md:px-20 border-b border-white/10 maple-grid">
    <div class="max-w-4xl">
      <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block reveal-on-scroll">/// Our
        Mission</span>
      <h1 class="font-['Oswald'] text-6xl md:text-8xl font-bold uppercase leading-none mb-8 reveal-on-scroll delay-100">
        Bridging <span class="text-outline-w">Markets</span>
      </h1>
      <p class="text-xl text-gray-400 max-w-2xl reveal-on-scroll delay-200 leading-relaxed">
        Founded with a singular purpose: to make the Canadian automotive market accessible, transparent, and secure for
        Nigerian buyers.
      </p>
    </div>
  </header>

  <!-- Narrative Section -->
  <section class="py-24 px-6 md:px-20 relative">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div class="order-2 md:order-1 reveal-on-scroll">
        <span class="block text-[#D43F3A] font-bold uppercase mb-4 tracking-widest text-sm">The Problem</span>
        <h3 class="font-['Oswald'] text-3xl font-bold uppercase mb-6">The Gap in Trust</h3>
        <p class="text-gray-400 mb-6 leading-relaxed">
          For years, buyers in Nigeria faced significant risks when importing vehicles. Hidden salvage titles, odometer
          rollbacks, and unreliable middlemen made the process a gamble.
        </p>
        <div class="h-px w-20 bg-white/20 my-8"></div>
        <span class="block text-[#D43F3A] font-bold uppercase mb-4 tracking-widest text-sm">Our Solution</span>
        <h3 class="font-['Oswald'] text-3xl font-bold uppercase mb-6">Vertical Integration</h3>
        <p class="text-gray-400 mb-6 leading-relaxed">
          Mapleby Inc. isn't just a shipper. We are a licensed Canadian dealership and a registered logistics entity. We
          control every step of the chain—from the auction lane in Toronto to the handover in Lagos. No middlemen, no
          stories.
        </p>
      </div>
      <div class="order-1 md:order-2 h-[600px] relative reveal-on-scroll delay-100">
        <!-- Image Grid -->
        <div class="absolute top-0 right-0 w-3/4 h-3/4 bg-white/5 border border-white/10 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1565514020125-61845bb08d3e?q=80&w=1000&auto=format&fit=crop"
            class="w-full h-full object-cover grayscale opacity-80">
        </div>
        <div
          class="absolute bottom-0 left-0 w-3/4 h-3/4 bg-[#141414] border border-[#D43F3A] z-10 overflow-hidden text-center flex flex-col items-center justify-center p-8">
          <span class="font-['Oswald'] text-6xl font-bold text-white mb-2">2500+</span>
          <span class="font-mono text-gray-500 uppercase tracking-widest">Vehicles Delivered</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Team / Values -->
  <section class="py-24 px-6 md:px-20 border-t border-white/10 bg-[#0F1419]">
    <div class="grid md:grid-cols-3 gap-12">
      <div class="reveal-on-scroll">
        <i data-lucide="award" class="w-10 h-10 text-[#D43F3A] mb-6"></i>
        <h4 class="font-['Oswald'] text-xl font-bold uppercase mb-4">Certified Quality</h4>
        <p class="text-gray-500 text-sm leading-relaxed">Every vehicle undergoes a rigid 50-point inspection before
          purchase. We don't ship scrap unless explicitly requested for parts.</p>
      </div>
      <div class="reveal-on-scroll delay-100">
        <i data-lucide="lock" class="w-10 h-10 text-[#D43F3A] mb-6"></i>
        <h4 class="font-['Oswald'] text-xl font-bold uppercase mb-4">Secure Finances</h4>
        <p class="text-gray-500 text-sm leading-relaxed">All payments go through verified corporate accounts. We offer
          local deposit options in Lagos for ease of transaction.</p>
      </div>
      <div class="reveal-on-scroll delay-200">
        <i data-lucide="clock" class="w-10 h-10 text-[#D43F3A] mb-6"></i>
        <h4 class="font-['Oswald'] text-xl font-bold uppercase mb-4">Speed & Precision</h4>
        <p class="text-gray-500 text-sm leading-relaxed">We utilize direct RoRo lines with minimal stops. Your vehicle
          gets to Tincan faster, reducing demurrage risks.</p>
      </div>
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

  <!-- Quote Modal -->
  <div id="quote-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="toggleModal()"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-[#141414] border border-white/10 p-8 shadow-2xl">
      <div class="flex justify-between items-center mb-8">
        <h3 class="font-['Oswald'] text-2xl font-bold text-white uppercase">Contact Us</h3>
        <button onclick="toggleModal()" class="text-white/50 hover:text-white">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>
      <form class="space-y-6">
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Full Name</label>
          <input type="text" class="input-field" placeholder="ENTER YOUR NAME">
        </div>
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Email Address</label>
          <input type="email" class="input-field" placeholder="ENTER YOUR EMAIL">
        </div>
        <button type="button" class="w-full btn-primary" onclick="toggleModal()">
          Send Message
        </button>
      </form>
    </div>
  </div>

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

  <script src="forms.js"></script>
  <script src="script.js"></script>
</body>

</html>
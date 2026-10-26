<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact — Mapleby Inc.</title>

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

  <!-- Layout -->
  <div class="min-h-screen flex flex-col md:flex-row pt-20">

    <!-- Info Column -->
    <div
      class="w-full md:w-1/2 p-6 md:p-20 flex flex-col justify-center border-b md:border-b-0 md:border-r border-white/10">
      <header class="mb-12">
        <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block reveal-on-scroll">/// Global
          Network</span>
        <h1 class="font-['Oswald'] text-6xl font-bold uppercase leading-none mb-6 reveal-on-scroll delay-100">
          Get in <span class="text-outline-w">Touch</span>
        </h1>
        <p class="text-gray-400 reveal-on-scroll delay-200">
          Reach out to our teams in Canada or Nigeria directly. We are available 24/6.
        </p>
      </header>

      <div class="space-y-12">
        <div class="reveal-on-scroll delay-300">
          <div class="flex items-center gap-3 mb-4">
            <img src="https://flagcdn.com/w20/ca.png" alt="Canada">
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase">Toronto HQ</h3>
          </div>
          <ul class="space-y-2 text-sm text-gray-400 font-mono tracking-wide">
            <li>96 Millwick Dr, Toronto, ON</li>
            <li>+1 (416) 555-0199</li>
            <li>Mon-Fri: 9am - 6pm EST</li>
          </ul>
        </div>

        <div class="reveal-on-scroll delay-400">
          <div class="flex items-center gap-3 mb-4">
            <img src="https://flagcdn.com/w20/ng.png" alt="Nigeria">
            <h3 class="font-['Oswald'] text-2xl font-bold uppercase">Lagos Office</h3>
          </div>
          <ul class="space-y-2 text-sm text-gray-400 font-mono tracking-wide">
            <li>15 Admiralty Way, Lekki Phase 1, Lagos</li>
            <li>+234 800 123 4567</li>
            <li>Mon-Sat: 9am - 6pm WAT</li>
          </ul>
        </div>
      </div>

      <div class="mt-12 pt-12 border-t border-white/10 reveal-on-scroll delay-500">
        <a href="mailto:export@mapleby.com"
          class="text-2xl font-['Oswald'] hover:text-[#D43F3A] transition-colors">export@mapleby.com</a>
      </div>
    </div>

    <!-- Form Column -->
    <div class="w-full md:w-1/2 p-6 md:p-20 bg-[#0F1419] flex flex-col justify-center">
      <form class="max-w-md w-full mx-auto space-y-8 reveal-on-scroll">
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Full Name</label>
          <input type="text" class="input-field" placeholder="ENTER YOUR NAME">
        </div>
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Email Address</label>
          <input type="email" class="input-field" placeholder="ENTER YOUR EMAIL">
        </div>
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Subject</label>
          <select class="input-field bg-[#0F1419]">
            <option>General Inquiry</option>
            <option>Rate Request</option>
            <option>Partnership</option>
            <option>Track Shipment</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="font-mono text-xs uppercase text-white/50">Message</label>
          <textarea class="input-field h-32" placeholder="HOW CAN WE HELP?"></textarea>
        </div>
        <button type="button" class="w-full btn-primary py-4">
          Send Transmission
        </button>
      </form>
    </div>

  </div>

  <!-- Quote Modal -->
  <div id="quote-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="toggleModal()"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-[#141414] border border-white/10 p-8 shadow-2xl">
      <div class="flex justify-between items-center mb-8">
        <h3 class="font-['Oswald'] text-2xl font-bold text-white uppercase">Get Pricing</h3>
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
          Submit
        </button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>
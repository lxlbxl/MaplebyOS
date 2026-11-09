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

  <link rel="stylesheet" href="/website/styles.css">
    @include('website.partials.head')
</head>

<body class="bg-[#0A0F14] text-white overflow-x-hidden selection:bg-[#D43F3A] selection:text-white font-['Archivo']">

    @include('website.partials.nav')


  <!-- Layout -->
  <div class="min-h-screen flex flex-col md:flex-row pt-20">

    <!-- Info Column -->
    <div
      class="w-full md:w-1/2 p-6 md:p-20 flex flex-col justify-center border-b md:border-b-0 md:border-r border-white/10">
      <header id="main" class="mb-12">
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
        <a href="mailto:export@maplebyinc.com"
          class="text-2xl font-['Oswald'] hover:text-[#D43F3A] transition-colors">export@maplebyinc.com</a>
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

  <script src="/website/script.js"></script>
    @include('website.partials.footer')
</body>

</html>
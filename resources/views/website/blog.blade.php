<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News — Mapleby Inc.</title>

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
            <a href="services.html" class="hover:text-[#D43F3A] transition-colors">Services</a>
            <a href="inventory.html" class="hover:text-[#D43F3A] transition-colors">Inventory</a>
            <a href="process.html" class="hover:text-[#D43F3A] transition-colors">Process</a>
            <a href="about.html" class="hover:text-[#D43F3A] transition-colors">About</a>
            <a href="contact.html" class="hover:text-[#D43F3A] transition-colors">Contact</a>
        </div>
        <button onclick="toggleModal()" class="hidden md:block btn-primary text-xs">Get Quote</button>
        <button class="md:hidden text-white" onclick="toggleMobileMenu()"><i data-lucide="menu"
                class="w-8 h-8"></i></button>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="fixed inset-0 bg-[#0A0F14] z-40 translate-x-full transition-transform duration-500 flex flex-col justify-center px-8 border-l border-white/10">
        <button onclick="toggleMobileMenu()" class="absolute top-6 right-6"><i data-lucide="x"
                class="w-8 h-8"></i></button>
        <div class="flex flex-col gap-6 font-['Oswald'] text-4xl uppercase">
            <a href="index.html" class="hover:text-[#D43F3A]">Home</a>
            <a href="services.html" class="hover:text-[#D43F3A]">Services</a>
            <a href="inventory.html" class="hover:text-[#D43F3A]">Inventory</a>
            <a href="process.html" class="hover:text-[#D43F3A]">Process</a>
            <a href="contact.html" class="hover:text-[#D43F3A]">Contact</a>
        </div>
    </div>

    <!-- Header -->
    <header class="pt-40 pb-20 px-6 md:px-20 border-b border-white/10 maple-grid">
        <div class="max-w-4xl">
            <span class="text-[#D43F3A] font-mono text-sm uppercase tracking-widest mb-4 block reveal-on-scroll">///
                Field Intel</span>
            <h1
                class="font-['Oswald'] text-6xl md:text-8xl font-bold uppercase leading-none mb-8 reveal-on-scroll delay-100">
                Market <span class="text-outline-w">Updates</span>
            </h1>
        </div>
    </header>

    <!-- Grid -->
    <section class="py-24 px-6 md:px-20">
        <div class="grid md:grid-cols-3 gap-12">

            <!-- Post 1 -->
            <a href="blog-post-1.html" class="group block reveal-on-scroll">
                <div class="aspect-[4/3] overflow-hidden mb-6 border border-white/10 relative">
                    <img src="https://images.unsplash.com/photo-1533473359331-0135ef1bcfb0?q=80&w=1000&auto=format&fit=crop"
                        class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700 grayscale group-hover:grayscale-0">
                    <div
                        class="absolute top-4 left-4 bg-[#D43F3A] text-white text-[10px] font-bold uppercase px-3 py-1">
                        Review</div>
                </div>
                <div class="space-y-3">
                    <span class="font-mono text-xs text-gray-500 uppercase">Jan 28, 2026</span>
                    <h3
                        class="font-['Oswald'] text-2xl font-bold uppercase leading-tight group-hover:text-[#D43F3A] transition-colors">
                        Top 5 SUVs for Nigerian Roads</h3>
                    <p class="text-gray-400 text-sm line-clamp-2">Why the Lexus RX and Toyota Highlander continue to
                        dominate the Lagos-Ibadan expressway.</p>
                </div>
            </a>

            <!-- Post 2 -->
            <a href="blog-post-2.html" class="group block reveal-on-scroll delay-100">
                <div class="aspect-[4/3] overflow-hidden mb-6 border border-white/10 relative">
                    <img src="https://images.unsplash.com/photo-1616401784845-180882ba9ba8?q=80&w=1000&auto=format&fit=crop"
                        class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700 grayscale group-hover:grayscale-0">
                    <div
                        class="absolute top-4 left-4 bg-[#D43F3A] text-white text-[10px] font-bold uppercase px-3 py-1">
                        Logistics</div>
                </div>
                <div class="space-y-3">
                    <span class="font-mono text-xs text-gray-500 uppercase">Jan 15, 2026</span>
                    <h3
                        class="font-['Oswald'] text-2xl font-bold uppercase leading-tight group-hover:text-[#D43F3A] transition-colors">
                        Understanding Clearing Costs</h3>
                    <p class="text-gray-400 text-sm line-clamp-2">A breakdown of duty calculations and how the 2026
                        fiscal policy affects vehicle imports.</p>
                </div>
            </a>

            <!-- Post 3 -->
            <a href="blog-post-3.html" class="group block reveal-on-scroll delay-200">
                <div class="aspect-[4/3] overflow-hidden mb-6 border border-white/10 relative">
                    <img src="https://images.unsplash.com/photo-1580674684081-7617fbf3d745?q=80&w=1000&auto=format&fit=crop"
                        class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700 grayscale group-hover:grayscale-0">
                    <div
                        class="absolute top-4 left-4 bg-[#D43F3A] text-white text-[10px] font-bold uppercase px-3 py-1">
                        Insight</div>
                </div>
                <div class="space-y-3">
                    <span class="font-mono text-xs text-gray-500 uppercase">Jan 02, 2026</span>
                    <h3
                        class="font-['Oswald'] text-2xl font-bold uppercase leading-tight group-hover:text-[#D43F3A] transition-colors">
                        Why Buy From Canada?</h3>
                    <p class="text-gray-400 text-sm line-clamp-2">Comparing KM to miles, rust-proofing, and maintenance
                        standards vs US salvage titles.</p>
                </div>
            </a>

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
                <h3 class="font-['Oswald'] text-2xl font-bold text-white uppercase">Subscribe</h3>
                <button onclick="toggleModal()" class="text-white/50 hover:text-white">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form class="space-y-6">
                <div class="space-y-2">
                    <label class="font-mono text-xs uppercase text-white/50">Email Address</label>
                    <input type="email" class="input-field" placeholder="ENTER YOUR EMAIL">
                </div>
                <button type="button" class="w-full btn-primary" onclick="toggleModal()">
                    Join Newsletter
                </button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
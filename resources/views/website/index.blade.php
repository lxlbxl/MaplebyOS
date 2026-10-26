<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPLEBY — Transcontinental Logistics | Canada to Nigeria</title>
    <meta name="description"
        content="Direct car shipping and procurement from Canada to Nigeria. Professional vehicle export and freight forwarding.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@400;700;900&family=IBM+Plex+Mono:wght@300;400;500&family=Hanken+Grotesk:wght@300;400;600&display=swap"
        rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <style>
        :root {
            --maple-crimson: #D72638;
            --void: #050505;
            --steel: #1A1C1E;
            --nickel: #A0A0A0;
            --blueprint-line: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: var(--void);
            color: white;
            overflow-x: hidden;
        }

        .font-tech {
            font-family: 'IBM Plex Mono', monospace;
        }

        .font-heading {
            font-family: 'Big Shoulders Display', sans-serif;
        }

        /* Blueprint Grid Background */
        .blueprint-bg {
            background-image:
                linear-gradient(var(--blueprint-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--blueprint-line) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Glass & Steel Panels */
        .panel-steel {
            background: rgba(15, 17, 20, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .panel-steel::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            width: 10px;
            height: 10px;
            border-top: 2px solid var(--maple-crimson);
            border-left: 2px solid var(--maple-crimson);
        }

        /* Industrial Buttons */
        .btn-command {
            position: relative;
            background: var(--maple-crimson);
            color: white;
            font-family: 'Big Shoulders Display', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            padding: 1rem 2.5rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            clip-path: polygon(10% 0, 100% 0, 100% 70%, 90% 100%, 0 100%, 0 30%);
        }

        .btn-command:hover {
            transform: scale(1.05);
            filter: brightness(1.2);
            box-shadow: 0 0 30px rgba(215, 38, 56, 0.4);
        }

        .btn-outline-steel {
            border: 1px solid var(--nickel);
            font-family: 'IBM Plex Mono', monospace;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            padding: 1rem 2rem;
            transition: all 0.3s ease;
        }

        .btn-outline-steel:hover {
            background: white;
            color: black;
        }

        /* Scanline FX */
        .scanline {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgba(215, 38, 56, 0.1);
            z-index: 100;
            pointer-events: none;
            animation: scan 6s linear infinite;
        }

        @keyframes scan {
            0% {
                transform: translateY(-100vh);
            }

            100% {
                transform: translateY(100vh);
            }
        }

        .text-reveal {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
        }

        .parallax-title {
            -webkit-text-stroke: 2px rgba(255, 255, 255, 0.8);
            color: transparent;
        }

        /* Scroll Telemetry */
        #telemetry {
            position: fixed;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 80;
            height: 200px;
            width: 2px;
            background: rgba(255, 255, 255, 0.1);
        }

        #telemetry-bar {
            width: 100%;
            height: 0%;
            background: var(--maple-crimson);
        }
    </style>
</head>

<body class="selection:bg-[var(--maple-crimson)] selection:text-white">

    <div class="scanline"></div>
    <div class="fixed inset-0 blueprint-bg opacity-30 pointer-events-none z-0"></div>

    <!-- Scroll Telemetry UI -->
    <div id="telemetry" class="hidden lg:block">
        <div
            class="absolute -top-8 left-1/2 -translate-x-1/2 font-tech text-[10px] text-gray-500 uppercase rotate-90 origin-center whitespace-nowrap">
            Route Progress</div>
        <div id="telemetry-bar"></div>
        <div id="telemetry-pct"
            class="absolute -bottom-8 left-1/2 -translate-x-1/2 font-tech text-[10px] text-[var(--maple-crimson)]">000
        </div>
    </div>

    <!-- Navigation -->
    <nav id="navbar"
        class="fixed w-full z-[90] px-6 md:px-16 py-10 flex justify-between items-start transition-all duration-700">
        <div class="flex flex-col">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-2 h-2 bg-[var(--maple-crimson)]"></div>
                <span class="font-heading text-4xl leading-none tracking-tighter font-black">MAPLEBY</span>
            </div>
            <span class="font-tech text-[9px] tracking-[0.5em] text-[var(--maple-crimson)] uppercase ml-5">Global
                Procurement</span>
        </div>

        <div class="hidden lg:flex gap-16 items-center">
            <div class="flex gap-12 font-tech text-[10px] uppercase tracking-widest text-gray-400">
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
        <div class="flex flex-col gap-8 font-heading text-5xl uppercase text-white">
            <a href="index.html" class="hover:text-[var(--maple-crimson)] transition-colors">Home</a>
            <a href="process.html" class="hover:text-[var(--maple-crimson)] transition-colors">01. Logistics</a>
            <a href="inventory.html" class="hover:text-[var(--maple-crimson)] transition-colors">02. Inventory</a>
            <a href="services.html" class="hover:text-[var(--maple-crimson)] transition-colors">03. Clearance</a>
            <a href="about.html" class="hover:text-[var(--maple-crimson)] transition-colors">04. About</a>
            <a href="contact.html" class="hover:text-[var(--maple-crimson)] transition-colors">05. Network</a>
            <a href="/app/login" class="text-[var(--maple-crimson)] mt-4 text-3xl">Staff Login</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center px-6 md:px-16 pt-32 overflow-hidden">
        <!-- Main Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80"
                class="w-full h-full object-cover opacity-20 hero-img" alt="Logistics Vessel">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[var(--void)] to-[var(--void)]"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[var(--void)] via-transparent to-transparent"></div>
        </div>

        <div class="relative z-10 w-full grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-8">
                <div class="mb-10 flex items-center gap-6 stagger-reveal">
                    <span class="font-tech text-xs tracking-[0.6em] uppercase text-gray-500">Established MMXXVI</span>
                    <div class="h-px w-20 bg-white/10"></div>
                </div>

                <h1
                    class="font-heading text-8xl md:text-[11rem] leading-[0.8] font-black uppercase mb-12 stagger-reveal">
                    Canadian <br>
                    <span class="text-[var(--maple-crimson)]">Sourcing.</span> <br>
                    <span class="parallax-title">Lagos Bound.</span>
                </h1>

                <div class="flex flex-col md:flex-row gap-12 items-start stagger-reveal">
                    <div class="max-w-md">
                        <p class="text-gray-400 text-lg leading-relaxed font-light mb-8">
                            The definitive bridge for automotive procurement and freight. We connect Canadian auctions
                            directly to Lagos terminals with uncompromising security and real-time telemetry.
                        </p>
                        <div class="flex gap-6">
                            <button class="btn-command text-xl px-12" onclick="openTerminal('LOGISTICS')">Start
                                Shipment</button>
                            <button class="btn-outline-steel" onclick="openTerminal('PROCUREMENT')">View
                                Inventory</button>
                        </div>
                    </div>

                    <!-- Tech Specs -->
                    <div
                        class="panel-steel p-6 flex flex-col gap-6 hidden md:flex border-l-4 border-l-[var(--maple-crimson)]">
                        <div>
                            <span class="block font-tech text-[10px] uppercase text-gray-500 mb-1">Port Status</span>
                            <span class="block font-heading text-4xl text-emerald-400">ACTIVE</span>
                        </div>
                        <div>
                            <span class="block font-tech text-[10px] uppercase text-gray-500 mb-1">Transit
                                Latency</span>
                            <span class="block font-heading text-4xl">22-28D</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 hidden lg:block">
                <div class="relative">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 w-px h-64 bg-white/10"></div>
                    <div class="space-y-12">
                        <div class="stagger-reveal">
                            <span
                                class="block font-tech text-[10px] text-[var(--maple-crimson)] uppercase mb-4 tracking-[0.3em]">Origin
                                Terminal</span>
                            <p class="font-heading text-5xl leading-none">TORONTO / MONTREAL <br> CANADA</p>
                        </div>
                        <div class="stagger-reveal">
                            <span
                                class="block font-tech text-[10px] text-[var(--maple-crimson)] uppercase mb-4 tracking-[0.3em]">Destination
                                Hub</span>
                            <p class="font-heading text-5xl leading-none">TINCAN / APAPA <br> LAGOS, NIGERIA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operations / Services Section -->
    <section class="py-40 px-6 md:px-16 relative">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end mb-32 gap-12">
                <div class="reveal">
                    <span
                        class="font-tech text-[11px] text-[var(--maple-crimson)] uppercase tracking-[0.5em] block mb-4">Service
                        Modules</span>
                    <h2 class="font-heading text-7xl md:text-9xl leading-none uppercase">Core <br>Operations</h2>
                </div>
                <div class="max-w-md reveal">
                    <p class="text-gray-400 text-lg border-l-2 border-white/10 pl-8">
                        [ SYSTEM-NOTE ]: All operations are monitored via GPS telemetry and verified through our
                        Toronto-based dispatch center. We ensure absolute chain-of-custody from the hammer drop to
                        terminal release.
                    </p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-0">
                <!-- Procurement Module -->
                <div
                    class="panel-steel p-16 group hover:z-20 transition-all duration-500 border-r-0 last:border-r hover:border-[var(--maple-crimson)] reveal">
                    <div class="mb-16">
                        <div
                            class="w-16 h-16 bg-white/5 flex items-center justify-center mb-8 group-hover:bg-[var(--maple-crimson)] transition-colors duration-500">
                            <i data-lucide="shopping-cart" class="w-8 h-8"></i>
                        </div>
                        <span class="font-tech text-xs text-gray-500 uppercase tracking-widest">Tech-ID: 01-PROC</span>
                    </div>
                    <h3 class="font-heading text-5xl uppercase mb-6 group-hover:translate-x-2 transition-transform">
                        Strategic Procurement</h3>
                    <p class="text-gray-400 leading-relaxed mb-10 text-lg">
                        Direct access to Canadian Dealer-only auctions. We execute bids, verify titles, and conduct
                        forensic inspections. Total transparency on every transaction.
                    </p>
                    <div
                        class="pt-8 border-t border-white/5 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="font-tech text-xs uppercase tracking-widest text-[var(--maple-crimson)]"
                            onclick="openTerminal('PROCUREMENT')">Initialize
                            Protocol</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </div>

                <!-- Shipping Module -->
                <div
                    class="panel-steel p-16 group hover:z-20 transition-all duration-500 border-r-0 last:border-r hover:border-[var(--maple-crimson)] reveal">
                    <div class="mb-16">
                        <div
                            class="w-16 h-16 bg-white/5 flex items-center justify-center mb-8 group-hover:bg-[var(--maple-crimson)] transition-colors duration-500">
                            <i data-lucide="ship" class="w-8 h-8"></i>
                        </div>
                        <span class="font-tech text-xs text-gray-500 uppercase tracking-widest">Tech-ID: 02-FRGT</span>
                    </div>
                    <h3 class="font-heading text-5xl uppercase mb-6 group-hover:translate-x-2 transition-transform">
                        Ocean Freight</h3>
                    <p class="text-gray-400 leading-relaxed mb-10 text-lg">
                        RORO and Containerized cargo. Weekly departures from Montreal and Toronto ports to Tincan
                        Terminal. Real-time vessel location tracking.
                    </p>
                    <div
                        class="pt-8 border-t border-white/5 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="font-tech text-[10px] uppercase tracking-widest text-[var(--maple-crimson)]"
                            onclick="openTerminal('LOGISTICS')">Vessel
                            Telemetry</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </div>

                <!-- Customs Module -->
                <div
                    class="panel-steel p-16 group hover:z-20 transition-all duration-500 last:border-r hover:border-[var(--maple-crimson)] reveal">
                    <div class="mb-16">
                        <div
                            class="w-16 h-16 bg-white/5 flex items-center justify-center mb-8 group-hover:bg-[var(--maple-crimson)] transition-colors duration-500">
                            <i data-lucide="file-check-2" class="w-8 h-8"></i>
                        </div>
                        <span class="font-tech text-xs text-gray-500 uppercase tracking-widest">Tech-ID: 03-CLR</span>
                    </div>
                    <h3 class="font-heading text-5xl uppercase mb-6 group-hover:translate-x-2 transition-transform">
                        Customs Concierge</h3>
                    <p class="text-gray-400 leading-relaxed mb-10 text-lg">
                        Precision valuation and duty processing for Nigerian Customs. Elimination of hidden fees and
                        demurrage risks. We navigate the bureaucracy so you don't have to.
                    </p>
                    <div
                        class="pt-8 border-t border-white/5 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="font-tech text-xs uppercase tracking-widest text-[var(--maple-crimson)]"
                            onclick="openTerminal('CLEARANCE')">Clearing
                            Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic Metrics -->
    <section class="py-20 border-y border-white/5 bg-black/50">
        <div class="px-6 md:px-16 grid grid-cols-2 md:grid-cols-4 gap-12">
            <div class="reveal">
                <span class="font-tech text-xs text-gray-400 uppercase mb-4 block tracking-[0.3em]">Units
                    Delivered</span>
                <span class="font-heading text-7xl">1,250+</span>
            </div>
            <div class="reveal">
                <span class="font-tech text-xs text-gray-400 uppercase mb-4 block tracking-[0.3em]">Secure
                    Release</span>
                <span class="font-heading text-7xl text-[var(--maple-crimson)]">100%</span>
            </div>
            <div class="reveal">
                <span class="font-tech text-xs text-gray-400 uppercase mb-4 block tracking-[0.3em]">Active
                    Vessels</span>
                <span class="font-heading text-7xl">04</span>
            </div>
            <div class="reveal">
                <span class="font-tech text-xs text-gray-400 uppercase mb-4 block tracking-[0.3em]">Terminal
                    Nodes</span>
                <span class="font-heading text-7xl">03</span>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-40 px-6 relative overflow-hidden">
        <div class="max-w-5xl mx-auto text-center reveal">
            <h2 class="font-heading text-7xl md:text-[12rem] leading-[0.8] uppercase font-black mb-16">
                Secure Your <br> <span class="parallax-title">Manifest</span>
            </h2>
            <div class="flex flex-col items-center gap-12">
                <button class="btn-command text-3xl px-20 py-10" onclick="openTerminal('LOGISTICS')">Initialize
                    Request</button>
                <div class="flex items-center gap-6 font-tech text-[11px] uppercase tracking-[0.4em] text-gray-500">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Toronto Dispatch Live
                    </span>
                    <span class="w-px h-4 bg-white/10"></span>
                    <span>Lagos Ops Standing By</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#0A0A0A] border-t border-white/5 pt-32 pb-12 px-6 md:px-16">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-4 gap-20 mb-32">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-3 h-3 bg-[var(--maple-crimson)]"></div>
                        <span class="font-heading text-4xl tracking-tighter">MAPLEBY LOGISTICS</span>
                    </div>
                    <p class="text-gray-400 text-lg max-w-md mb-12 leading-relaxed">
                        The architectural backbone of North American-West African automotive supply chains. Redefining
                        vehicle exports through technology, transparency, and trust.
                    </p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-12 h-12 panel-steel flex items-center justify-center hover:bg-[var(--maple-crimson)] transition-colors"><i
                                data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#"
                            class="w-12 h-12 panel-steel flex items-center justify-center hover:bg-[var(--maple-crimson)] transition-colors"><i
                                data-lucide="linkedin" class="w-5 h-5"></i></a>
                        <a href="#"
                            class="w-12 h-12 panel-steel flex items-center justify-center hover:bg-[var(--maple-crimson)] transition-colors"><i
                                data-lucide="twitter" class="w-5 h-5"></i></a>
                    </div>
                </div>

                <div class="space-y-10">
                    <h4 class="font-heading text-2xl uppercase tracking-widest text-white">Operations</h4>
                    <ul class="space-y-4 font-tech text-[10px] uppercase text-gray-600 tracking-widest">
                        <li><a href="tracking.html" class="hover:text-white transition-colors">Vessel Tracking</a></li>
                        <li><a href="schedules.html" class="hover:text-white transition-colors">Port Schedules</a></li>
                        <li><a href="valuation.html" class="hover:text-white transition-colors">Customs Valuation</a>
                        </li>
                        <li><a href="inventory.html" class="hover:text-white transition-colors">Fleet Inventory</a></li>
                    </ul>
                </div>

                <div class="space-y-10">
                    <h4 class="font-heading text-2xl uppercase tracking-widest text-white">Contact</h4>
                    <div class="font-tech text-[10px] uppercase text-gray-600 tracking-widest leading-loose">
                        HQ: 96 MILLWICK DR, TORONTO, ON<br>
                        OPS: TINCAN ISLAND, LAGOS, NG<br><br>
                        PH: +1 (416) 555-0199<br>
                        EM: <span class="text-white">DISPATCH@MAPLEBY.COM</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-8 pt-12 border-t border-white/5">
                <span class="font-tech text-[9px] uppercase tracking-[0.5em] text-gray-700">
                    &copy; MMXXVI MAPLEBY INC. [ VER 3.0.1 ]
                </span>
                <div class="flex gap-10 font-tech text-[9px] uppercase tracking-[0.5em] text-gray-700">
                    <a href="security.html" class="hover:text-white transition-colors">Security Protocol</a>
                    <a href="terms.html" class="hover:text-white transition-colors">Terms of Carriage</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Dynamic Terminal Modal -->
    <div id="terminal-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeTerminal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl px-6">
            <div id="terminal-panel" class="panel-steel border-t-[4px] border-t-[var(--maple-crimson)] overflow-hidden">
                <!-- Terminal Header -->
                <div class="bg-white/5 p-6 flex justify-between items-center border-b border-white/5">
                    <div>
                        <span id="terminal-subtitle"
                            class="font-tech text-[10px] text-[var(--maple-crimson)] uppercase tracking-[0.3em] block mb-1">System</span>
                        <h3 id="terminal-title" class="font-heading text-3xl uppercase leading-none">Terminal</h3>
                    </div>
                    <button onclick="closeTerminal()" class="text-gray-500 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Progress Line -->
                <div class="w-full h-1 bg-white/5">
                    <div id="terminal-progress" class="h-full bg-[var(--maple-crimson)] transition-all duration-500"
                        style="width: 0%"></div>
                </div>

                <!-- Dynamic Content Area -->
                <div id="terminal-content" class="p-8 md:p-12 min-h-[400px] flex flex-col justify-center">
                    <!-- Steps injected by forms.js -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="forms.js"></script>
    <script src="script.js"></script>
    <script>
        // Initialize Icons
        lucide.createIcons();

        // GSAP Animations
        window.addEventListener('load', () => {
            gsap.registerPlugin(ScrollTrigger);

            // Initial Hero Stagger
            gsap.to('.stagger-reveal', {
                opacity: 1,
                y: 0,
                duration: 1.4,
                stagger: 0.1,
                ease: "expo.out"
            });

            // Parallax Hero Img
            gsap.to('.hero-img', {
                scrollTrigger: {
                    trigger: 'body',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true
                },
                y: 300,
                scale: 1.2
            });

            // Reveal Sections
            document.querySelectorAll('.reveal').forEach((el) => {
                gsap.from(el, {
                    scrollTrigger: {
                        trigger: el,
                        start: 'top 85%',
                    },
                    opacity: 0,
                    y: 60,
                    duration: 1.2,
                    ease: "power4.out"
                });
            });

            // Telemetry Bar Tracking
            window.addEventListener('scroll', () => {
                const scrollPct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                document.getElementById('telemetry-bar').style.height = `${scrollPct}%`;
                document.getElementById('telemetry-pct').innerText = String(Math.floor(scrollPct)).padStart(3, '0');
            });
        });

        // Navbar Scroll FX
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('bg-black/80', 'backdrop-blur-xl', 'py-6', 'border-b', 'border-white/5');
                nav.classList.remove('py-10');
            } else {
                nav.classList.remove('bg-black/80', 'backdrop-blur-xl', 'py-6', 'border-b', 'border-white/5');
                nav.classList.add('py-10');
            }
        });

        // Modal Logic
        function toggleModal() {
            const modal = document.getElementById('quote-modal');
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden')) {
                gsap.from('#quote-modal .panel-steel', {
                    y: 100,
                    opacity: 0,
                    duration: 0.8,
                    ease: "expo.out"
                });
            }
        }

        // Mobile Menu Placeholder

    </script>
</body>

</html>
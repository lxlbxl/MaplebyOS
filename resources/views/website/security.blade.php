<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security — MAPLEBY</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@400;700;900&family=IBM+Plex+Mono:wght@300;400;500&family=Hanken+Grotesk:wght@300;400;600&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --maple-crimson: #D72638;
            --void: #050505;
            --blueprint-line: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: var(--void);
            color: white;
        }

        .font-tech {
            font-family: 'IBM Plex Mono', monospace;
        }

        .font-heading {
            font-family: 'Big Shoulders Display', sans-serif;
        }

        .blueprint-bg {
            background-image: linear-gradient(var(--blueprint-line) 1px, transparent 1px), linear-gradient(90deg, var(--blueprint-line) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .panel-steel {
            background: rgba(15, 17, 20, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="selection:bg-[var(--maple-crimson)] selection:text-white min-h-screen flex flex-col">
    <div class="fixed inset-0 blueprint-bg opacity-30 pointer-events-none z-0"></div>

    <nav
        class="fixed w-full z-50 px-6 md:px-16 py-8 flex justify-between items-center bg-black/80 backdrop-blur-xl border-b border-white/5">
        <a href="index.html" class="flex items-center gap-3">
            <div class="w-2 h-2 bg-[var(--maple-crimson)]"></div>
            <span class="font-heading text-3xl tracking-tighter font-black">MAPLEBY</span>
        </a>
        <div class="hidden lg:flex gap-12 font-tech text-[10px] uppercase tracking-widest text-gray-400">
            <a href="process.html" class="hover:text-white transition-colors">Logistics</a>
            <a href="inventory.html" class="hover:text-white transition-colors">Inventory</a>
            <a href="services.html" class="hover:text-white transition-colors">Clearance</a>
            <a href="contact.html" class="hover:text-white transition-colors">Network</a>
        </div>
    </nav>

    <main class="flex-grow pt-40 px-6 md:px-16 relative z-10 max-w-4xl mx-auto">
        <h1 class="font-heading text-6xl md:text-8xl uppercase font-black mb-12">Security <span
                class="text-[var(--maple-crimson)]">Protocol</span></h1>

        <div class="space-y-8 text-gray-400 leading-relaxed text-lg">
            <div class="panel-steel p-8">
                <h3 class="font-heading text-3xl text-white mb-4">01. Chain of Custody</h3>
                <p>From the moment of acquisition at Canadian auctions, every asset is tagged with a unique digital
                    identifier. Physical condition is documented via 4K video inspection before leaving the lot.</p>
            </div>

            <div class="panel-steel p-8">
                <h3 class="font-heading text-3xl text-white mb-4">02. Terminal Surveillance</h3>
                <p>Our staging yards in Toronto and Montreal are monitored 24/7. No vehicle is loaded onto a vessel
                    without a triple-check of the VIN and manifest match.</p>
            </div>

            <div class="panel-steel p-8">
                <h3 class="font-heading text-3xl text-white mb-4">03. Secure Release</h3>
                <p>Upon arrival in Lagos, release codes are issued only to the verified consignee via encrypted channel.
                    We represent the gold standard in anti-fraud logistics.</p>
            </div>
        </div>
    </main>

    <footer
        class="mt-20 py-8 border-t border-white/5 text-center font-tech text-[10px] uppercase text-gray-600 tracking-widest relative z-10">
        &copy; 2026 MAPLEBY LOGISTICS
    </footer>
    <script>lucide.createIcons();</script>
</body>

</html>
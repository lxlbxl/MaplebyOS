<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules — MAPLEBY</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@400;700;900&family=IBM+Plex+Mono:wght@300;400;500&family=Hanken+Grotesk:wght@300;400;600&display=swap"
        rel="stylesheet">
    <!-- Tailwind -->
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

        .btn-command {
            background: var(--maple-crimson);
            color: white;
            font-family: 'Big Shoulders Display', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            padding: 1rem 2.5rem;
            clip-path: polygon(10% 0, 100% 0, 100% 70%, 90% 100%, 0 100%, 0 30%);
            transition: all 0.3s;
        }

        .btn-command:hover {
            transform: scale(1.02);
        }
    </style>
</head>

<body class="selection:bg-[var(--maple-crimson)] selection:text-white min-h-screen flex flex-col">
    <div class="fixed inset-0 blueprint-bg opacity-30 pointer-events-none z-0"></div>

    <!-- Nav -->
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

    <!-- Content -->
    <main class="flex-grow pt-40 px-6 md:px-16 relative z-10 flex flex-col justify-center items-center">
        <div class="max-w-5xl w-full">
            <h1 class="font-heading text-7xl md:text-8xl uppercase font-black mb-12 text-center">Departure <span
                    class="text-[var(--maple-crimson)]">Schedule</span></h1>

            <div class="panel-steel overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="border-b border-white/10 font-tech text-[10px] uppercase text-gray-500 tracking-widest">
                            <th class="p-6">Vessel ID</th>
                            <th class="p-6">Line</th>
                            <th class="p-6">Departs (YYZ/YUL)</th>
                            <th class="p-6">Arrives (LOS)</th>
                            <th class="p-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="font-mono text-sm">
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="p-6 text-[var(--maple-crimson)]">GRANDE-AFRICA</td>
                            <td class="p-6">Grimaldi</td>
                            <td class="p-6">Feb 02</td>
                            <td class="p-6">Feb 24</td>
                            <td class="p-6 text-emerald-500">Boarding</td>
                        </tr>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="p-6 text-white">SILVER-RAY</td>
                            <td class="p-6">Sallaum</td>
                            <td class="p-6">Feb 09</td>
                            <td class="p-6">Mar 03</td>
                            <td class="p-6 text-gray-400">Scheduled</td>
                        </tr>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="p-6 text-white">GLOVIS-STAR</td>
                            <td class="p-6">Glovis</td>
                            <td class="p-6">Feb 16</td>
                            <td class="p-6">Mar 12</td>
                            <td class="p-6 text-gray-400">Scheduled</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 text-center">
                <button class="btn-command">Download Full Manifest PDF</button>
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
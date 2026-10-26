<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vessel Tracking — MAPLEBY</title>
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
            --steel: #1A1C1E;
            --nickel: #A0A0A0;
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
            filter: brightness(1.1);
        }

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
    </style>
</head>

<body class="selection:bg-[var(--maple-crimson)] selection:text-white min-h-screen flex flex-col">
    <div class="scanline"></div>
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
        <div class="max-w-3xl w-full">
            <h1 class="font-heading text-7xl md:text-9xl uppercase font-black mb-8 text-center">Vessel <span
                    class="text-[var(--maple-crimson)]">Telemetry</span></h1>
            <p class="text-center text-gray-400 text-lg mb-16 font-light max-w-xl mx-auto">
                Enter your Mapleby tracking ID (for example MAP26-10-023) or your car's full 17-character VIN to see where it is.
            </p>

            <div class="panel-steel p-10 border-l-4 border-l-[var(--maple-crimson)]">
                <form id="track-form" class="flex flex-col md:flex-row gap-4">
                    <input id="track-q" type="text" placeholder="TRACKING ID OR VIN" autocomplete="off" required minlength="6" maxlength="40"
                        value="{{ request('q') }}"
                        class="flex-grow bg-white/5 border border-white/10 px-6 py-4 font-tech text-sm text-white uppercase focus:border-[var(--maple-crimson)] outline-none">
                    <button class="btn-command" type="submit">TRACK</button>
                </form>
                <p id="track-error" class="hidden mt-6 font-tech text-xs text-[#D72638]"></p>

                <div id="track-result" class="hidden mt-8 pt-8 border-t border-white/5 space-y-6">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div>
                            <p class="font-tech text-[10px] uppercase tracking-widest text-gray-500">Vehicle</p>
                            <p id="r-vehicle" class="font-heading text-3xl uppercase"></p>
                            <p id="r-ids" class="font-tech text-xs text-gray-400"></p>
                        </div>
                        <div class="text-right">
                            <p class="font-tech text-[10px] uppercase tracking-widest text-gray-500">Status</p>
                            <p id="r-status" class="font-heading text-3xl uppercase text-[var(--maple-crimson)]"></p>
                        </div>
                    </div>
                    <ol id="r-steps" class="grid grid-cols-5 md:grid-cols-10 gap-1"></ol>
                    <div id="r-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 font-tech text-xs"></div>
                    <div>
                        <p class="font-tech text-[10px] uppercase tracking-widest text-gray-500 mb-2">History</p>
                        <ul id="r-timeline" class="space-y-1 font-tech text-xs text-gray-300"></ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer
        class="mt-20 py-8 border-t border-white/5 text-center font-tech text-[10px] uppercase text-gray-600 tracking-widest relative z-10">
        &copy; 2026 MAPLEBY LOGISTICS [ SYSTEM ID: TRX-001 ]
    </footer>

    <script>
        lucide.createIcons();
        (function () {
            const form = document.getElementById('track-form');
            const input = document.getElementById('track-q');
            const err = document.getElementById('track-error');
            const box = document.getElementById('track-result');
            const fmt = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            async function track(q) {
                err.classList.add('hidden');
                box.classList.add('hidden');
                try {
                    const res = await fetch('{{ route('website.tracking.lookup') }}?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    if (!res.ok || !data.found) {
                        err.textContent = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Not found.');
                        err.classList.remove('hidden');
                        return;
                    }
                    document.getElementById('r-vehicle').textContent = data.vehicle || 'Vehicle';
                    document.getElementById('r-ids').textContent = data.tracking_id + (data.vin_last6 ? ' · VIN …' + data.vin_last6 : '') + (data.destination ? ' · to ' + data.destination : '');
                    document.getElementById('r-status').textContent = data.status_label;
                    const reached = new Set(data.timeline.map((t) => t.status));
                    const current = data.steps.findIndex((s) => s.key === data.status);
                    document.getElementById('r-steps').innerHTML = data.steps.map((s, i) =>
                        `<li class="text-center"><div class="h-1.5 ${i <= current || reached.has(s.key) ? 'bg-[#D72638]' : 'bg-white/10'}"></div><span class="font-tech text-[9px] uppercase text-gray-500">${esc(s.label)}</span></li>`).join('');
                    const c = data.container;
                    document.getElementById('r-container').innerHTML = c ? [
                        ['Container', c.number], ['Shipping line', c.shipping_line], ['Loaded', fmt(c.loaded_on)], ['Sailed', fmt(c.sailed_on)],
                        ['ETA', fmt(c.eta)], ['Arrived', fmt(c.arrived_on)], ['Offloaded', fmt(c.offloaded_on)], ['Vessel', c.vessel || '—'],
                    ].map(([k, v]) => `<div><p class="text-[10px] uppercase tracking-widest text-gray-500">${k}</p><p class="text-white">${esc(v || '—')}</p></div>`).join('')
                        : '<p class="col-span-4 text-gray-400">Not loaded into a container yet.</p>';
                    document.getElementById('r-timeline').innerHTML = data.timeline.map((t) => `<li>${fmt(t.date)} — ${esc(t.label)}</li>`).join('') || '<li>No updates yet.</li>';
                    box.classList.remove('hidden');
                } catch (e) {
                    err.textContent = 'Tracking is unavailable right now. Please try again shortly.';
                    err.classList.remove('hidden');
                }
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const q = input.value.trim();
                history.replaceState(null, '', '?q=' + encodeURIComponent(q));
                track(q);
            });
            if (input.value.trim()) track(input.value.trim());
        })();
    </script>
</body>

</html>
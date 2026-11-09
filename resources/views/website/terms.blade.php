<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms — MAPLEBY</title>
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

        .panel-steel {
            background: rgba(15, 17, 20, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
    </style>
    @include('website.partials.head')
</head>

<body class="selection:bg-[var(--maple-crimson)] selection:text-white min-h-screen flex flex-col">
    @include('website.partials.nav')
    <main id="main" class="flex-grow pt-40 px-6 md:px-16 max-w-4xl mx-auto mb-20">
        <h1 class="font-heading text-6xl md:text-8xl uppercase font-black mb-12">Terms of <span
                class="text-[var(--maple-crimson)]">Carriage</span></h1>

        <div class="panel-steel p-10 font-mono text-xs text-gray-400 space-y-6 leading-relaxed">
            <p>1. DEFINITIONS: "Carrier" refers to Mapleby Inc. "Shipper" refers to the entity initiating the transport.
            </p>
            <p>2. LIABILITY: Carrier is not liable for minor cosmetic damage incurred during ocean transit unless
                premium insurance is purchased.</p>
            <p>3. PAYMENT: 70% deposit required upon vehicle purchase. Balance due upon Bill of Lading issuance.</p>
            <p>4. CUSTOMS: Consignee is responsible for all local duties unless "Door-to-Door" service is explicitly
                contracted.</p>
            <p>5. JURISDICTION: This agreement is governed by the laws of Ontario, Canada.</p>
        </div>
    </main>

    @include('website.partials.footer')
    <script>lucide.createIcons();</script>
</body>

</html>
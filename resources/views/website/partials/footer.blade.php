<footer class="mb-footer">
    <div class="mb-footer__leaf" aria-hidden="true">{!! file_get_contents(public_path('brand/leaf.svg')) !!}</div>
    <div class="mb-footer__grid">
        <div class="mb-footer__brand">
            <div class="mb-seal" aria-hidden="true">
                <svg class="mb-seal__ring" viewBox="0 0 200 200">
                    <defs><path id="mb-seal-path" d="M100,100 m-86,0 a86,86 0 1,1 172,0 a86,86 0 1,1 -172,0"/></defs>
                    <text><textPath href="#mb-seal-path" textLength="535">MAPLEBY INC &#8226; CANADA &#8594; NIGERIA &#8226; EST. MMXXVI &#8226;</textPath></text>
                </svg>
                <img src="/brand/badge-256.webp" width="128" height="128" alt="" loading="lazy">
            </div>
            <div>
                <p class="mb-footer__title">Mapleby Inc.</p>
                <p class="mb-footer__lede">The bridge between Canadian auctions and Lagos terminals. Vehicle procurement,
                    export and clearance, handled end to end.</p>
            </div>
        </div>

        <div>
            <h2 class="mb-footer__h">Operations</h2>
            <ul class="mb-footer__list">
                <li><a href="/shipment-request">Start a Shipment</a></li>
                <li><a href="/tracking">Vehicle Tracking</a></li>
                <li><a href="/schedules">Port Schedules</a></li>
                <li><a href="/valuation">Customs Valuation</a></li>
                <li><a href="/inventory">Fleet Inventory</a></li>
            </ul>
        </div>

        <div>
            <h2 class="mb-footer__h">Company</h2>
            <ul class="mb-footer__list">
                <li><a href="/about">About</a></li>
                <li><a href="/process">Our Process</a></li>
                <li><a href="/services">Services</a></li>
                <li><a href="/blog">News</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </div>

        <div>
            <h2 class="mb-footer__h">Contact</h2>
            <ul class="mb-footer__list mb-footer__list--plain">
                <li>96 Millwick Dr, Toronto, ON</li>
                <li>Tincan Island, Lagos, NG</li>
                <li><a href="tel:+14165550199">+1 (416) 555-0199</a></li>
                <li><a href="mailto:export@maplebyinc.com">export@maplebyinc.com</a></li>
                <li><a href="mailto:dispatch@maplebyinc.com">dispatch@maplebyinc.com</a></li>
            </ul>
        </div>
    </div>

    <div class="mb-footer__bar">
        <span>&copy; {{ date('Y') }} Mapleby Inc. All rights reserved.</span>
        <span class="mb-footer__legal">
            <a href="/security">Security Protocol</a>
            <a href="/terms">Terms of Carriage</a>
            <a href="/app/login">Staff Login</a>
        </span>
    </div>
</footer>
<script src="/website/brand.js?v={{ filemtime(public_path('website/brand.js')) }}" defer></script>

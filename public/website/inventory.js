
const API_BASE_URL = "http://localhost:8000/api/website";
// Note: Adjust BASE_URL if your API is on a different port.

let state = {
    page: 1,
    per_page: 9,
    make: '',
    year: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    search: '',
    isLoading: false
};

document.addEventListener('DOMContentLoaded', () => {
    initInventory();
});

function initInventory() {
    // Bind Filter Events
    document.getElementById('filter-make')?.addEventListener('change', (e) => updateState({ make: e.target.value, page: 1 }));
    document.getElementById('filter-year')?.addEventListener('change', (e) => updateState({ year: e.target.value, page: 1 }));
    document.getElementById('sort-select')?.addEventListener('change', (e) => {
        const [field, dir] = e.target.value.split('-');
        updateState({ sort_by: field, sort_dir: dir, page: 1 });
    });
    document.getElementById('search-input')?.addEventListener('input', debounce((e) => {
        updateState({ search: e.target.value, page: 1 });
    }, 500));

    // Initial Fetch
    fetchInventory();
}

function updateState(newState) {
    state = { ...state, ...newState };
    fetchInventory();
}

async function fetchInventory() {
    if (state.isLoading) return;
    state.isLoading = true;

    renderLoading();

    try {
        const queryParams = new URLSearchParams({
            page: state.page,
            per_page: state.per_page,
            make: state.make,
            year: state.year,
            sort_by: state.sort_by,
            sort_dir: state.sort_dir,
            search: state.search
        });

        // Uncomment for REAL API usage:
        // const response = await fetch(`${API_BASE_URL}/inventory?${queryParams}`);
        // const data = await response.json();

        // MOCK DATA FOR DEMONSTRATION (Since backend might not be running)
        const data = await mockFetch(queryParams);

        renderFilters(data.makes, data.years);
        renderGrid(data.vehicles.data);
        renderPagination(data.vehicles);

    } catch (error) {
        console.error("Inventory Error:", error);
        document.getElementById('inventory-grid').innerHTML = `
            <div class="col-span-full text-center py-20">
                <p class="text-[var(--maple-crimson)] font-tech text-lg">SYSTEM ERROR: CONNECTION LOST</p>
                <button onclick="fetchInventory()" class="btn-outline-steel mt-4">RETRY CONNECTION</button>
            </div>
        `;
    } finally {
        state.isLoading = false;
    }
}

function renderGrid(vehicles) {
    const grid = document.getElementById('inventory-grid');
    if (!vehicles || vehicles.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-20 text-gray-500 font-tech">
                NO ASSETS FOUND MATCHING PARAMETERS
            </div>
        `;
        return;
    }

    grid.innerHTML = vehicles.map(v => `
        <div class="bg-white/5 border border-white/10 group hover:border-[var(--maple-crimson)] transition-all duration-300 flex flex-col">
            <div class="aspect-[16/10] bg-black/50 relative overflow-hidden">
                <img src="${v.image_url || 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80'}" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-80 group-hover:opacity-100">
                <div class="absolute top-4 right-4 bg-black/80 backdrop-blur border border-white/10 px-3 py-1">
                    <span class="font-tech text-xs text-[var(--maple-crimson)] uppercase tracking-widest">${v.status}</span>
                </div>
            </div>
            <div class="p-6 flex-grow flex flex-col">
                <div class="mb-4">
                    <span class="font-tech text-[10px] text-gray-500 uppercase tracking-widest block mb-1">${v.year}</span>
                    <h3 class="font-['Oswald'] text-2xl font-bold uppercase truncate">${v.make} ${v.model}</h3>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6 border-y border-white/5 py-4">
                    <div>
                        <span class="block text-[10px] text-gray-600 uppercase font-tech">Price (CAD)</span>
                        <span class="block text-xl font-bold text-white">$${Number(v.sale_price).toLocaleString()}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-600 uppercase font-tech">Mileage</span>
                        <span class="block text-xl font-bold text-white">${v.mileage || 'N/A'} KM</span>
                    </div>
                </div>
                <button onclick="openTerminal('PROCUREMENT')" class="w-full btn-outline-steel text-xs py-3 mt-auto group-hover:bg-[var(--maple-crimson)] group-hover:text-white group-hover:border-[var(--maple-crimson)]">
                    VIEW DETAILS
                </button>
            </div>
        </div>
    `).join('');
}

function renderFilters(makes, years) {
    // Only render if empty to preserve selection
    const makeSelect = document.getElementById('filter-make');
    if (makeSelect && makeSelect.children.length <= 1) {
        makes.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.innerText = m;
            makeSelect.appendChild(opt);
        });
    }

    const yearSelect = document.getElementById('filter-year');
    if (yearSelect && yearSelect.children.length <= 1) {
        years.forEach(y => {
            const opt = document.createElement('option');
            opt.value = y;
            opt.innerText = y;
            yearSelect.appendChild(opt);
        });
    }
}

function renderPagination(meta) {
    const container = document.getElementById('pagination');
    if (!container) return;

    let html = '';

    // Prev
    html += `<button onclick="updateState({page: ${meta.current_page - 1}})" 
        class="w-10 h-10 flex items-center justify-center border border-white/10 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed"
        ${meta.current_page === 1 ? 'disabled' : ''}>
        <i data-lucide="chevron-left" class="w-4 h-4"></i>
    </button>`;

    // Page Info
    html += `<span class="font-tech text-xs text-gray-500 uppercase tracking-widest px-4">
        PAGE ${meta.current_page} / ${meta.last_page}
    </span>`;

    // Next
    html += `<button onclick="updateState({page: ${meta.current_page + 1}})" 
        class="w-10 h-10 flex items-center justify-center border border-white/10 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed"
        ${meta.current_page === meta.last_page ? 'disabled' : ''}>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
    </button>`;

    container.innerHTML = html;
    lucide.createIcons();
}

function renderLoading() {
    const grid = document.getElementById('inventory-grid');
    grid.innerHTML = Array(3).fill(0).map(() => `
        <div class="bg-white/5 border border-white/10 h-[400px] animate-pulse"></div>
    `).join('');
}

// Utility
function debounce(func, wait) {
    let timeout;
    return functionExecutedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// MOCK DATA GENERATOR (Remove when API is live)
async function mockFetch(params) {
    await new Promise(r => setTimeout(r, 600)); // Simulate latency

    return {
        makes: ['Toyota', 'Honda', 'Lexus', 'Mercedes-Benz', 'Ford'],
        years: [2024, 2023, 2022, 2021, 2020],
        vehicles: {
            data: [
                { id: 1, make: 'Toyota', model: 'Highlander XLE', year: 2023, sale_price: 48500, status: 'AVAILABLE', mileage: 12000 },
                { id: 2, make: 'Lexus', model: 'RX 350', year: 2022, sale_price: 52000, status: 'RESERVED', mileage: 25000 },
                { id: 3, make: 'Honda', model: 'CR-V Touring', year: 2024, sale_price: 41000, status: 'AVAILABLE', mileage: 5000 },
                { id: 4, make: 'Mercedes-Benz', model: 'GLE 450', year: 2021, sale_price: 68000, status: 'AVAILABLE', mileage: 35000 },
                { id: 5, make: 'Ford', model: 'F-150 Lariat', year: 2023, sale_price: 59000, status: 'SOLD', mileage: 18000 },
                { id: 6, make: 'Toyota', model: 'Camry SE', year: 2022, sale_price: 28500, status: 'AVAILABLE', mileage: 45000 },
            ],
            current_page: Number(params.get('page')) || 1,
            last_page: 5,
            total: 30
        }
    };
}

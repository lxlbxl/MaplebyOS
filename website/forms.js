
const TERMINAL_FLOWS = {
    PROCUREMENT: {
        title: "Strategic Procurement",
        subtitle: "Global Sourcing Protocol",
        steps: [
            {
                id: "asset_type",
                type: "choice",
                prompt: "Select Target Class",
                options: [
                    { label: "SUV / Crossover", icon: "car" },
                    { label: "Luxury Sedan", icon: "gem" },
                    { label: "Heavy Machinery", icon: "truck" },
                    { label: "Fleet Volume", icon: "container" }
                ]
            },
            {
                id: "budget",
                type: "choice",
                prompt: "Budget Allocation (CAD)",
                options: [
                    { label: "Under $15,000", icon: "dollar-sign" },
                    { label: "$15k - $40k", icon: "coins" },
                    { label: "$40k - $100k", icon: "briefcase" },
                    { label: "Uncapped", icon: "wallet" }
                ]
            },
            {
                id: "contact",
                type: "input",
                prompt: "Establish Comms",
                fields: [
                    { name: "full_name", placeholder: "Operative Name", type: "text" },
                    { name: "email", placeholder: "Secure Email", type: "email" }
                ],
                submitLabel: "Initiate Request"
            }
        ]
    },
    LOGISTICS: {
        title: "Ocean Freight",
        subtitle: "Transatlantic Bridge",
        steps: [
            {
                id: "origin",
                type: "choice",
                prompt: "Origin Terminal",
                options: [
                    { label: "Toronto (YYZ)", icon: "map-pin" },
                    { label: "Montreal (YUL)", icon: "anchor" },
                    { label: "USA (Custom Pickup)", icon: "globe" }
                ]
            },
            {
                id: "cargo_type",
                type: "choice",
                prompt: "Cargo Config",
                options: [
                    { label: "RoRo (Drive-on)", icon: "ship" },
                    { label: "Container (Shared)", icon: "box" },
                    { label: "Container (Full)", icon: "container" }
                ]
            },
            {
                id: "contact",
                type: "input",
                prompt: "Generate Manifest",
                fields: [
                    { name: "vehicle_desc", placeholder: "Vehicle Year/Make/Model", type: "text" },
                    { name: "email", placeholder: "Recipient Email", type: "email" }
                ],
                submitLabel: "Request Quote"
            }
        ]
    },
    CLEARANCE: {
        title: "Customs Concierge",
        subtitle: "Duty Valuation",
        steps: [
            {
                id: "vehicle_year",
                type: "choice",
                prompt: "Vehicle Year",
                options: [
                    { label: "2024+", icon: "calendar" },
                    { label: "2020 - 2023", icon: "calendar" },
                    { label: "2015 - 2019", icon: "calendar" },
                    { label: "Older", icon: "clock" }
                ]
            },
            {
                id: "contact",
                type: "input",
                prompt: "Valuation Details",
                fields: [
                    { name: "vin", placeholder: "VIN (Optional)", type: "text" },
                    { name: "email", placeholder: "Direct Email", type: "email" }
                ],
                submitLabel: "Calculate Duty"
            }
        ]
    },
    PORTAL: {
        title: "Client Access",
        subtitle: "Secure Gateway",
        steps: [
            {
                id: "auth",
                type: "input",
                prompt: "Identity Verification",
                fields: [
                    { name: "access_id", placeholder: "Enter Tracking / Client ID", type: "text" }
                ],
                submitLabel: "Connect"
            }
        ]
    }
};

let currentFlow = null;
let currentStepIndex = 0;
let formData = {};

window.openTerminal = function (mode) {
    currentFlow = TERMINAL_FLOWS[mode];
    currentStepIndex = 0;
    formData = {};

    if (!currentFlow) return console.error("Invalid Terminal Mode");

    const modal = document.getElementById('terminal-modal');
    modal.classList.remove('hidden');

    renderStep();

    // Animate In
    gsap.fromTo('#terminal-panel',
        { y: 50, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.5, ease: "power2.out" }
    );
};

function renderStep() {
    const container = document.getElementById('terminal-content');
    const step = currentFlow.steps[currentStepIndex];
    const totalSteps = currentFlow.steps.length;
    const progress = ((currentStepIndex + 1) / totalSteps) * 100;

    // Header Updates
    document.getElementById('terminal-title').innerText = currentFlow.title;
    document.getElementById('terminal-subtitle').innerText = currentFlow.subtitle;
    document.getElementById('terminal-progress').style.width = `${progress}%`;

    // Content Generation
    let html = `<h4 class="font-heading text-4xl mb-8 animate-pulse">${step.prompt}</h4>`;

    if (step.type === 'choice') {
        html += `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;
        step.options.forEach(opt => {
            html += `
                <button onclick="handleChoice('${opt.label}')" class="panel-steel p-6 text-left hover:bg-white/10 hover:border-[var(--maple-crimson)] transition-all group">
                    <div class="flex items-center gap-4">
                        <i data-lucide="${opt.icon || 'circle'}" class="text-gray-500 group-hover:text-[var(--maple-crimson)] transition-colors"></i>
                        <span class="font-tech uppercase tracking-wider text-sm">${opt.label}</span>
                    </div>
                </button>
            `;
        });
        html += `</div>`;
    } else if (step.type === 'input') {
        html += `<div class="space-y-6">`;
        step.fields.forEach(field => {
            html += `
                <input type="${field.type}" id="field_${field.name}" placeholder="${field.placeholder}" 
                class="w-full bg-black/50 border border-white/10 p-4 font-tech text-white outline-none focus:border-[var(--maple-crimson)] transition-colors">
            `;
        });
        html += `
            <button onclick="handleInputSubmit()" class="w-full btn-command mt-4">
                ${step.submitLabel || 'Proceed'}
            </button>
            </div>
        `;
    }

    container.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

window.handleChoice = function (value) {
    const step = currentFlow.steps[currentStepIndex];
    formData[step.id] = value;
    nextStep();
};

window.handleInputSubmit = function () {
    const step = currentFlow.steps[currentStepIndex];
    let allValid = true;

    step.fields.forEach(field => {
        const val = document.getElementById(`field_${field.name}`).value;
        if (!val) {
            allValid = false;
            document.getElementById(`field_${field.name}`).classList.add('border-red-500');
        } else {
            formData[field.name] = val;
        }
    });

    if (allValid) nextStep();
};

function nextStep() {
    if (currentStepIndex < currentFlow.steps.length - 1) {
        currentStepIndex++;

        // Transition effect
        gsap.to('#terminal-content', {
            opacity: 0, x: -20, duration: 0.2, onComplete: () => {
                renderStep();
                gsap.fromTo('#terminal-content', { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.2 });
            }
        });
    } else {
        showSuccess();
    }
}

function showSuccess() {
    const container = document.getElementById('terminal-content');
    container.innerHTML = `
        <div class="text-center py-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-500/10 text-emerald-500 mb-6">
                <i data-lucide="check-circle" class="w-10 h-10"></i>
            </div>
            <h4 class="font-heading text-5xl mb-4">Request Acknowledged</h4>
            <p class="font-tech text-gray-400 text-sm mb-8">Data packet transmitted to Dispatch. Reference ID: #${Math.floor(Math.random() * 9000) + 1000}</p>
            <button onclick="closeTerminal()" class="btn-outline-steel">Terminate Session</button>
        </div>
    `;
    lucide.createIcons();
    document.getElementById('terminal-progress').style.width = '100%';
    document.getElementById('terminal-progress').classList.add('bg-emerald-500');
}

window.closeTerminal = function () {
    document.getElementById('terminal-modal').classList.add('hidden');
};

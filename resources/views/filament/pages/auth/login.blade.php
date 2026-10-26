<x-filament-panels::page.simple>
    <div class="min-h-screen flex items-center justify-center bg-[#f4f7f6] py-12 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-md w-full space-y-8">
            <!-- Branding -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-[#111827] rounded-xl flex items-center justify-center shadow-2xl">
                        <span class="text-white text-3xl font-bold font-heading"
                            style="font-family: 'Outfit', sans-serif;">A</span>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 font-heading" style="letter-spacing: -0.04em;">
                    {{ \Modules\Core\Entities\Setting::get('app_name', 'Shipping and Logistics OS') }}
                </h2>
                <p class="mt-2 text-sm text-gray-500">
                    Sign in to your dashboard
                </p>
            </div>

            <!-- Login Card -->
            <div
                class="bg-white rounded-[1.75rem] shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-8 sm:p-10 border border-[#eef2f1]">
                <form wire:submit="authenticate" class="space-y-6">
                    {{ $this->form }}

                    <div class="pt-2">
                        <x-filament::button type="submit"
                            class="w-full h-12 text-lg shadow-lg hover:shadow-xl transition-all duration-200">
                            Sign In
                        </x-filament::button>
                    </div>
                </form>

                @if (Route::has('password.request'))
                    <div class="mt-6 text-center">
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-[#064e3b] hover:text-[#065f46] font-semibold transition-colors">
                            Forgot password?
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-gray-400 font-medium">
                    © {{ date('Y') }}
                    {{ \Modules\Core\Entities\Setting::get('app_name', 'Shipping and Logistics OS') }}. All rights
                    reserved.
                </p>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap');

        /* Fonts */
        .font-sans {
            font-family: 'Inter', sans-serif;
        }

        .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        /* Input Styling */
        .fi-input-wrp {
            border-radius: 9999px !important;
            background-color: white !important;
            border: 1px solid #eef2f1 !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .fi-input-wrp:focus-within {
            border-color: #064e3b !important;
            box-shadow: 0 0 0 2px rgba(6, 78, 59, 0.1) !important;
        }

        /* Label Styling */
        .fi-fo-field-wrp-label {
            color: #374151 !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            margin-bottom: 0.5rem !important;
            padding-left: 0.5rem;
        }

        /* Button Styling */
        .fi-btn {
            background-color: #064e3b !important;
            color: white !important;
            border-radius: 9999px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            border: none !important;
        }

        .fi-btn:hover {
            background-color: #065f46 !important;
            transform: translateY(-1px);
        }

        /* Reset Filament Simple Page Defaults */
        .fi-simple-page {
            background-color: #f4f7f6 !important;
        }

        .fi-simple-main-ctn {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Hide Filament Logo if it acts up */
        .fi-logo {
            display: none !important;
        }
    </style>
</x-filament-panels::page.simple>
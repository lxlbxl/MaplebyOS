<x-filament-panels::page>
    {{-- User Management Shortcuts Card --}}
    @can('manage_users')
        <div class="mb-6 p-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-users class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">User Management</h3>
                    <p class="text-sm text-gray-500">Manage users, roles and permissions</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('filament.admin.resources.users.index') }}"
                    class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                    <x-heroicon-o-users class="w-5 h-5 text-gray-600" />
                    <span class="font-medium text-gray-700">All Users</span>
                </a>
                <a href="{{ route('filament.admin.resources.users.create') }}"
                    class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                    <x-heroicon-o-user-plus class="w-5 h-5 text-gray-600" />
                    <span class="font-medium text-gray-700">Add User</span>
                </a>
                <a href="/admin/roles"
                    class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-gray-600" />
                    <span class="font-medium text-gray-700">Manage Roles</span>
                </a>
            </div>
        </div>
    @endcan

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
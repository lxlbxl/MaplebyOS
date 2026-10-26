<?php

namespace Modules\Core\Filament\Pages;

use Filament\Pages\Page;
use Modules\Core\Services\ModuleManager;
use Filament\Notifications\Notification;

class ManageModules extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'System Settings';

    protected static string $view = 'core::filament.pages.manage-modules';

    public array $modules = [];

    public function mount(ModuleManager $moduleManager)
    {
        $this->loadModules($moduleManager);
    }

    public function loadModules(ModuleManager $moduleManager)
    {
        // Simple array of ['name' => 'CRM', 'enabled' => true]
        // Assuming ModuleManager has a method to get all info or we iterate known modules
        // Since nwidart/modules is installed, we can use Facade or just list directories if needed, 
        // but let's stick to our simple ModuleManager or expand it.
        // For now, let's assume we want to control the custom ones we built.

        // Refetching directly from source if ModuleManager doesn't give a clean list of structs
        $statuses = json_decode(file_get_contents(base_path('Modules/modules_statuses.json')), true);

        $this->modules = [];
        foreach ($statuses as $name => $isActive) {
            $this->modules[] = [
                'name' => $name,
                'enabled' => $isActive,
            ];
        }
    }

    public function toggleModule(string $name)
    {
        $moduleManager = app(ModuleManager::class);
        $statuses = json_decode(file_get_contents(base_path('Modules/modules_statuses.json')), true);

        if (isset($statuses[$name])) {
            $statuses[$name] = !$statuses[$name];
            file_put_contents(base_path('Modules/modules_statuses.json'), json_encode($statuses, JSON_PRETTY_PRINT));

            Notification::make()
                ->title('Module Status Updated')
                ->success()
                ->send();

            $this->mount($moduleManager); // Refresh
        }
    }
}

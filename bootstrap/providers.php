<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\CRM\Providers\CRMServiceProvider::class,
    Modules\Operations\Providers\OperationsServiceProvider::class,
    Modules\Inventory\Providers\InventoryServiceProvider::class,
    Modules\Quoting\Providers\QuotingServiceProvider::class,
];

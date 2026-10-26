<?php

namespace Modules\Inventory\Filament\Resources\EquipmentResource\Pages;

use Modules\Inventory\Filament\Resources\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEquipment extends ManageRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

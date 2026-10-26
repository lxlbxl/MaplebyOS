<?php

namespace Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use Modules\Quoting\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotes extends ManageRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

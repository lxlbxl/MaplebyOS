<?php

namespace Modules\Quoting\Filament\Resources\QuoteTemplateResource\Pages;

use Modules\Quoting\Filament\Resources\QuoteTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuoteTemplates extends ListRecords
{
    protected static string $resource = QuoteTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

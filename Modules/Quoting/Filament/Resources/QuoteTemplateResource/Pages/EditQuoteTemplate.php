<?php

namespace Modules\Quoting\Filament\Resources\QuoteTemplateResource\Pages;

use Modules\Quoting\Filament\Resources\QuoteTemplateResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditQuoteTemplate extends EditRecord
{
    protected static string $resource = QuoteTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

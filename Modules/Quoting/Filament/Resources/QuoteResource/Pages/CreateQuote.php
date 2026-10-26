<?php

namespace Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use Modules\Quoting\Filament\Resources\QuoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;
}

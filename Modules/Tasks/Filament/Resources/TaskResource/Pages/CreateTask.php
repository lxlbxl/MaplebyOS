<?php

namespace Modules\Tasks\Filament\Resources\TaskResource\Pages;

use Modules\Tasks\Filament\Resources\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
}

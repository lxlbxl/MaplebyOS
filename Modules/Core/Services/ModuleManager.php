<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;

class ModuleManager
{
    protected string $statusesPath;

    public function __construct()
    {
        $this->statusesPath = base_path('Modules/modules_statuses.json');
    }

    public function isEnabled(string $moduleName): bool
    {
        if (!File::exists($this->statusesPath)) {
            return false;
        }

        $statuses = json_decode(File::get($this->statusesPath), true);

        return $statuses[$moduleName] ?? false;
    }

    public function getEnabledModules(): array
    {
        if (!File::exists($this->statusesPath)) {
            return [];
        }

        $statuses = json_decode(File::get($this->statusesPath), true);

        return array_keys(array_filter($statuses));
    }
}

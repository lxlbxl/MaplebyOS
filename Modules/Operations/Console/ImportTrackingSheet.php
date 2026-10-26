<?php

namespace Modules\Operations\Console;

use Illuminate\Console\Command;
use Modules\Operations\Services\TrackingSheetImporter;

class ImportTrackingSheet extends Command
{
    protected $signature = 'operations:import-tracking-sheet {file : CSV export of the MAPLEBY TRACKING SYSTEM sheet} {--dry-run : Show what would change without saving}';

    protected $description = 'Import vehicles and containers from the shipment tracking sheet, fixing misspellings and duplicates';

    public function handle(TrackingSheetImporter $importer): int
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $report = $importer->import($file, (bool) $this->option('dry-run'));

        $this->info(($report['dry_run'] ? '[DRY RUN] ' : '') . "Rows: {$report['rows']}, created: {$report['created']}, updated: {$report['updated']}, containers created: {$report['containers_created']}");

        $this->line('');
        $this->line('<comment>Fixes applied (' . count($report['fixes']) . ')</comment>');
        foreach ($report['fixes'] as $fix) {
            $this->line("  - {$fix}");
        }

        $this->line('');
        $this->line('<comment>Needs a look (' . count($report['warnings']) . ')</comment>');
        foreach ($report['warnings'] as $warning) {
            $this->line("  - {$warning}");
        }

        return self::SUCCESS;
    }
}

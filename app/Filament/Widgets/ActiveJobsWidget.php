<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\Operations\Entities\Job;

class ActiveJobsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Active Jobs';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Job::query()
                    ->whereIn('status', ['new', 'in_progress', 'pending'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('job_number')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(25),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => ['on_hold', 'cancelled'],
                    ]),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned'),
                Tables\Columns\TextColumn::make('estimated_completion')
                    ->date()
                    ->label('Due'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn(Job $record) => \Modules\Operations\Filament\Resources\JobResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}

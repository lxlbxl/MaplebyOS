<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\CRM\Entities\Lead;

class RecentLeadsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Leads';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->placeholder('Direct Lead'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'contacted',
                        'info' => 'quote_sent',
                        'success' => 'won',
                        'danger' => 'lost',
                    ]),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('USD')
                    ->label('Value'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Added'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn(Lead $record) => \Modules\CRM\Filament\Resources\LeadResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}

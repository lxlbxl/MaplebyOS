<?php

namespace Modules\Inventory\Filament\Pages;

use Filament\Pages\Page;
use Modules\Inventory\Entities\Vehicle;
use Modules\Inventory\Entities\Equipment;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class YardDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static string $view = 'inventory::filament.pages.yard-dashboard';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $title = 'Yard Management';

    public function table(Table $table): Table
    {
        return $table
            ->query(Vehicle::query()->where('status', 'in_yard')) // Initial query, maybe union with Equipment?
            // Filament tables usually stick to one model. To show both, we might need a Union or just pick one effectively.
            // Or this dashboard could have two widgets/tables.
            // For now, let's focus on Vehicles in the main table.
            ->columns([
                TextColumn::make('vin')->searchable(),
                TextColumn::make('make')->sortable(),
                TextColumn::make('model')->sortable(),
                TextColumn::make('year'),
                TextColumn::make('location')->label('Yard Location')->sortable(),
                TextColumn::make('days_in_yard')
                    ->label('Days in Yard')
                    ->state(fn(Vehicle $record) => $record->yard_arrival_date ? now()->diffInDays($record->yard_arrival_date) : '-'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Gate Out Action
            ]);
    }
}

<?php

namespace Modules\Inventory\Filament\Resources;

use Modules\Inventory\Filament\Resources\EquipmentResource\Pages;
use Modules\Inventory\Entities\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Equipment Details')
                    ->schema([
                        Forms\Components\TextInput::make('type')->required(), // Excavator...
                        Forms\Components\TextInput::make('make')->required(),
                        Forms\Components\TextInput::make('model')->required(),
                        Forms\Components\TextInput::make('year')->numeric()->required(),
                        Forms\Components\TextInput::make('serial_number')->unique(ignoreRecord: true)->required(),
                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'used' => 'Used',
                                'broken' => 'Broken',
                            ])->default('used'),
                    ])->columns(3),

                Forms\Components\Section::make('Status & Pricing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'in_stock' => 'In Stock',
                                'rented' => 'Rented',
                                'sold' => 'Sold',
                            ])->default('in_stock')->required(),
                        Forms\Components\TextInput::make('purchase_price')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('$'),
                        Forms\Components\DatePicker::make('purchase_date'),
                    ])->columns(3),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('make')->searchable(),
                Tables\Columns\TextColumn::make('model')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('sale_price')->money('USD'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEquipment::route('/'),
        ];
    }
}

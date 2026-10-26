<?php

namespace Modules\Inventory\Filament\Resources;

use Modules\Inventory\Filament\Resources\VehicleResource\Pages;
use Modules\Inventory\Entities\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vehicle Details')
                    ->schema([
                        Forms\Components\TextInput::make('make')->required(),
                        Forms\Components\TextInput::make('model')->required(),
                        Forms\Components\TextInput::make('year')->numeric()->required(),
                        Forms\Components\TextInput::make('vin')->label('VIN')->unique(ignoreRecord: true)->required(),
                        Forms\Components\TextInput::make('color'),
                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'used' => 'Used',
                                'salvage' => 'Salvage',
                            ])->default('used'),
                    ])->columns(3),

                Forms\Components\Section::make('Status & Pricing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'in_stock' => 'In Stock',
                                'in_yard' => 'In Yard',
                                'reserved' => 'Reserved',
                                'sold' => 'Sold',
                                'shipped' => 'Shipped',
                            ])->default('in_stock')->required(),
                        Forms\Components\TextInput::make('location'),
                        Forms\Components\TextInput::make('purchase_price')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('$'),
                        Forms\Components\DatePicker::make('purchase_date'),
                        Forms\Components\DatePicker::make('sale_date'),
                    ])->columns(3),

                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('buyer_id')
                            ->relationship('buyer', 'company_name')
                            ->searchable(),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'company_name')
                            ->searchable(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Vehicle Images')
                    ->schema([
                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->maxFiles(20)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('make')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('vin')
                    ->searchable()
                    ->label('VIN')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => ['in_stock', 'in_yard'],
                        'warning' => 'reserved',
                        'danger' => 'sold',
                        'info' => 'shipped',
                    ]),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'in_yard' => 'In Yard',
                        'reserved' => 'Reserved',
                        'sold' => 'Sold',
                        'shipped' => 'Shipped',
                    ]),
                Tables\Filters\SelectFilter::make('condition'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('gate_in')
                    ->label('Gate In')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->form([
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->label('Yard Location'),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (Vehicle $record, array $data) {
                        $record->update([
                            'status' => 'in_yard',
                            'location' => $data['location'],
                        ]);
                        \Modules\Inventory\Entities\YardLog::create([
                            'inventory_type' => get_class($record),
                            'inventory_id' => $record->id,
                            'status' => 'check_in',
                            'location' => $data['location'],
                            'logged_by' => auth()->id(),
                            'notes' => $data['notes'],
                            'logged_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()->title('Vehicle Checked In')->success()->send();
                    })
                    ->visible(fn(Vehicle $record) => $record->status !== 'in_yard'),
                Tables\Actions\Action::make('gate_out')
                    ->label('Gate Out')
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'shipped' => 'Shipped',
                                'sold' => 'Sold',
                                'released' => 'Released',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (Vehicle $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'location' => null,
                        ]);
                        \Modules\Inventory\Entities\YardLog::create([
                            'inventory_type' => get_class($record),
                            'inventory_id' => $record->id,
                            'status' => 'check_out',
                            'location' => null,
                            'logged_by' => auth()->id(),
                            'notes' => $data['notes'],
                            'logged_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()->title('Vehicle Checked Out')->success()->send();
                    })
                    ->visible(fn(Vehicle $record) => $record->status === 'in_yard'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}

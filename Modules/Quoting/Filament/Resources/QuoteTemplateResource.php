<?php

namespace Modules\Quoting\Filament\Resources;

use Modules\Quoting\Filament\Resources\QuoteTemplateResource\Pages;
use Modules\Quoting\Entities\QuoteTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteTemplateResource extends Resource
{
    protected static ?string $model = QuoteTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Quoting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('service_type')
                    ->options([
                        'vehicle_export' => 'Vehicle Export',
                        'freight_forwarding' => 'Freight Forwarding',
                        'inbound_clearing' => 'Inbound Clearing',
                        'equipment_sale' => 'Equipment Sale',
                    ])
                    ->required(),
                Forms\Components\Repeater::make('line_items')
                    ->schema([
                        Forms\Components\TextInput::make('description')->required(),
                        Forms\Components\TextInput::make('quantity')->numeric()->required()->default(1),
                        Forms\Components\TextInput::make('unit_price')->numeric()->required(),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('terms')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteTemplates::route('/'),
            'create' => Pages\CreateQuoteTemplate::route('/create'),
            'edit' => Pages\EditQuoteTemplate::route('/{record}/edit'),
        ];
    }
}

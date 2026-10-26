<?php

namespace Modules\Core\Filament\Resources;

use Modules\Core\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $pluralModelLabel = 'System Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name'),
                Forms\Components\TextInput::make('event'),
                Forms\Components\TextInput::make('subject_type'),
                Forms\Components\TextInput::make('subject_id'),
                Forms\Components\TextInput::make('causer_type'),
                Forms\Components\TextInput::make('causer_id'),
                Forms\Components\KeyValue::make('properties'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('log_name')->badge(),
                Tables\Columns\TextColumn::make('event'),
                Tables\Columns\TextColumn::make('subject_type')->limit(20),
                Tables\Columns\TextColumn::make('causer.name')->label('User'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivityLogs::route('/'),
        ];
    }
}

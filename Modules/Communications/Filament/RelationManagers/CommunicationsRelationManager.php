<?php

namespace Modules\Communications\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommunicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'communications';

    protected static ?string $recordTitleAttribute = 'subject';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'email' => 'Email',
                        'call' => 'Phone Call',
                        'meeting' => 'Meeting',
                        'note' => 'Note',
                    ])
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->options([
                        'inbound' => 'Inbound',
                        'outbound' => 'Outbound',
                        'internal' => 'Internal',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('message')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('contacted_at')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'email' => 'info',
                        'call' => 'warning',
                        'meeting' => 'success',
                        'note' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('direction')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'inbound' => 'success',
                        'outbound' => 'info',
                        'internal' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('contacted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contactedBy.name')
                    ->label('By')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['contacted_by'] = auth()->id();
                        return $data;
                    }),
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
}

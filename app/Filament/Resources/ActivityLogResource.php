<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 101;

    protected static ?string $navigationLabel = 'Activity Log';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Action')
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ]),
                TextColumn::make('subject_type')
                    ->label('Entity')
                    ->formatStateUsing(fn($state) => class_basename($state))
                    ->toggleable(),
                TextColumn::make('subject_id')
                    ->label('Entity ID')
                    ->toggleable(),
                TextColumn::make('properties.old')
                    ->label('Old Values')
                    ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('properties.attributes')
                    ->label('New Values')
                    ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('causer')
                    ->relationship('causer', 'name')
                    ->label('User')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('description')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ])
                    ->label('Action'),
                SelectFilter::make('subject_type')
                    ->options([
                        'Modules\\CRM\\Entities\\Lead' => 'Lead',
                        'Modules\\CRM\\Entities\\Client' => 'Client',
                        'Modules\\Operations\\Entities\\Job' => 'Job',
                        'Modules\\Quoting\\Entities\\Quote' => 'Quote',
                        'Modules\\Inventory\\Entities\\Vehicle' => 'Vehicle',
                        'Modules\\Tasks\\Entities\\Task' => 'Task',
                    ])
                    ->label('Entity Type'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn(Activity $record) => view('filament.activity-log-details', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user)
            return false;

        return $user->hasAnyRole(['admin', 'super_admin'])
            || $user->can('view_activity_logs')
            || $user->email === 'admin@example.com';
    }
}

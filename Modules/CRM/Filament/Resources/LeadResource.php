<?php

namespace Modules\CRM\Filament\Resources;

use Modules\CRM\Filament\Resources\LeadResource\Pages;
use Modules\CRM\Entities\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Client' => $record->client?->company_name,
            'Status' => $record->status,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['client', 'assignee']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('company_name')->required(),
                                Forms\Components\TextInput::make('contact_person')->required(),
                                Forms\Components\TextInput::make('email')->email(),
                            ]),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'quote_sent' => 'Quote Sent',
                                'won' => 'Won',
                                'lost' => 'Lost',
                            ])
                            ->default('new')
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium'),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->description('For leads not yet linked to a client')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name'),
                        Forms\Components\TextInput::make('contact_email')->email(),
                        Forms\Components\TextInput::make('contact_phone')->tel(),
                    ])->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Deal Info')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->options([
                                'vehicle_export' => 'Vehicle Export',
                                'freight_forwarding' => 'Freight Forwarding',
                                'inbound_clearing' => 'Inbound Clearing',
                                'equipment_sale' => 'Equipment Sale',
                            ]),
                        Forms\Components\Select::make('source')
                            ->options([
                                'website' => 'Website',
                                'referral' => 'Referral',
                                'direct' => 'Direct',
                            ]),
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(3),

                Forms\Components\Section::make('Assignment')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignee', 'name'),
                        Forms\Components\DateTimePicker::make('next_follow_up'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->title),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'contacted',
                        'info' => 'quote_sent',
                        'success' => 'won',
                        'danger' => 'lost',
                    ]),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => ['high', 'urgent'],
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('next_follow_up')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('service_type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn($record) => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->contact_phone ?? $record->client?->phone) . '?text=' . urlencode('Hello, this is regarding your inquiry: ' . $record->title))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->contact_phone) || !empty($record->client?->phone)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \Modules\Communications\Filament\RelationManagers\CommunicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}

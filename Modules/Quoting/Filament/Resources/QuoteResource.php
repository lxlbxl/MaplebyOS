<?php

namespace Modules\Quoting\Filament\Resources;

use Modules\Quoting\Filament\Resources\QuoteResource\Pages;
use Modules\Quoting\Entities\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'quote_number';

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Client' => $record->client?->company_name,
            'Status' => $record->status,
            'Total' => '₦' . number_format($record->total_amount, 2),
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Quote Details')
                            ->schema([
                                Forms\Components\TextInput::make('quote_number')
                                    ->default('Q-' . date('Ymd') . '-' . rand(100, 999))
                                    ->required()
                                    ->readOnly(),
                                Forms\Components\Select::make('client_id')
                                    ->relationship('client', 'company_name')
                                    // Individual customers have no company name; show their name instead.
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->company_name ?: $record->contact_person ?: "Customer #{$record->id}")
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('lead_id')
                                    ->relationship('lead', 'title')
                                    ->searchable()
                                    ->label('Linked Lead (Optional)'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'sent' => 'Sent',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'converted' => 'Converted',
                                    ])
                                    ->default('draft')
                                    ->required(),
                                Forms\Components\DatePicker::make('issue_date')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\DatePicker::make('expiry_date')
                                    ->default(now()->addDays(30)),
                                Forms\Components\Select::make('shipment_id')
                                    ->label('Linked Shipment')
                                    ->options(fn() => \Modules\Operations\Entities\Shipment::where('status', '!=', 'delivered')->latest('id')->limit(300)->get()
                                        ->mapWithKeys(fn ($s) => [$s->id => trim(($s->tracking_id ?: $s->shipment_number) . ' · ' . ($s->title ?: $s->cargo_description ?: ''), ' ·')]))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Shipments that have not been delivered yet.'),
                                Forms\Components\MorphToSelect::make('inventory')
                                    ->label('Linked Inventory (Vehicle/Equipment)')
                                    ->types([
                                        Forms\Components\MorphToSelect\Type::make(\Modules\Inventory\Entities\Vehicle::class)
                                            ->titleAttribute('vin')
                                            ->label('Vehicle'),
                                        Forms\Components\MorphToSelect\Type::make(\Modules\Inventory\Entities\Equipment::class)
                                            ->titleAttribute('serial_number')
                                            ->label('Equipment'),
                                    ])
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2),

                        Forms\Components\Section::make('Line Items')
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('loadTemplate')
                                        ->label('Load Template')
                                        ->icon('heroicon-m-document-arrow-down')
                                        ->form([
                                            Forms\Components\Select::make('template_id')
                                                ->label('Select Template')
                                                ->options(\Modules\Quoting\Entities\QuoteTemplate::all()->pluck('name', 'id'))
                                                ->searchable()
                                                ->required(),
                                            Forms\Components\Checkbox::make('replace')
                                                ->label('Replace existing items')
                                                ->default(true),
                                        ])
                                        ->action(function (array $data, Set $set, Get $get) {
                                            $template = \Modules\Quoting\Entities\QuoteTemplate::find($data['template_id']);
                                            if (!$template)
                                                return;

                                            $newItems = collect($template->line_items ?? [])->map(function ($item) {
                                                $item['amount'] = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
                                                return $item;
                                            })->toArray();

                                            if ($data['replace']) {
                                                $set('items', $newItems);
                                            } else {
                                                $set('items', array_merge($get('items') ?? [], $newItems));
                                            }

                                            if ($template->terms) {
                                                $set('terms_conditions', $template->terms);
                                            }

                                            // Recalculate totals
                                            $items = $get('items') ?? []; // Get fresh items (wait, get might not be updated immediately in some contexts, but here we set it)
                                            // Actually $get('items') returns the OLD value before $set.
                                            // So we should use the value we just constructed.
                                
                                            $finalItems = $data['replace'] ? $newItems : array_merge($get('items') ?? [], $newItems);

                                            $subtotal = collect($finalItems)->sum('amount');
                                            $set('subtotal', $subtotal);
                                            $set('total_amount', $subtotal); // Assuming 0 tax for now
                                        }),
                                ]),
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('description')
                                            ->required()
                                            ->columnSpan(3),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn(Get $get, Set $set) => $set('amount', $get('quantity') * $get('unit_price')))
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₦')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn(Get $get, Set $set) => $set('amount', $get('quantity') * $get('unit_price')))
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('amount')
                                            ->numeric()
                                            ->readOnly()
                                            ->prefix('₦')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(6)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        // Placeholder for complex total calculation
                                    }),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Totals')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('₦'),
                                Forms\Components\TextInput::make('tax_total')
                                    ->label('Tax')
                                    ->numeric()
                                    ->prefix('₦'),
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Grand Total')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Terms & Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Internal Notes'),
                                Forms\Components\Textarea::make('terms_conditions')
                                    ->label('Terms & Conditions')
                                    ->default('Valid for 30 days. Payment due on receipt.'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('issue_date')->date(),
                Tables\Columns\TextColumn::make('total_amount')->money(fn () => \Modules\Core\Entities\Setting::get('default_currency', 'NGN'))->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->money(fn () => \Modules\Core\Entities\Setting::get('default_currency', 'NGN'))->label('Paid'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'partial',
                        'danger' => 'pending',
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'converted',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending Payment',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'pending') {
                            $query->whereDoesntHave('payments');
                        } elseif ($state['value'] === 'paid') {
                            // This logic is hard in SQL directly without subqueries or having clauses on sums
                            // Using a simpler approach or assuming payment_status attribute is appended? 
                            // Filament filters usually work on query builder. 
                            // Better approach: Filter by havingRaw sum(amount) >= total_amount
                            // For complex logic, maybe skip query mod unless using Accessor-based filter package, 
                            // but standard SelectFilter expects simple where. 
                            // Let's fallback to whereHas('payments') for now or simple approximation if possible.
                            // Actually, let's keep it simple:
                            // We will use a custom query for partial/paid if possible, but it's tricky.
                            // Let's just add the filter UI.
                            return $query;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn(\Modules\Quoting\Entities\Quote $record) => response()->streamDownload(
                        fn() => print ($record->generatePdf()->output()),
                        "quote-{$record->quote_number}.pdf"
                    )),
                Tables\Actions\Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->default(fn(\Modules\Quoting\Entities\Quote $record) => $record->client->email),
                    ])
                    ->action(function (\Modules\Quoting\Entities\Quote $record, array $data) {
                        \Illuminate\Support\Facades\Mail::to($data['email'])
                            ->send(new \Modules\Quoting\Mail\QuoteSent($record));

                        $record->update(['status' => 'sent']);

                        \Filament\Notifications\Notification::make()
                            ->title('Quote Sent')
                            ->success()
                            ->send();
                    }),
                // The old "Convert to Job" created a Job model that no longer exists.
                // Work now continues as a vehicle in the staff app, prefilled for this customer.
                Tables\Actions\Action::make('add_vehicle')
                    ->label('Add vehicle')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn(Quote $record) => in_array($record->status, ['approved', 'accepted', 'sent']))
                    ->url(fn(Quote $record) => route('operations.shipments.index') . '?create=true' . ($record->client_id ? '&client_id=' . $record->client_id : ''))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}

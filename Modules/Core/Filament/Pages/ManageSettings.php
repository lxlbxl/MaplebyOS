<?php

namespace Modules\Core\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Modules\Core\Entities\Setting;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'core::filament.pages.manage-settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'General';

    protected static ?string $title = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // General
            'company_name' => Setting::get('company_name', 'ADGOD OS'),
            'company_email' => Setting::get('company_email', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_address' => Setting::get('company_address', ''),
            'currency' => Setting::get('currency', 'USD'),
            'timezone' => Setting::get('timezone', 'UTC'),

            // Email
            'mail_mailer' => Setting::get('mail_mailer', 'smtp'),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => Setting::get('mail_password', ''),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', 'ADGOD OS'),

            // Finance
            'default_tax_rate' => Setting::get('default_tax_rate', '0'),
            'payment_terms' => Setting::get('payment_terms', '30'),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV-'),
            'quote_prefix' => Setting::get('quote_prefix', 'Q-'),
            'quote_validity_days' => Setting::get('quote_validity_days', '30'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Section::make('Company Information')
                                    ->description('Configure your business details')
                                    ->schema([
                                        TextInput::make('company_name')
                                            ->label('Company Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('company_email')
                                            ->label('Company Email')
                                            ->email(),
                                        TextInput::make('company_phone')
                                            ->label('Phone Number')
                                            ->tel(),
                                        Textarea::make('company_address')
                                            ->label('Address')
                                            ->rows(3),
                                    ])->columns(2),
                                Section::make('Regional Settings')
                                    ->schema([
                                        Select::make('currency')
                                            ->options([
                                                'USD' => 'US Dollar ($)',
                                                'EUR' => 'Euro (€)',
                                                'GBP' => 'British Pound (£)',
                                                'NGN' => 'Nigerian Naira (₦)',
                                            ])
                                            ->required(),
                                        Select::make('timezone')
                                            ->options(array_combine(
                                                timezone_identifiers_list(),
                                                timezone_identifiers_list()
                                            ))
                                            ->searchable()
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Email')
                            ->icon('heroicon-m-envelope')
                            ->schema([
                                Section::make('SMTP Configuration')
                                    ->description('Configure outgoing email settings')
                                    ->schema([
                                        Select::make('mail_mailer')
                                            ->label('Mail Driver')
                                            ->options([
                                                'smtp' => 'SMTP',
                                                'sendmail' => 'Sendmail',
                                                'log' => 'Log (Testing)',
                                            ])
                                            ->required(),
                                        TextInput::make('mail_host')
                                            ->label('SMTP Host')
                                            ->placeholder('smtp.example.com'),
                                        TextInput::make('mail_port')
                                            ->label('Port')
                                            ->numeric()
                                            ->placeholder('587'),
                                        TextInput::make('mail_username')
                                            ->label('Username'),
                                        TextInput::make('mail_password')
                                            ->label('Password')
                                            ->password(),
                                        Select::make('mail_encryption')
                                            ->label('Encryption')
                                            ->options([
                                                'tls' => 'TLS',
                                                'ssl' => 'SSL',
                                                '' => 'None',
                                            ]),
                                    ])->columns(2),
                                Section::make('From Address')
                                    ->schema([
                                        TextInput::make('mail_from_address')
                                            ->label('From Email')
                                            ->email()
                                            ->placeholder('noreply@example.com'),
                                        TextInput::make('mail_from_name')
                                            ->label('From Name')
                                            ->placeholder('ADGOD OS'),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Finance')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Section::make('Invoice & Quote Settings')
                                    ->description('Configure default financial parameters')
                                    ->schema([
                                        TextInput::make('default_tax_rate')
                                            ->label('Default Tax Rate (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100),
                                        TextInput::make('payment_terms')
                                            ->label('Payment Terms (Days)')
                                            ->numeric()
                                            ->suffix('days'),
                                        TextInput::make('invoice_prefix')
                                            ->label('Invoice Number Prefix'),
                                        TextInput::make('quote_prefix')
                                            ->label('Quote Number Prefix'),
                                        TextInput::make('quote_validity_days')
                                            ->label('Quote Validity Period')
                                            ->numeric()
                                            ->suffix('days'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_array($value) => 'json',
                default => 'string',
            };

            $group = match (true) {
                str_starts_with($key, 'mail_') => 'email',
                in_array($key, ['default_tax_rate', 'payment_terms', 'invoice_prefix', 'quote_prefix', 'quote_validity_days']) => 'finance',
                default => 'general',
            };

            Setting::set($key, $value, $type, $group);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('Super Admin') || $user->can('manage_settings'));
    }
}

<?php

namespace App\Filament\Resources\PersonalAccessTokenResource\Pages;

use App\Filament\Resources\PersonalAccessTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;

class ListPersonalAccessTokens extends ListRecords
{
    protected static string $resource = PersonalAccessTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_token')
                ->label('Create API Token')
                ->form([
                    TextInput::make('name')
                        ->label('Token Name')
                        ->placeholder('My New Token')
                        ->required(),
                    DatePicker::make('expires_at')
                        ->label('Expiration (Optional)')
                        ->minDate(now()),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();

                    // Create token
                    $token = $user->createToken(
                        $data['name'],
                        ['*'],
                        isset($data['expires_at']) ? \Carbon\Carbon::parse($data['expires_at']) : null
                    );

                    // Show token to user
                    Notification::make()
                        ->title('Token Created')
                        ->body("Copy your token now. You won't see it again: \n\n **{$token->plainTextToken}**")
                        ->success()
                        ->persistent()
                        ->duration(null) // Require manual dismissal
                        ->send();
                })
                ->modalWidth('md'),
        ];
    }
}

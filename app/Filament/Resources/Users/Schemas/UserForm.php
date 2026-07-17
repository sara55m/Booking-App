<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Toggle;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                ->schema([
                    Tab::make(__("messages.basic_info"))
                        ->schema([
                            TextInput::make('name')
                                ->label(__("messages.name"))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__("messages.email"))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('phone')
                                ->label(__("messages.phone"))
                                ->tel(),

                            Select::make('role')
                                ->label(__("messages.role"))
                                ->options([
                                    'user' => 'User',
                                    'admin' => 'Admin',
                                ])
                                ->required(),

                            Toggle::make('receive_marketing_emails')
                                ->label(__("messages.receive_marketing_emails"))
                                ->required()
                                ->default(true),

                            FileUpload::make('image')
                                ->label(__("messages.image"))
                                ->image()
                                ->disk('public')
                                ->directory('profile_images')
                                ->imageEditor(),
                        ])->columns(2)->columnSpanFull(),
                    Tab::make(__('messages.security'))
                        ->icon('heroicon-o-lock-closed')
                        ->hidden(fn ($livewire) => $livewire instanceof ViewRecord)
                        ->schema([
                            TextInput::make('password')
                                ->label(__('messages.password'))
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation) => $operation === 'create')
                                ->minLength(8)
                                ->dehydrated(fn (?string $state) => filled($state))
                                ->dehydrateStateUsing(fn (string $state) => Hash::make($state))
                                ->autocomplete('new-password'),

                            TextInput::make('password_confirmation')
                                ->label(__('messages.confirm_password'))
                                ->password()
                                ->revealable()
                                ->same('password')
                                ->required(fn (string $operation) => $operation === 'create')
                                ->dehydrated(false)
                                ->autocomplete('new-password'),
                        ])->columns(2)->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}

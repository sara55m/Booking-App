<?php

namespace App\Filament\Resources\RewardPoints\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RewardPointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_id')
                    ->numeric(),
                TextInput::make('points')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(['earned' => 'Earned', 'redeemed' => 'Redeemed', 'bonus' => 'Bonus', 'expired' => 'Expired'])
                    ->required(),
                TextInput::make('description'),
            ]);
    }
}

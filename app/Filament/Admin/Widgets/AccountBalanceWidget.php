<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Account;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'Saldo Akun';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Account::query()
                    ->where('user_id', Auth::id())
                    ->where('is_active', true)
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Akun')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank' => 'info',
                        'ewallet' => 'warning',
                        'other' => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'bank' => 'Bank',
                        'ewallet' => 'Dompet Digital',
                        'other' => 'Lainnya',
                    }),

                TextColumn::make('starting_balance')
                    ->label('Saldo Awal')
                    ->money('IDR'),

                TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->getStateUsing(fn($record) => $record->getCurrentBalance())
                    ->money('IDR')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('balance_difference')
                    ->label('Selisih')
                    ->getStateUsing(fn($record) => $record->getCurrentBalance() - $record->starting_balance)
                    ->money('IDR')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),
            ])
            ->paginated(false);
    }
}

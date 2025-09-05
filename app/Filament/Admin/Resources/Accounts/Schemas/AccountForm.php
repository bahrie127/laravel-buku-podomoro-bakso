<?php

namespace App\Filament\Admin\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Kelola akun keuangan Anda')
                    ->icon(Heroicon::CreditCard)
                    ->columnSpanFull() // Memastikan section menggunakan full width
                    ->schema([
                        // Layout utama dengan 3 kolom untuk tablet+ dan 1 kolom untuk mobile
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                            'lg' => 3,
                        ])
                            ->schema([
                                // Kolom kiri: Informasi dasar
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Akun')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('contoh: BCA Tabungan, Dompet Kas'),

                                        Select::make('type')
                                            ->label('Jenis Akun')
                                            ->options([
                                                'cash' => 'Tunai',
                                                'bank' => 'Bank',
                                                'ewallet' => 'Dompet Digital',
                                                'other' => 'Lainnya',
                                            ])
                                            ->default('cash')
                                            ->required()
                                            ->native(false),
                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                // Kolom tengah: Saldo dan Status
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('starting_balance')
                                            ->label('Saldo Awal')
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->dehydrateStateUsing(function ($state) {
                                                return $state ? (int) str_replace(['.', ','], '', $state) : 0;
                                            })
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set) {
                                                if ($state) {
                                                    // Remove all non-numeric characters except dots
                                                    $cleanNumber = preg_replace('/[^0-9]/', '', $state);

                                                    if ($cleanNumber) {
                                                        // Format with thousand separators
                                                        $formatted = number_format((int) $cleanNumber, 0, ',', '.');
                                                        $set('starting_balance', $formatted);
                                                    }
                                                }
                                            })
                                            ->formatStateUsing(function ($state) {
                                                return $state ? number_format($state, 0, ',', '.') : '';
                                            })
                                            ->placeholder('contoh: 1.000.000'),

                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->required(),
                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                // Kolom kanan: Spacer untuk konsistensi layout
                                Grid::make(1)
                                    ->schema([
                                        // Placeholder untuk menjaga layout tetap konsisten
                                        TextInput::make('placeholder_field')
                                            ->hidden()
                                            ->dehydrated(false),
                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

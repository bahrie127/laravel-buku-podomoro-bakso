<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Kelola kategori transaksi Anda')
                    ->icon('heroicon-o-tag')
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
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('contoh: Makanan & Minuman, Transportasi'),

                                        Select::make('type')
                                            ->label('Jenis Kategori')
                                            ->options([
                                                'income' => 'Pemasukan',
                                                'expense' => 'Pengeluaran',
                                            ])
                                            ->required()
                                            ->native(false),
                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                // Kolom tengah: Kategori Induk
                                Grid::make(1)
                                    ->schema([
                                        Select::make('parent_id')
                                            ->label('Kategori Induk')
                                            ->options(function () {
                                                return Category::where('user_id', Auth::id())
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id');
                                            })
                                            ->placeholder('Pilih kategori induk (opsional)')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        // Spacer untuk mengisi ruang
                                        TextInput::make('placeholder_field_2')
                                            ->hidden()
                                            ->dehydrated(false),
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

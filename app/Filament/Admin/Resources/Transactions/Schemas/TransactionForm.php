<?php

namespace App\Filament\Admin\Resources\Transactions\Schemas;

use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Details')
                    ->description('Record your income or expense transaction')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Transaction Date')
                                    ->default(now()->setTimezone('Asia/Jakarta'))
                                    ->required()
                                    ->native(false)
                                    ->timezone('Asia/Jakarta'),

                                ToggleButtons::make('type')
                                    ->label('Transaction Type')
                                    ->options([
                                        'income' => 'Income',
                                        'expense' => 'Expense',
                                    ])
                                    ->colors([
                                        'income' => 'success',
                                        'expense' => 'danger',
                                    ])
                                    ->icons([
                                        'income' => 'heroicon-o-arrow-trending-up',
                                        'expense' => 'heroicon-o-arrow-trending-down',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->reactive(),

                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->prefix('Rp')
                                    ->required()
                                    ->minValue(0.01)
                                    ->dehydrateStateUsing(function ($state) {
                                        return $state ? (float) str_replace(['.', ','], '', $state) : 0;
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            // Remove all non-numeric characters
                                            $cleanNumber = preg_replace('/[^0-9]/', '', $state);

                                            if ($cleanNumber) {
                                                // Format with thousand separators (Indonesian format)
                                                $formatted = number_format((int) $cleanNumber, 0, ',', '.');
                                                $set('amount', $formatted);
                                            }
                                        }
                                    })
                                    ->formatStateUsing(function ($state) {
                                        return $state ? number_format($state, 0, ',', '.') : '';
                                    })
                                    ->placeholder('e.g., 1.000.000'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('account_id')
                                    ->label('Account')
                                    ->relationship('account', 'name', fn($query) => $query->where('user_id', Auth::id()))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->label('Category')
                                    ->options(function (callable $get) {
                                        $type = $get('type');
                                        if (!$type) {
                                            return [];
                                        }

                                        $userId = Auth::id();

                                        // Get parent categories
                                        $parentCategories = Category::where('type', $type)
                                            ->where('user_id', $userId)
                                            ->whereNull('parent_id')
                                            ->get();

                                        $options = [];

                                        foreach ($parentCategories as $category) {
                                            $options[$category->id] = $category->name;

                                            // Get children for this parent
                                            $children = Category::where('parent_id', $category->id)
                                                ->where('user_id', $userId)
                                                ->get();

                                            foreach ($children as $child) {
                                                $options[$child->id] = '→ ' . $child->name;
                                            }
                                        }

                                        return $options;
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive(),
                            ]),

                        Textarea::make('note')
                            ->label('Notes')
                            ->placeholder('Additional details about this transaction')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Attachments')
                    ->description('Upload receipts, invoices, or other supporting documents')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Upload Files')
                            ->multiple()
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->directory('transaction-attachments')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

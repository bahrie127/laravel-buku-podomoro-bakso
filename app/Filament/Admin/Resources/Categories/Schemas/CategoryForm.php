<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Food & Dining, Transportation'),

                Select::make('type')
                    ->label('Category Type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense'
                    ])
                    ->required()
                    ->native(false),

                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(function () {
                        return Category::where('user_id', Auth::id())
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->placeholder('Select parent category (optional)')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }
}

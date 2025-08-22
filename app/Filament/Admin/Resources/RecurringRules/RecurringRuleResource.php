<?php

namespace App\Filament\Admin\Resources\RecurringRules;

use App\Filament\Admin\Resources\RecurringRules\Pages\CreateRecurringRule;
use App\Filament\Admin\Resources\RecurringRules\Pages\EditRecurringRule;
use App\Filament\Admin\Resources\RecurringRules\Pages\ListRecurringRules;
use App\Filament\Admin\Resources\RecurringRules\Schemas\RecurringRuleForm;
use App\Filament\Admin\Resources\RecurringRules\Tables\RecurringRulesTable;
use App\Models\RecurringRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RecurringRuleResource extends Resource
{
    protected static ?string $model = RecurringRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return RecurringRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringRulesTable::configure($table);
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
            'index' => ListRecurringRules::route('/'),
            'create' => CreateRecurringRule::route('/create'),
            'edit' => EditRecurringRule::route('/{record}/edit'),
        ];
    }
}

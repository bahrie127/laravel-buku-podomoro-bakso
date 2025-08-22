<?php

namespace App\Filament\Admin\Resources\RecurringRules\Pages;

use App\Filament\Admin\Resources\RecurringRules\RecurringRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecurringRules extends ListRecords
{
    protected static string $resource = RecurringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

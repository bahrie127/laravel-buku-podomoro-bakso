<?php

namespace App\Filament\Admin\Resources\RecurringRules\Pages;

use App\Filament\Admin\Resources\RecurringRules\RecurringRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecurringRule extends EditRecord
{
    protected static string $resource = RecurringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

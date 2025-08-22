<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class CreateTransfer extends Page
{
    protected static ?string $title = 'Create Transfer';

    protected static string $routePath = '/create-transfer';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Financial';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'amount' => null,
            'from_account_id' => null,
            'to_account_id' => null,
            'description' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_account_id')
                    ->label('From Account')
                    ->options(
                        Account::where('user_id', Auth::id())
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),

                Select::make('to_account_id')
                    ->label('To Account')
                    ->options(
                        Account::where('user_id', Auth::id())
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->different('from_account_id'),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->prefix('$'),

                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Transfer')
                ->action('create'),
        ];
    }

    public function create(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            // Create outgoing transaction (from account)
            Transaction::create([
                'user_id' => Auth::id(),
                'account_id' => $data['from_account_id'],
                'amount' => -abs($data['amount']), // Negative for outgoing
                'description' => $data['description'] ?: 'Transfer out',
                'type' => 'expense',
                'date' => now(),
            ]);

            // Create incoming transaction (to account)
            Transaction::create([
                'user_id' => Auth::id(),
                'account_id' => $data['to_account_id'],
                'amount' => abs($data['amount']), // Positive for incoming
                'description' => $data['description'] ?: 'Transfer in',
                'type' => 'income',
                'date' => now(),
            ]);
        });

        Notification::make()
            ->success()
            ->title('Transfer created successfully')
            ->send();

        $this->form->fill([
            'amount' => null,
            'from_account_id' => null,
            'to_account_id' => null,
            'description' => '',
        ]);
    }
}

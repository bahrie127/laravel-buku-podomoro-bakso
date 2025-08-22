<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Transactions\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FinancialOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Auth::id();
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Current month income
        $currentMonthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        // Current month expenses
        $currentMonthExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        // Net profit this month
        $netProfit = $currentMonthIncome - $currentMonthExpenses;

        // Last month income for comparison
        $lastMonthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$lastMonth, $currentMonth])
            ->sum('amount');

        // Last month expenses for comparison
        $lastMonthExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$lastMonth, $currentMonth])
            ->sum('amount');

        // Calculate changes
        $incomeChange = $lastMonthIncome > 0
            ? (($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100
            : 0;

        $expenseChange = $lastMonthExpenses > 0
            ? (($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100
            : 0;

        // Calculate total current balance across all active accounts
        $totalCurrentBalance = Account::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->sum(function ($account) {
                return $account->getCurrentBalance();
            });

        return [
            Stat::make('Income This Month', 'Rp ' . number_format($currentMonthIncome, 0, ',', '.'))
                ->description(($incomeChange >= 0 ? '+' : '') . number_format($incomeChange, 1) . '% from last month')
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger')
                ->url(TransactionResource::getUrl('index', ['tableFilters' => ['type' => ['value' => 'income']]])),

            Stat::make('Expenses This Month', 'Rp ' . number_format($currentMonthExpenses, 0, ',', '.'))
                ->description(($expenseChange >= 0 ? '+' : '') . number_format($expenseChange, 1) . '% from last month')
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseChange >= 0 ? 'danger' : 'success')
                ->url(TransactionResource::getUrl('index', ['tableFilters' => ['type' => ['value' => 'expense']]])),

            Stat::make('Net Profit This Month', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                ->description($netProfit >= 0 ? 'Profit this month' : 'Loss this month')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netProfit >= 0 ? 'success' : 'danger')
                ->url(TransactionResource::getUrl('index')),

            Stat::make('Total Balance', 'Rp ' . number_format($totalCurrentBalance, 0, ',', '.'))
                ->description('Current total across all accounts')
                ->descriptionIcon('heroicon-m-wallet')
                ->color($totalCurrentBalance >= 0 ? 'success' : 'danger')
                ->url(TransactionResource::getUrl('index')),
        ];
    }
}

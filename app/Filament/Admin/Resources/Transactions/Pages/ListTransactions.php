<?php

namespace App\Filament\Admin\Resources\Transactions\Pages;

use App\Filament\Admin\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadReport')
                ->label('Download Report')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('success')
                ->action('downloadPdfReport'),
            CreateAction::make(),
        ];
    }

    public function downloadPdfReport(): \Symfony\Component\HttpFoundation\Response
    {
        // Get all transactions for current user
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary data
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = abs($transactions->where('type', 'expense')->sum('amount'));
        $netBalance = $totalIncome - $totalExpenses;

        // Get date range
        $startDate = $transactions->min('date')?->format('M d, Y') ?? 'No transactions';
        $endDate = $transactions->max('date')?->format('M d, Y') ?? 'No transactions';

        // Generate PDF
        $pdf = Pdf::loadView('reports.transactions-pdf', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netBalance' => $netBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $filename = 'transactions-report-' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}

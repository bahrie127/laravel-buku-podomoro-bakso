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
            CreateAction::make()
                ->label('Buat Transaksi'),
            Action::make('downloadReport')
                ->label('Unduh Laporan')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('success')
                ->action('downloadPdfReport'),
        ];
    }

    public function downloadPdfReport(): \Symfony\Component\HttpFoundation\Response
    {
        // Get filter values from table
        $tableFilters = $this->getTableFilters();

        // Build query with current user
        $query = Transaction::where('user_id', Auth::id())
            ->with(['account', 'category']);

        // Apply date range filter if exists
        if (isset($tableFilters['date_range']) && is_array($tableFilters['date_range'])) {
            if (isset($tableFilters['date_range']['from']) && $tableFilters['date_range']['from']) {
                $query->whereDate('date', '>=', $tableFilters['date_range']['from']);
            }

            if (isset($tableFilters['date_range']['to']) && $tableFilters['date_range']['to']) {
                $query->whereDate('date', '<=', $tableFilters['date_range']['to']);
            }
        }

        // Apply type filter if exists
        if (isset($tableFilters['type']) && $tableFilters['type']) {
            $query->where('type', $tableFilters['type']);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        // Calculate summary data
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = abs($transactions->where('type', 'expense')->sum('amount'));
        $netBalance = $totalIncome - $totalExpenses;

        // Get date range for display
        $startDate = 'Semua periode';
        $endDate = 'Semua periode';

        // If date filter is applied, use filter dates
        if (isset($tableFilters['date_range']) && is_array($tableFilters['date_range'])) {
            if (isset($tableFilters['date_range']['from']) && $tableFilters['date_range']['from']) {
                $startDate = \Carbon\Carbon::parse($tableFilters['date_range']['from'])->format('d M Y');
            }
            if (isset($tableFilters['date_range']['to']) && $tableFilters['date_range']['to']) {
                $endDate = \Carbon\Carbon::parse($tableFilters['date_range']['to'])->format('d M Y');
            }
        }

        // If no filter applied, show actual date range from data
        if ($startDate === 'Semua periode' && $transactions->count() > 0) {
            $startDate = $transactions->min('date')?->format('d M Y') ?? 'Tidak ada transaksi';
            $endDate = $transactions->max('date')?->format('d M Y') ?? 'Tidak ada transaksi';
        }

        // Generate PDF
        $pdf = Pdf::loadView('reports.transactions-pdf', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netBalance' => $netBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $dateRange = '';
        if ($startDate !== 'Semua periode') {
            $dateRange = '-' . \Carbon\Carbon::parse($tableFilters['date_range']['from'] ?? now())->format('Y-m-d');
            if (isset($tableFilters['date_range']['to'])) {
                $dateRange .= '-sampai-' . \Carbon\Carbon::parse($tableFilters['date_range']['to'])->format('Y-m-d');
            }
        }

        $filename = 'laporan-transaksi' . $dateRange . '-' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}

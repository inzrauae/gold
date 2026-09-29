<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\PriceRepository;

class HistoryController
{
    private PriceRepository $repository;

    public function __construct()
    {
        $this->repository = new PriceRepository();
    }

    public function index(Request $request): void
    {
        $years = $this->repository->historyYears();
        $recent = $this->repository->historyBetween(date('Y-m-d', strtotime('-90 days')), date('Y-m-d'));

        Response::html(View::layout('history-index', [
            'title' => 'Gold Price History - Sri Lanka Daily Archive',
            'description' => 'Daily archive of verified gold prices in Sri Lanka, organised by year and month.',
            'canonical' => '/gold-price-history',
            'years' => $years,
            'recent' => array_reverse($recent),
            'firstDate' => $this->repository->firstHistoryDate(),
        ]));
    }

    public function year(Request $request, string $year): void
    {
        if (!ctype_digit($year)) {
            Response::notFound();
            return;
        }

        $rows = $this->repository->historyBetween("{$year}-01-01", "{$year}-12-31");

        Response::html(View::layout('history-year', [
            'title' => "Gold Price History {$year} - Sri Lanka",
            'description' => "Monthly gold price history for {$year} in Sri Lanka.",
            'canonical' => "/gold-price-history/{$year}",
            'year' => (int) $year,
            'rows' => $rows,
        ]));
    }

    public function month(Request $request, string $year, string $month): void
    {
        if (!ctype_digit($year) || !ctype_digit($month) || (int) $month < 1 || (int) $month > 12) {
            Response::notFound();
            return;
        }

        $rows = $this->repository->historyForMonth((int) $year, (int) $month);
        $monthName = date('F', mktime(0, 0, 0, (int) $month, 1));

        Response::html(View::layout('history-month', [
            'title' => "Gold Price History {$monthName} {$year} - Sri Lanka",
            'description' => "Daily gold price history for {$monthName} {$year} in Sri Lanka.",
            'canonical' => "/gold-price-history/{$year}/{$month}",
            'year' => (int) $year,
            'month' => (int) $month,
            'monthName' => $monthName,
            'rows' => array_reverse($rows),
        ]));
    }
}

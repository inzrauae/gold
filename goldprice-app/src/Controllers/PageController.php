<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\Content;
use App\Services\NewsService;
use App\Services\PriceRepository;
use App\Services\Scheduler;

class PageController
{
    private PriceRepository $repository;

    public function __construct()
    {
        $this->repository = new PriceRepository();
    }

    public function home(Request $request): void
    {
        $latest = $this->repository->latest();
        $chart = $this->chartData();

        Response::html(View::layout('home', [
            'title' => 'Gold Price Today Sri Lanka - Live 24K, 22K, 21K, 18K Rates',
            'description' => 'Live, verified gold prices in Sri Lanka for 24K, 22K, 21K and 18K gold, per gram, per 8g pawn and per troy ounce, with history and a calculator.',
            'canonical' => '/',
            'latest' => $latest,
            'chart' => $chart,
            'marketOpen' => (new Scheduler())->isMarketOpen(),
            'news' => $this->safeNews(),
            'faq' => Content::faq(),
        ]));
    }

    public function todayBreakdown(Request $request): void
    {
        $latest = $this->repository->latest();

        Response::html(View::layout('today-breakdown', [
            'title' => 'Gold Price Today in Sri Lanka - Step by Step Breakdown',
            'description' => 'How today\'s gold price in Sri Lanka is calculated, with the live inputs and the latest updates.',
            'canonical' => '/gold-price-today-sri-lanka',
            'latest' => $latest,
            'steps' => Content::methodSteps(),
            'logs' => $this->repository->recentLogs(10),
        ]));
    }

    public function purityOrWeight(Request $request, string $slug): void
    {
        $karatMap = ['24k' => 24, '22k' => 22, '21k' => 21, '18k' => 18];
        if (isset($karatMap[$slug])) {
            $this->purity($request, $slug, $karatMap[$slug]);
            return;
        }

        $units = ['per-gram' => 'gram', 'per-8-grams' => '8g'];
        if (isset($units[$slug])) {
            $this->weight($request, $slug, $units[$slug]);
            return;
        }

        Response::notFound();
    }

    private function purity(Request $request, string $karat, int $k): void
    {
        $latest = $this->repository->latest();

        Response::html(View::layout('purity', [
            'title' => "{$k}K Gold Price Today in Sri Lanka",
            'description' => "Live {$k}K gold price in Sri Lanka per gram, per 8g pawn and per troy ounce.",
            'canonical' => "/gold-price-{$karat}-sri-lanka",
            'karat' => $k,
            'latest' => $latest,
        ]));
    }

    private function weight(Request $request, string $unit, string $u): void
    {
        Response::html(View::layout('weight', [
            'title' => $u === 'gram' ? 'Gold Price Per Gram in Sri Lanka' : 'Gold Price Per 8 Grams (Pawn) in Sri Lanka',
            'description' => 'Live gold price table by weight for every karat sold in Sri Lanka.',
            'canonical' => "/gold-price-{$unit}-sri-lanka",
            'unit' => $u,
            'latest' => $this->repository->latest(),
        ]));
    }

    public function dataSources(Request $request): void
    {
        $latest = $this->repository->latest();

        Response::html(View::layout('data-sources', [
            'title' => 'Data Sources & Methodology - Gold Price Today Sri Lanka',
            'description' => 'Where our gold price and USD/LKR exchange rate data comes from, and how every price is verified before publication.',
            'canonical' => '/data-sources',
            'latest' => $latest,
            'steps' => Content::methodSteps(),
        ]));
    }

    public function widgetBuilder(Request $request): void
    {
        Response::html(View::layout('widget-builder', [
            'title' => 'Free Gold Price Widget for Your Website',
            'description' => 'Embed a live, self-updating gold price widget on your website in one line of code.',
            'canonical' => '/gold-price-widget',
        ]));
    }

    public function widgetGuide(Request $request): void
    {
        Response::html(View::layout('widget-guide', [
            'title' => 'Gold Price Widget - How To Use & Terms',
            'description' => 'How to install the gold price widget, customise it, and the terms of use.',
            'canonical' => '/gold-price-widget/how-to-use',
        ]));
    }

    public function apiDocs(Request $request): void
    {
        Response::html(View::layout('api-docs', [
            'title' => 'Free Gold Price JSON API - Gold Price Today Sri Lanka',
            'description' => 'Free JSON API for verified Sri Lankan gold prices: current price and history.',
            'canonical' => '/gold-price-api',
        ]));
    }

    private function chartData(int $days = 90): array
    {
        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime("-{$days} days"));
        return $this->repository->historyBetween($from, $to);
    }

    private function safeNews(): array
    {
        try {
            return (new NewsService())->recent();
        } catch (\Throwable) {
            return [];
        }
    }
}

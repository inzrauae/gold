<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\PriceRepository;
use App\Services\PriceUpdater;
use App\Services\ProviderGateway;

class AdminController
{
    private PriceRepository $repository;

    public function __construct()
    {
        $this->repository = new PriceRepository();
    }

    public function login(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/admin');
            return;
        }

        if ($request->method === 'POST') {
            if (!Csrf::verify($request->input('_csrf'))) {
                Response::html(View::layout('admin/login', ['title' => 'Admin Login', 'error' => 'Session expired, please try again.']), 400);
                return;
            }

            if (!RateLimiter::attempt('login-attempt:' . $request->ip(), 10, 300)) {
                Response::html(View::layout('admin/login', ['title' => 'Admin Login', 'error' => 'Too many attempts. Try again later.']), 429);
                return;
            }

            $email = (string) $request->input('email', '');
            $password = (string) $request->input('password', '');

            if (Auth::attempt($email, $password)) {
                Response::redirect('/admin');
                return;
            }

            Response::html(View::layout('admin/login', ['title' => 'Admin Login', 'error' => 'Invalid email or password.']), 401);
            return;
        }

        Response::html(View::layout('admin/login', ['title' => 'Admin Login']));
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/admin/login');
    }

    public function dashboard(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/admin/login');
            return;
        }

        $gateway = new ProviderGateway();

        Response::html(View::layout('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'user' => Auth::user(),
            'latest' => $this->repository->latest(),
            'logs' => $this->repository->recentLogs(30),
            'pending' => $this->repository->unresolvedPendingChanges(),
            'sources' => [
                'Gold primary' => $gateway->goldPrimary(),
                'Gold secondary' => $gateway->goldSecondary(),
                'FX primary' => $gateway->fxPrimary(),
                'FX secondary' => $gateway->fxSecondary(),
            ],
            'recentLogLines' => Logger::tail(50),
        ]));
    }

    public function updateNow(Request $request): void
    {
        if (!Auth::check() || !Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/admin');
            return;
        }

        (new PriceUpdater())->run();
        Response::redirect('/admin');
    }

    public function acceptChange(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/admin');
            return;
        }

        $pending = $this->repository->find((int) $id);
        if ($pending) {
            $payload = json_decode($pending['payload_json'], true) ?: [];
            (new PriceUpdater())->acceptPendingChange($payload);
            $this->repository->resolvePendingChange((int) $id);
        }

        Response::redirect('/admin');
    }
}

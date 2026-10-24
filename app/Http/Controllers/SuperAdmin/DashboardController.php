<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\SubscriptionPayment;
use App\Services\Platform\SuperAdminMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected SuperAdminMetricsService $metricsService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $dateRange = (string) $request->input('date_range', 'all');

        $stats = $this->metricsService->getDashboardStatistics();
        $paymentAnalytics = $this->metricsService->getPaymentAnalytics($dateRange);
        $recentShops = $this->metricsService->getRecentRegistrations(8);
        $expiringSoon = $this->metricsService->getSubscriptionExpiryAlerts(7);
        $recentActivities = $this->metricsService->getRecentActivities(10);

        // Quick table listing of recent raw payments
        $recentPayments = SubscriptionPayment::with(['shop', 'plan'])
            ->latest()
            ->limit(8)
            ->get();

        $data = [
            // Primary KPI stats (structured & backwards-compatible keys)
            'stats' => $stats,
            'total_shops' => $stats['total_shops'],
            'active_subscriptions' => $stats['active_subscriptions'],
            'trial_shops' => $stats['trial_shops'],
            'expired_subscriptions' => $stats['expired_subscriptions'],
            'pending_approvals' => $stats['pending_payments_count'],
            'pending_payments_count' => $stats['pending_payments_count'],
            'pending_payments_amount' => $stats['pending_payments_amount'],
            'mrr' => $stats['mrr'],
            'cash_received_this_month' => $stats['cash_received_this_month'],

            // Sections
            'payment_analytics' => $paymentAnalytics,
            'recent_shops' => $recentShops,
            'recent_payments' => $recentPayments,
            'expiring_soon' => $expiringSoon,
            'recent_activities' => $recentActivities,
            'date_range' => $dateRange,
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('super-admin.dashboard.index', $data);
    }
}

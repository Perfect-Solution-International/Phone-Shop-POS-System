<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isOwnerOrAdmin(), 403, 'Unauthorized. Shop settings are only accessible by Shop Owner / Administrator.');

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::with('setting')->findOrFail($shopId);
        $setting = $shop->setting ?? Setting::firstOrCreate(['shop_id' => $shop->id], ['timezone' => 'Asia/Colombo', 'currency' => 'LKR']);

        return view('shop.settings.index', compact('shop', 'setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isOwnerOrAdmin(), 403, 'Only shop owners and administrators can modify shop settings.');

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_reg_number' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'max:5'],
            'timezone' => ['required', 'string', 'max:50'],
            'invoice_header_note' => ['nullable', 'string', 'max:1000'],
            'invoice_footer_note' => ['nullable', 'string', 'max:1000'],
            'warranty_terms' => ['nullable', 'string', 'max:2000'],
        ]);

        $shop->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'business_reg_number' => $data['business_reg_number'] ?? null,
            'country' => $data['country'] ?? 'Sri Lanka',
            'invoice_header_note' => $data['invoice_header_note'] ?? null,
            'invoice_footer_note' => $data['invoice_footer_note'] ?? null,
            'warranty_terms' => $data['warranty_terms'] ?? null,
        ]);

        Setting::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'currency' => $data['currency'],
                'timezone' => $data['timezone'],
            ]
        );

        return redirect()->route('shop.settings')->with('success', 'Shop settings and invoice profile saved successfully.');
    }
}


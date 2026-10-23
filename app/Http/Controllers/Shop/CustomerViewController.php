<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('has_balance')) {
            $query->where('balance', '>', 0);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('shop.customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'nic' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Customer::create($validated);

        return redirect()->route('shop.customers')->with('success', 'Customer profile registered successfully!');
    }
}


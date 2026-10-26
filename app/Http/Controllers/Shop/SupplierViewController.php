<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();
        $totalBalanceOwed = (float) Supplier::sum('balance');

        return view('shop.suppliers.index', compact('suppliers', 'totalBalanceOwed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['balance'] = $data['balance'] ?? 0.00;

        Supplier::create($data);

        return redirect()->route('shop.suppliers')->with('success', "Supplier '{$data['name']}' registered successfully.");
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $supplier->update($data);

        return redirect()->route('shop.suppliers')->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('shop.suppliers')->with('success', "Supplier '{$name}' deleted successfully.");
    }
}


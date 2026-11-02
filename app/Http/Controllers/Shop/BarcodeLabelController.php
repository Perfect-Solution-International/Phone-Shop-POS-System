<?php

namespace App\Services;

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Services\BarcodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeLabelController extends Controller
{
    public function __construct(
        protected BarcodeGeneratorService $barcodeService
    ) {}

    public function index(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::find($shopId);

        $phones = PhoneDevice::where('status', 'in_stock')
            ->latest()
            ->take(50)
            ->get();

        $products = Product::latest()
            ->take(50)
            ->get();

        $selectedType = $request->input('type', 'phone');
        $selectedId = $request->input('id');
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) $quantity = 1;
        if ($quantity > 100) $quantity = 100;

        $template = $request->input('template', 'thermal_50x30'); // thermal_50x30, thermal_40x25, sheet_a4

        $labelData = null;
        if ($selectedType === 'phone') {
            $phone = $selectedId ? PhoneDevice::find($selectedId) : $phones->first();
            if ($phone) {
                $code = $phone->imei_1 ?: ($phone->imei1 ?: ($phone->imei_2 ?: 'PHONE-' . $phone->id));
                $brandName = is_object($phone->brand) ? $phone->brand->name : (string) $phone->brand;
                $labelData = [
                    'type' => 'phone',
                    'id' => $phone->id,
                    'title' => trim(($brandName ? $brandName . ' ' : '') . $phone->model),
                    'subtitle' => trim(($phone->storage ? $phone->storage . ' ' : '') . ($phone->color ? $phone->color . ' ' : '') . ($phone->condition ?? '')),
                    'code' => $code,
                    'barcode_svg' => $this->barcodeService->generateCode128Svg($code, 38, 1.4),
                    'price' => $phone->selling_price,
                    'secondary_code' => ($phone->imei_2 ?? $phone->imei2) ? 'IMEI2: ' . ($phone->imei_2 ?? $phone->imei2) : null,
                ];
            }
        } else {
            $product = $selectedId ? Product::find($selectedId) : $products->first();
            if ($product) {
                $code = $product->barcode ?: ($product->code ?: 'PRD-' . $product->id);
                $labelData = [
                    'type' => 'product',
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => $product->category ? $product->category->name : ($product->brand ? $product->brand->name : 'Accessory'),
                    'code' => $code,
                    'barcode_svg' => $this->barcodeService->generateCode128Svg($code, 38, 1.4),
                    'price' => $product->retail_price ?? $product->selling_price,
                    'secondary_code' => $product->sku ? 'SKU: ' . $product->sku : null,
                ];
            }
        }

        return view('shop.labels.index', compact(
            'shop',
            'phones',
            'products',
            'selectedType',
            'selectedId',
            'quantity',
            'template',
            'labelData'
        ));
    }

    public function print(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::find($shopId);

        $type = $request->input('type', 'phone');
        $id = $request->input('id');
        $quantity = max(1, min(200, (int) $request->input('quantity', 1)));
        $template = $request->input('template', 'thermal_50x30');

        $labelData = null;
        if ($type === 'phone') {
            $phone = PhoneDevice::findOrFail($id);
            $code = $phone->imei_1 ?: ($phone->imei1 ?: ($phone->imei_2 ?: 'PHONE-' . $phone->id));
            $brandName = is_object($phone->brand) ? $phone->brand->name : (string) $phone->brand;
            $labelData = [
                'type' => 'phone',
                'title' => trim(($brandName ? $brandName . ' ' : '') . $phone->model),
                'subtitle' => trim(($phone->storage ? $phone->storage . ' ' : '') . ($phone->color ? $phone->color . ' ' : '') . ($phone->condition ?? '')),
                'code' => $code,
                'barcode_svg' => $this->barcodeService->generateCode128Svg($code, 36, 1.3),
                'price' => $phone->selling_price,
                'secondary_code' => ($phone->imei_2 ?? $phone->imei2) ? 'IMEI2: ' . ($phone->imei_2 ?? $phone->imei2) : null,
            ];
        } else {
            $product = Product::findOrFail($id);
            $code = $product->barcode ?: ($product->code ?: 'PRD-' . $product->id);
            $labelData = [
                'type' => 'product',
                'title' => $product->name,
                'subtitle' => $product->category ? $product->category->name : ($product->brand ? $product->brand->name : 'Accessory'),
                'code' => $code,
                'barcode_svg' => $this->barcodeService->generateCode128Svg($code, 36, 1.3),
                'price' => $product->retail_price ?? $product->selling_price,
                'secondary_code' => $product->sku ? 'SKU: ' . $product->sku : null,
            ];
        }

        return view('shop.labels.print', compact('shop', 'labelData', 'quantity', 'template'));
    }
}

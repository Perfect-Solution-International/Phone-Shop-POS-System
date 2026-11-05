<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');
});

test('shop owner can create a product with an uploaded image', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Retail']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Chargers', 'slug' => 'chargers']);
    $brand = Brand::create(['shop_id' => $shop->id, 'name' => 'Anker', 'slug' => 'anker']);

    $image = UploadedFile::fake()->create('anker-charger.jpg', 150, 'image/jpeg');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Anker 30W GaN Charger',
            'category_id' => $cat->id,
            'brand_id' => $brand->id,
            'cost_price' => 4500,
            'selling_price' => 6800,
            'stock_quantity' => 15,
            'alert_quantity' => 4,
            'image' => $image,
        ]);

    $response->assertRedirect(route('shop.products'));

    $product = Product::withoutGlobalScopes()->where('name', 'Anker 30W GaN Charger')->first();
    expect($product)->not->toBeNull();
    expect($product->image_path)->not->toBeNull();
    expect(Storage::disk('public')->exists($product->image_path))->toBeTrue();
    expect($product->image_url)->toContain('storage/' . $product->image_path);
});

test('shop owner can update product and replace its image', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Cables', 'slug' => 'cables']);

    // Create product with initial image
    $oldFile = UploadedFile::fake()->create('old-cable.jpg', 120, 'image/jpeg');
    $oldPath = $oldFile->store('products', 'public');

    $product = Product::create([
        'shop_id' => $shop->id,
        'category_id' => $cat->id,
        'name' => 'Type-C Braided Cable',
        'code' => 'CBL-01',
        'cost_price' => 1200,
        'retail_price' => 2000,
        'stock_quantity' => 20,
        'image_path' => $oldPath,
    ]);

    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();

    // Upload replacement image
    $newFile = UploadedFile::fake()->create('new-cable.png', 140, 'image/png');

    $response = $this->actingAs($owner, 'web')
        ->put(route('shop.products.update', $product), [
            'name' => 'Type-C Braided Cable 2M',
            'category_id' => $cat->id,
            'cost_price' => 1400,
            'selling_price' => 2400,
            'stock_quantity' => 25,
            'image' => $newFile,
        ]);

    $response->assertRedirect(route('shop.products'));

    $fresh = Product::withoutGlobalScopes()->find($product->id);
    expect($fresh->name)->toBe('Type-C Braided Cable 2M');
    expect($fresh->image_path)->not->toBe($oldPath);
    expect(Storage::disk('public')->exists($fresh->image_path))->toBeTrue();
    // Old file must be cleaned up from storage
    expect(Storage::disk('public')->exists($oldPath))->toBeFalse();
});

test('shop owner can remove product image without deleting product', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Cases', 'slug' => 'cases']);
    $file = UploadedFile::fake()->create('case.jpg', 100, 'image/jpeg');
    $path = $file->store('products', 'public');

    $product = Product::create([
        'shop_id' => $shop->id,
        'category_id' => $cat->id,
        'name' => 'Silicone Case iPhone 15',
        'code' => 'CSE-15',
        'cost_price' => 2000,
        'retail_price' => 3500,
        'stock_quantity' => 10,
        'image_path' => $path,
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $response = $this->actingAs($owner, 'web')
        ->deleteJson(route('shop.products.delete-image', $product));

    $response->assertOk()
        ->assertJsonPath('success', true);

    $fresh = Product::withoutGlobalScopes()->find($product->id);
    expect($fresh->image_path)->toBeNull();
    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('deleting a product also cleans up its image file from storage', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Audio', 'slug' => 'audio']);
    $file = UploadedFile::fake()->create('headphone.jpg', 110, 'image/jpeg');
    $path = $file->store('products', 'public');

    $product = Product::create([
        'shop_id' => $shop->id,
        'category_id' => $cat->id,
        'name' => 'Wireless Headset',
        'code' => 'WHD-01',
        'cost_price' => 5000,
        'retail_price' => 8500,
        'stock_quantity' => 8,
        'image_path' => $path,
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $response = $this->actingAs($owner, 'web')
        ->delete(route('shop.products.destroy', $product));

    $response->assertRedirect(route('shop.products'));

    expect(Product::withoutGlobalScopes()->find($product->id))->toBeNull();
    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('shop cannot edit or delete products belonging to another tenant', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Shop Owner');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $catB = Category::create(['shop_id' => $shopB->id, 'name' => 'Shop B Cat', 'slug' => 'shop-b-cat']);

    $productB = Product::create([
        'shop_id' => $shopB->id,
        'category_id' => $catB->id,
        'name' => 'Shop B Secret Product',
        'code' => 'SB-01',
        'cost_price' => 1000,
        'retail_price' => 2000,
        'stock_quantity' => 5,
    ]);

    // Shop A attempts to delete Shop B product
    $response = $this->actingAs($ownerA, 'web')
        ->delete(route('shop.products.destroy', $productB));

    $response->assertNotFound();
    expect(Product::withoutGlobalScopes()->find($productB->id))->not->toBeNull();
});

test('shop owner can create product without category and it auto-defaults to General Accessories', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Fast USB-C Cable 1M',
            'category_id' => '', // blank category
            'cost_price' => 350,
            'selling_price' => 850,
            'stock_quantity' => 25,
        ]);

    $response->assertRedirect(route('shop.products'));

    $product = Product::withoutGlobalScopes()->where('name', 'Fast USB-C Cable 1M')->first();
    expect($product)->not->toBeNull();
    $category = Category::withoutGlobalScopes()->find($product->category_id);
    expect($category)->not->toBeNull();
    expect($category->name)->toBe('General Accessories');
    expect($product->stock_quantity)->toBe(25);
    expect((float) $product->cost_price)->toBe(350.0);
    expect((float) $product->retail_price)->toBe(850.0);
});

test('shop owner can create product with on-the-fly new category name', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Clear Case iPhone 15',
            'new_category' => 'Protective Back Covers',
            'cost_price' => 400,
            'selling_price' => 1200,
            'stock_quantity' => 12,
        ]);

    $response->assertRedirect(route('shop.products'));

    $product = Product::withoutGlobalScopes()->where('name', 'Clear Case iPhone 15')->first();
    expect($product)->not->toBeNull();
    $category = Category::withoutGlobalScopes()->find($product->category_id);
    expect($category)->not->toBeNull();
    expect($category->name)->toBe('Protective Back Covers');
});

test('shop owner can register and update product with supplier and view supplier relationship', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    app()->instance('shop.id', $shop->id);
    $supplier = \App\Models\Supplier::create([
        'shop_id' => $shop->id,
        'name' => 'Lanka Accessories Hub',
        'phone' => '0771234567',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Baseus 65W GaN Charger',
            'cost_price' => 5000,
            'selling_price' => 7500,
            'stock_quantity' => 20,
            'supplier_id' => $supplier->id,
        ]);

    $response->assertRedirect(route('shop.products'));

    $product = Product::withoutGlobalScopes()->where('name', 'Baseus 65W GaN Charger')->first();
    expect($product)->not->toBeNull();
    expect($product->supplier_id)->toBe($supplier->id);

    // Verify supplier is displayed in view
    $viewResponse = $this->actingAs($owner, 'web')->get(route('shop.products'));
    $viewResponse->assertOk();
    $viewResponse->assertSee('Lanka Accessories Hub');

    app()->instance('shop.id', $shop->id);
    $supplier2 = \App\Models\Supplier::create([
        'shop_id' => $shop->id,
        'name' => 'Global Tech Importers',
        'phone' => '0719876543',
    ]);
    app()->forgetInstance('shop.id');

    $updateResponse = $this->actingAs($owner, 'web')
        ->put(route('shop.products.update', $product), [
            'name' => 'Baseus 65W GaN Charger Pro',
            'category_id' => $product->category_id,
            'cost_price' => 5200,
            'selling_price' => 7800,
            'stock_quantity' => 25,
            'supplier_id' => $supplier2->id,
        ]);

    $updateResponse->assertRedirect(route('shop.products'));
    expect($product->fresh()->supplier_id)->toBe($supplier2->id);

    $viewResponse2 = $this->actingAs($owner, 'web')->get(route('shop.products'));
    $viewResponse2->assertOk();
    $viewResponse2->assertSee('Global Tech Importers');
});

test('shop owner can type supplier name directly when creating product', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Anker PowerBank 20000mAh',
            'cost_price' => 12000,
            'selling_price' => 16500,
            'stock_quantity' => 10,
            'supplier_name' => 'Direct Import Hub Colombo',
        ]);

    $response->assertRedirect(route('shop.products'));

    $supplier = \App\Models\Supplier::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('name', 'Direct Import Hub Colombo')
        ->first();
    expect($supplier)->not->toBeNull();

    $product = Product::withoutGlobalScopes()->where('name', 'Anker PowerBank 20000mAh')->first();
    expect($product)->not->toBeNull();
    expect($product->supplier_id)->toBe($supplier->id);
});

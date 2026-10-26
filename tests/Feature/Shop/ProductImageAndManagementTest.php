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

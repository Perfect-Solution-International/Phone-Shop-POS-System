<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('shop owner can add a new category with tenant isolation', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->postJson(route('shop.categories.store'), [
            'name' => 'Smartwatches',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('category.name', 'Smartwatches');

    $category = Category::withoutGlobalScopes()->where('name', 'Smartwatches')->first();
    expect($category)->not->toBeNull();
    expect($category->shop_id)->toBe($shop->id);
    expect($category->slug)->toBe('smartwatches');
});

test('shop owner can add a new brand with tenant isolation', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->postJson(route('shop.brands.store'), [
            'name' => 'Google Pixel',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('brand.name', 'Google Pixel');

    $brand = Brand::withoutGlobalScopes()->where('name', 'Google Pixel')->first();
    expect($brand)->not->toBeNull();
    expect($brand->shop_id)->toBe($shop->id);
    expect($brand->slug)->toBe('google-pixel');
});

test('submitting existing category name returns existing without database error', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    Category::create(['shop_id' => $shop->id, 'name' => 'Tablets', 'slug' => 'tablets']);

    $response = $this->actingAs($owner, 'web')
        ->postJson(route('shop.categories.store'), [
            'name' => 'Tablets',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('category.name', 'Tablets');

    expect(Category::withoutGlobalScopes()->where('shop_id', $shop->id)->where('name', 'Tablets')->count())->toBe(1);
});

test('shop owner can initialize standard phone shop catalog', function (): void {
    $shop = Shop::factory()->create(['name' => 'Quick Phone Care']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.catalog.seed-defaults'));

    $response->assertRedirect();

    expect(Category::withoutGlobalScopes()->where('shop_id', $shop->id)->where('name', 'Smartphones')->exists())->toBeTrue();
    expect(Category::withoutGlobalScopes()->where('shop_id', $shop->id)->where('name', 'Chargers & Cables')->exists())->toBeTrue();
    expect(Brand::withoutGlobalScopes()->where('shop_id', $shop->id)->where('name', 'Apple')->exists())->toBeTrue();
    expect(Brand::withoutGlobalScopes()->where('shop_id', $shop->id)->where('name', 'Samsung')->exists())->toBeTrue();
});

test('products can be saved with newly added category and brand', function (): void {
    $shop = Shop::factory()->create(['name' => 'Apex Retail']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Create category and brand with shop context
    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Wearables', 'slug' => 'wearables']);
    $brand = Brand::create(['shop_id' => $shop->id, 'name' => 'Garmin', 'slug' => 'garmin']);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.products.store'), [
            'name' => 'Garmin Forerunner 265',
            'category_id' => $cat->id,
            'brand_id' => $brand->id,
            'cost_price' => 85000,
            'selling_price' => 110000,
            'stock_quantity' => 5,
        ]);

    $response->assertRedirect(route('shop.products'));

    $product = Product::withoutGlobalScopes()->where('name', 'Garmin Forerunner 265')->first();
    expect($product)->not->toBeNull();
    expect($product->category_id)->toBe($cat->id);
    expect($product->brand_id)->toBe($brand->id);
    expect($product->shop_id)->toBe($shop->id);
});

test('shop owner can edit a category name', function (): void {
    $shop = Shop::factory()->create(['name' => 'City Mobile']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Old Category', 'slug' => 'old-category']);

    $response = $this->actingAs($owner, 'web')
        ->putJson(route('shop.categories.update', $cat), [
            'name' => 'Wireless Audio & Speakers',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('category.name', 'Wireless Audio & Speakers');

    $freshCat = Category::withoutGlobalScopes()->find($cat->id);
    expect($freshCat->name)->toBe('Wireless Audio & Speakers');
    expect($freshCat->slug)->toBe('wireless-audio-speakers');
});

test('shop owner can edit a brand name', function (): void {
    $shop = Shop::factory()->create(['name' => 'City Mobile']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $brand = Brand::create(['shop_id' => $shop->id, 'name' => 'Old Brand', 'slug' => 'old-brand']);

    $response = $this->actingAs($owner, 'web')
        ->putJson(route('shop.brands.update', $brand), [
            'name' => 'Sony Xperia',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('brand.name', 'Sony Xperia');

    $freshBrand = Brand::withoutGlobalScopes()->find($brand->id);
    expect($freshBrand->name)->toBe('Sony Xperia');
    expect($freshBrand->slug)->toBe('sony-xperia');
});

test('submitting duplicate category name on update is rejected with 422', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $cat1 = Category::create(['shop_id' => $shop->id, 'name' => 'Chargers', 'slug' => 'chargers']);
    $cat2 = Category::create(['shop_id' => $shop->id, 'name' => 'Cables', 'slug' => 'cables']);

    $response = $this->actingAs($owner, 'web')
        ->putJson(route('shop.categories.update', $cat2), [
            'name' => 'Chargers',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('shop owner can delete a category and associated products are safely unassigned', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    app()->instance('shop.id', $shop->id);
    $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Audio', 'slug' => 'audio']);
    $product = Product::create([
        'shop_id' => $shop->id,
        'category_id' => $cat->id,
        'name' => 'AirPods Pro',
        'code' => 'APP-01',
        'cost_price' => 50000,
        'retail_price' => 65000,
        'stock_quantity' => 4,
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($owner, 'web')
        ->deleteJson(route('shop.categories.destroy', $cat));

    $response->assertOk()
        ->assertJsonPath('success', true);

    // Category is deleted
    expect(Category::withoutGlobalScopes()->find($cat->id))->toBeNull();

    // Product remains intact, category_id set to null
    $freshProduct = Product::withoutGlobalScopes()->find($product->id);
    expect($freshProduct)->not->toBeNull();
    expect($freshProduct->category_id)->toBeNull();
});

test('shop owner can delete a brand and associated products are safely unassigned', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    app()->instance('shop.id', $shop->id);
    $brand = Brand::create(['shop_id' => $shop->id, 'name' => 'Sony', 'slug' => 'sony']);
    $product = Product::create([
        'shop_id' => $shop->id,
        'brand_id' => $brand->id,
        'name' => 'WH-1000XM5',
        'code' => 'SNY-01',
        'cost_price' => 70000,
        'retail_price' => 95000,
        'stock_quantity' => 2,
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($owner, 'web')
        ->deleteJson(route('shop.brands.destroy', $brand));

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect(Brand::withoutGlobalScopes()->find($brand->id))->toBeNull();

    $freshProduct = Product::withoutGlobalScopes()->find($product->id);
    expect($freshProduct)->not->toBeNull();
    expect($freshProduct->brand_id)->toBeNull();
});

test('tenant isolation prevents shop from deleting another shops category', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Shop Owner');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $catB = Category::create(['shop_id' => $shopB->id, 'name' => 'Shop B Exclusive', 'slug' => 'shop-b-excl']);

    $response = $this->actingAs($ownerA, 'web')
        ->delete(route('shop.categories.destroy', $catB));

    // Because of BelongsToTenantScope, $catB is not visible to Shop A -> 404
    $response->assertNotFound();

    expect(Category::withoutGlobalScopes()->find($catB->id))->not->toBeNull();
});


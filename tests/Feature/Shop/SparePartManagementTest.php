<?php

use App\Models\Platform\Shop;
use App\Models\SparePart;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');
});

test('shop owner can add a new spare part with an uploaded image', function (): void {
    $shop = Shop::factory()->create(['name' => 'FastFix Mobile']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $file = UploadedFile::fake()->create('battery.jpg', 120, 'image/jpeg');

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.spare-parts.store'), [
            'name' => 'iPhone 12 Battery Replacement',
            'part_number' => 'BAT-IP12-OEM',
            'quality_type' => 'original',
            'compatible_models' => 'iPhone 12, iPhone 12 Pro',
            'cost_price' => 4500,
            'selling_price' => 8500,
            'stock_quantity' => 12,
            'alert_quantity' => 3,
            'image' => $file,
        ]);

    $response->assertRedirect(route('shop.spare-parts'));

    $part = SparePart::withoutGlobalScopes()->where('part_number', 'BAT-IP12-OEM')->first();
    expect($part)->not->toBeNull();
    expect($part->image_path)->not->toBeNull();
    expect(Storage::disk('public')->exists($part->image_path))->toBeTrue();
    expect($part->image_url)->toContain('storage/' . $part->image_path);
});

test('shop owner can edit a spare part and replace its image', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $oldFile = UploadedFile::fake()->create('old-screen.jpg', 150, 'image/jpeg');
    $oldPath = $oldFile->store('spare_parts', 'public');

    $part = SparePart::create([
        'shop_id' => $shop->id,
        'name' => 'iPhone 13 OLED Display',
        'part_number' => 'DISP-IP13-OLED',
        'quality_type' => 'oem',
        'compatible_models' => 'iPhone 13',
        'cost_price' => 18000,
        'selling_price' => 26500,
        'stock_quantity' => 6,
        'alert_quantity' => 2,
        'image_path' => $oldPath,
    ]);

    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();

    $newFile = UploadedFile::fake()->create('new-screen.png', 180, 'image/png');

    $response = $this->actingAs($owner, 'web')
        ->put(route('shop.spare-parts.update', $part), [
            'name' => 'iPhone 13 OLED Display Assembly (OEM Grade A)',
            'part_number' => 'DISP-IP13-OLED-A',
            'quality_type' => 'original',
            'compatible_models' => 'iPhone 13, iPhone 13 Pro',
            'cost_price' => 19000,
            'selling_price' => 28000,
            'stock_quantity' => 8,
            'alert_quantity' => 2,
            'image' => $newFile,
        ]);

    $response->assertRedirect(route('shop.spare-parts'));

    $fresh = SparePart::withoutGlobalScopes()->find($part->id);
    expect($fresh->name)->toBe('iPhone 13 OLED Display Assembly (OEM Grade A)');
    expect($fresh->quality_type)->toBe('original');
    expect($fresh->image_path)->not->toBe($oldPath);
    expect(Storage::disk('public')->exists($fresh->image_path))->toBeTrue();
    expect(Storage::disk('public')->exists($oldPath))->toBeFalse();
});

test('shop owner can delete a spare part image without deleting the part', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $file = UploadedFile::fake()->create('mic.jpg', 80, 'image/jpeg');
    $path = $file->store('spare_parts', 'public');

    $part = SparePart::create([
        'shop_id' => $shop->id,
        'name' => 'Charging Flex Board',
        'part_number' => 'FLX-S21-CHG',
        'quality_type' => 'compatible',
        'cost_price' => 2500,
        'selling_price' => 4500,
        'stock_quantity' => 10,
        'image_path' => $path,
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $response = $this->actingAs($owner, 'web')
        ->deleteJson(route('shop.spare-parts.delete-image', $part));

    $response->assertOk()
        ->assertJsonPath('success', true);

    $fresh = SparePart::withoutGlobalScopes()->find($part->id);
    expect($fresh->image_path)->toBeNull();
    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('shop owner can delete a spare part and its image is cleaned up', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $file = UploadedFile::fake()->create('speaker.jpg', 90, 'image/jpeg');
    $path = $file->store('spare_parts', 'public');

    $part = SparePart::create([
        'shop_id' => $shop->id,
        'name' => 'Earpiece Speaker',
        'part_number' => 'SPK-IP11',
        'quality_type' => 'original',
        'cost_price' => 1500,
        'selling_price' => 3000,
        'stock_quantity' => 5,
        'image_path' => $path,
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $response = $this->actingAs($owner, 'web')
        ->delete(route('shop.spare-parts.destroy', $part));

    $response->assertRedirect(route('shop.spare-parts'));

    expect(SparePart::withoutGlobalScopes()->find($part->id))->toBeNull();
    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('tenant isolation prevents shop from editing or deleting another shops spare part', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Shop Owner');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $partB = SparePart::create([
        'shop_id' => $shopB->id,
        'name' => 'Shop B Camera Module',
        'part_number' => 'CAM-B-01',
        'quality_type' => 'original',
        'cost_price' => 9000,
        'selling_price' => 15000,
        'stock_quantity' => 4,
    ]);

    $response = $this->actingAs($ownerA, 'web')
        ->delete(route('shop.spare-parts.destroy', $partB));

    $response->assertNotFound();
    expect(SparePart::withoutGlobalScopes()->find($partB->id))->not->toBeNull();
});


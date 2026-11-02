<?php

use App\Mail\PayrollReady;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

function hrmAdministrator(): User
{
    test()->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Administrator');

    return $user;
}

function hrmPayload(string $resource, int $employeeId): array
{
    return match ($resource) {
        'employees' => ['employee_code' => 'EMP-100', 'name' => 'Asha Perera', 'email' => 'asha@example.test', 'joined_on' => '2026-10-01', 'is_active' => true],
        'attendance' => ['employee_id' => $employeeId, 'work_date' => '2026-10-21', 'clocked_in_at' => '2026-10-21T09:00:00+05:30', 'clocked_out_at' => null],
        'leave' => ['employee_id' => $employeeId, 'starts_on' => '2026-10-22', 'ends_on' => '2026-10-23', 'type' => 'annual', 'status' => 'pending'],
        'payroll' => ['employee_id' => $employeeId, 'period' => '2026-10-01', 'basic_salary' => '50000.10', 'allowances' => '5000.20', 'deductions' => '1000.05', 'status' => 'draft'],
    };
}

test('HRM endpoints reject unauthenticated requests with 401', function (string $resource): void {
    $this->getJson('/api/v1/'.$resource)->assertUnauthorized();
})->with(['employees', 'attendance', 'leave', 'payroll']);

test('unprivileged users cannot list or mutate HRM records', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/employees')->assertForbidden();
    $this->postJson('/api/v1/employees', hrmPayload('employees', 1))->assertForbidden();
    $this->assertDatabaseCount('employees', 0);
});

test('users without a shop assignment are denied access', function (): void {
    $user = hrmAdministrator();
    $user->forceFill(['shop_id' => null])->save();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/employees')->assertForbidden()->assertJsonPath('message', 'A shop assignment is required.');
});

test('authorized users can create read update and delete HRM records', function (string $resource, string $model, string $table): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);
    $payload = hrmPayload($resource, $employee->id);

    $created = $this->postJson('/api/v1/'.$resource, $payload)->assertCreated();
    $id = $created->json('data.id');
    $this->assertDatabaseHas($table, ['id' => $id, 'shop_id' => $user->shop_id]);
    $this->getJson('/api/v1/'.$resource.'/'.$id)->assertOk()->assertJsonPath('data.id', $id);
    $this->putJson('/api/v1/'.$resource.'/'.$id, $payload)->assertOk()->assertJsonPath('data.id', $id);
    $this->deleteJson('/api/v1/'.$resource.'/'.$id)->assertNoContent();

    if ($resource === 'employees') {
        $this->assertSoftDeleted($table, ['id' => $id]);
    } else {
        $this->assertDatabaseMissing($table, ['id' => $id]);
    }
})->with([
    ['employees', Employee::class, 'employees'],
    ['attendance', Attendance::class, 'attendances'],
    ['leave', Leave::class, 'leaves'],
    ['payroll', Payroll::class, 'payrolls'],
]);

test('cross shop records cannot be read changed or deleted', function (string $resource, string $model): void {
    $user = hrmAdministrator();
    $foreign = $model::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/'.$resource.'/'.$foreign->id)->assertNotFound();
    $this->putJson('/api/v1/'.$resource.'/'.$foreign->id, [])->assertNotFound();
    $this->deleteJson('/api/v1/'.$resource.'/'.$foreign->id)->assertNotFound();
    $this->getJson('/api/v1/'.$resource)->assertOk()->assertJsonPath('meta.total', 0);
    $this->assertModelExists($foreign);
})->with([
    ['employees', Employee::class], ['attendance', Attendance::class], ['leave', Leave::class], ['payroll', Payroll::class],
]);

test('cross shop employee references are rejected with 422', function (string $resource): void {
    $user = hrmAdministrator();
    $foreign = Employee::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/'.$resource, hrmPayload($resource, $foreign->id))
        ->assertUnprocessable()->assertJsonValidationErrors('employee_id');
})->with(['attendance', 'leave', 'payroll']);

test('client supplied shop assignments cannot create records in another shop', function (): void {
    $user = hrmAdministrator();
    $shop = Shop::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/employees', [...hrmPayload('employees', 1), 'shop_id' => $shop->id])
        ->assertUnprocessable()->assertJsonValidationErrors('shop_id');
    $this->assertDatabaseCount('employees', 0);
});

test('listing searches and sorts only the current shop with bounded pagination', function (): void {
    $user = hrmAdministrator();
    Employee::factory()->create(['shop_id' => $user->shop_id, 'name' => 'Zara Current']);
    Employee::factory()->create(['shop_id' => $user->shop_id, 'name' => 'Asha Current']);
    Employee::factory()->create(['name' => 'Another Current']);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/employees?search=Current&sort=name&direction=asc&per_page=1')
        ->assertOk()->assertJsonPath('meta.total', 2)->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Asha Current');
    $this->getJson('/api/v1/employees?sort=name%20DESC;DROP%20TABLE%20users')->assertUnprocessable()->assertJsonValidationErrors('sort');
    $this->getJson('/api/v1/employees?per_page=1000')->assertUnprocessable()->assertJsonValidationErrors('per_page');
});

test('employee code and email uniqueness is scoped to a shop', function (): void {
    $user = hrmAdministrator();
    Employee::factory()->create(['employee_code' => 'EMP-100', 'email' => 'asha@example.test']);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/employees', hrmPayload('employees', 1))->assertCreated();
    $this->postJson('/api/v1/employees', hrmPayload('employees', 1))->assertUnprocessable()->assertJsonValidationErrors(['employee_code', 'email']);
});

test('attendance normalizes an explicit timezone offset to UTC and rejects duplicate work days', function (): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);
    $payload = hrmPayload('attendance', $employee->id);

    $response = $this->postJson('/api/v1/attendance', $payload)->assertCreated();
    $this->assertDatabaseHas('attendances', ['id' => $response->json('data.id'), 'clocked_in_at' => '2026-10-21 03:30:00']);
    $this->postJson('/api/v1/attendance', $payload)->assertUnprocessable()->assertJsonValidationErrors('work_date');
});

test('attendance requires a timezone and clock out after clock in', function (): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);
    $payload = hrmPayload('attendance', $employee->id);

    $this->postJson('/api/v1/attendance', [...$payload, 'clocked_in_at' => '2026-10-21T09:00:00'])
        ->assertUnprocessable()->assertJsonValidationErrors('clocked_in_at');
    $this->postJson('/api/v1/attendance', [...$payload, 'clocked_out_at' => '2026-10-21T08:00:00+05:30'])
        ->assertUnprocessable()->assertJsonValidationErrors('clocked_out_at');
});

test('leave cannot end before it starts', function (): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/leave', [...hrmPayload('leave', $employee->id), 'ends_on' => '2026-10-20'])
        ->assertUnprocessable()->assertJsonValidationErrors('ends_on');
    $this->assertDatabaseCount('leaves', 0);
});

test('payroll computes exact decimal net salary on the server', function (): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/payroll', hrmPayload('payroll', $employee->id))->assertCreated()->assertJsonPath('data.net_salary', '54000.25');

    $this->assertDatabaseHas('payrolls', ['id' => $response->json('data.id'), 'net_salary' => '54000.25']);
});

test('payroll rejects forged net amounts invalid precision and excessive deductions', function (array $changes, string $field): void {
    Mail::fake();
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/payroll', [...hrmPayload('payroll', $employee->id), ...$changes])
        ->assertUnprocessable()->assertJsonValidationErrors($field);
    $this->assertDatabaseCount('payrolls', 0);
    Mail::assertNothingQueued();
})->with([
    'forged net' => [['net_salary' => '100000.00'], 'net_salary'],
    'precision' => [['basic_salary' => '1.999'], 'basic_salary'],
    'deductions' => [['deductions' => '99999999.00'], 'deductions'],
    'period' => [['period' => '2026-10-15'], 'period'],
]);

test('ready payroll queues a content snapshot and unchanged ready status does not resend', function (): void {
    Mail::fake();
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Sanctum::actingAs($user);
    $payload = [...hrmPayload('payroll', $employee->id), 'status' => 'ready'];

    $response = $this->postJson('/api/v1/payroll', $payload)->assertCreated();
    $this->putJson('/api/v1/payroll/'.$response->json('data.id'), $payload)->assertOk();

    Mail::assertQueued(PayrollReady::class, fn (PayrollReady $mail) => $mail->hasTo($employee->email)
        && $mail->employeeName === $employee->name && $mail->period === '2026-10');
    Mail::assertQueuedCount(1);
});

test('settings are shop scoped and require permission to change', function (): void {
    $user = hrmAdministrator();
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/settings', ['timezone' => 'Asia/Colombo', 'currency' => 'LKR'])->assertOk()->assertJsonPath('data.timezone', 'Asia/Colombo');
    $this->assertDatabaseHas('settings', ['shop_id' => $user->shop_id, 'timezone' => 'Asia/Colombo']);
    $this->putJson('/api/v1/settings', ['timezone' => 'invalid/time', 'currency' => 'rupees'])->assertUnprocessable()->assertJsonValidationErrors(['timezone', 'currency']);
});

test('viewer can view HR data but cannot change settings or open payroll reports', function (): void {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Viewer');
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/employees')->assertOk();
    $this->getJson('/api/v1/payroll')->assertForbidden();
    $this->getJson('/api/v1/reports/payroll.pdf')->assertForbidden();
    $this->putJson('/api/v1/settings', ['timezone' => 'UTC', 'currency' => 'LKR'])->assertForbidden();
});

test('dashboard aggregates only the current shop and uses its local date', function (): void {
    $this->travelTo(new DateTimeImmutable('2026-10-20T20:00:00Z'));
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id]);
    Attendance::factory()->create(['shop_id' => $user->shop_id, 'employee_id' => $employee->id]);
    Attendance::factory()->create();
    Sanctum::actingAs($user);
    $this->putJson('/api/v1/settings', ['timezone' => 'Asia/Colombo', 'currency' => 'LKR'])->assertOk();

    $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonPath('employees', 1)->assertJsonPath('present_today', 1)->assertJsonPath('timezone', 'Asia/Colombo');
});

test('authorized users can download payroll PDF and spreadsheet reports', function (): void {
    $user = hrmAdministrator();
    $employee = Employee::factory()->create(['shop_id' => $user->shop_id, 'name' => '<script>alert(1)</script>']);
    Payroll::factory()->create(['shop_id' => $user->shop_id, 'employee_id' => $employee->id]);
    Payroll::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/reports/payroll.pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->getJson('/api/v1/reports/payroll.xlsx')->assertOk()->assertDownload('payroll.xlsx');
});

test('shop models return no records without a trusted context', function (): void {
    Employee::factory()->create();

    expect(Employee::count())->toBe(0);
});

test('report data remains scoped outside a request and employee content is escaped', function (): void {
    $shop = Shop::factory()->create();
    $employee = Employee::factory()->create(['shop_id' => $shop->id, 'name' => '<script>alert(1)</script>']);
    Payroll::factory()->create(['shop_id' => $shop->id, 'employee_id' => $employee->id]);
    Payroll::factory()->create();
    $export = new App\Services\Report\PayrollExport($shop->id);

    $payrolls = $export->query()->get();
    $html = view('reports.payroll', ['payrolls' => $payrolls, 'currency' => 'LKR'])->render();

    expect($payrolls)->toHaveCount(1);
    expect($export->map($payrolls->first())[0])->toBe('<script>alert(1)</script>');
    expect($html)->toContain('&lt;script&gt;')->not->toContain('<script>alert(1)</script>');
    $sheet = new PhpOffice\PhpSpreadsheet\Spreadsheet;
    $cell = $sheet->getActiveSheet()->getCell('A1');
    $export->bindValue($cell, '=HYPERLINK("https://example.test")');
    expect($cell->getDataType())->toBe(PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
});

test('administrator provisioning creates a shop role and hashed credentials', function (): void {
    $this->artisan('app:create-administrator', ['email' => 'admin@example.test', '--name' => 'Admin', '--shop' => 'My shop'])
        ->expectsQuestion('Password (at least 12 characters)', 'StrongPassword123!')
        ->expectsOutput('Administrator created.')->assertSuccessful();

    $user = User::where('email', 'admin@example.test')->firstOrFail();
    expect($user->hasRole('Administrator'))->toBeTrue();
    expect($user->shop->name)->toBe('My shop');
    expect(Hash::check('StrongPassword123!', $user->password))->toBeTrue();
});

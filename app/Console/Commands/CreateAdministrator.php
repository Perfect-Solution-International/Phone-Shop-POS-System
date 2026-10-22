<?php

namespace App\Console\Commands;

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'app:create-administrator {email} {--name=} {--shop=}';

    protected $description = 'Create a shop and its administrator using a hidden password prompt';

    public function handle(): int
    {
        $password = $this->secret('Password (at least 12 characters)');
        $data = [
            'email' => $this->argument('email'),
            'name' => $this->option('name'),
            'shop' => $this->option('shop'),
            'password' => $password,
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'shop' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($data): void {
            (new DatabaseSeeder)->run();
            $shop = Shop::create(['name' => $data['shop']]);
            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->shop()->associate($shop);
            $user->save();
            $user->assignRole('Administrator');
        });
        $this->info('Administrator created.');

        return self::SUCCESS;
    }
}

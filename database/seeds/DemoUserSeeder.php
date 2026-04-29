<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    const EMAIL = 'jane@example.com';
    const PASSWORD = 'secret123';
    const API_TOKEN = 'local-demo-api-token';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', self::EMAIL)->first();

        if (!$user) {
            $user = new User();
        }

        $user->forceFill([
            'name' => 'Jane Translator',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'api_token' => self::API_TOKEN,
            'email_verified_at' => now(),
        ])->save();

        $this->command->info('Demo user ready: ' . self::EMAIL . ' / ' . self::PASSWORD);
    }
}

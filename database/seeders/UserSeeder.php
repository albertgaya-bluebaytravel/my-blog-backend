<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->verified()->create([
            'name' => 'admin',
            'email' => 'admin@myblog.com',
            'password' => env('ADMIN_PASSWORD', 'password')
        ]);
    }
}

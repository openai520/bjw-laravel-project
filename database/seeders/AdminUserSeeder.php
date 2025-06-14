<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 删除现有管理员账户
        User::where('email', 'openai520openai@gmail.com')->delete();
        User::where('email', 'admin@kalala-shop.com')->delete();

        // 创建新的管理员账户
        User::create([
            'name' => 'KalalaAdmin',
            'email' => 'admin@kalala.me',
            'password' => Hash::make('Admin@2024'),
            'is_admin' => true,
        ]);
    }
}

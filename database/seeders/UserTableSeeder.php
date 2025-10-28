<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'I Kadek Yudi Artana',
            'email'     => 'yudiartana024@gmail.com',
            'password'  => bcrypt('12345678'),
        ]);
    }
}

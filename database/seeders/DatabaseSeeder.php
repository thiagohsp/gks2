<?php

namespace Database\Seeders;

use App\Models\User;
use App\Repository\Eloquent\UserRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Thiago Pereira',
            'email' => 'mr.thiagoo@gmail.com',
            'password' => bcrypt('AeYf+9zY'),
        ]);

    }
}

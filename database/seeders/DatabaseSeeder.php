<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // disable foreign key checks - error will happen if not called
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // truncate tables - reset DB
        DB::table('users')->truncate();

        // re-enable foreign key checks - error will happen if not called
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // create tables with dummy data
        $this->call(AdminUserSeeder::class);
        $this->call(TestUserSeeder::class);
        $this->call(DummyUsersSeeder::class);
    }
}

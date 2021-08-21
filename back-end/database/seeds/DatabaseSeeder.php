<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(CitiesTableSeeder::class);

        $this->call(UsersTableSeeder::class);
        $this->call(LocationsTableSeeder::class);
        $this->call(CompaniesSeeder::class);
        $this->call(BancSeeder::class);
        $this->call(CompanyUserSeeder::class);
        $this->call(map_banc_index::class);
        $this->call(map_local_index::class);
        $this->call(oauthSeeds::class);
        $this->call(LocalTxSeeder::class);
        $this->call(ExternalTxSeeder::class);
        $this->call(AccountsSeeder::class);
        
    }
}

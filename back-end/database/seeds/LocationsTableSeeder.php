<?php

use Illuminate\Database\Seeder;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('locations')->truncate();
        Schema::enableForeignKeyConstraints();

    	$locations = array(

    		array(
    			'country_id' => 47,
    			'state_id' => 779,
    			'city_id' => 12688,

    		),
    		array(
    			'country_id' => 47,
    			'state_id' => 779,
    			'city_id' => 12688,

    		),
    	);

    	DB::table('locations')->insert($locations);
    }
}

<?php

use Illuminate\Database\Seeder;

class oauthSeeds extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

    	$oauth_clients = array(
			[
				'id' => '9129b2e3-a510-4215-b431-ac5521854a94',
				'user_id' => NULL,
		  	 	'name' => 'Laravel Personal Access Client',
		  	 	'secret' => '5xnI5pHHGoeKYeSDjBGtiSerLqFjZWWiQ3oAEJZ5',
		  	 	'provider' => null,
		  	 	'redirect' => 'http://localhost',
		  	 	'personal_access_client' => 1,
		  	 	'password_client' => 0,
		  	 	'revoked' => 0,
		    ],
			[
				'id' => '9129b2e3-a874-46f0-950e-6d1602a38cac',
				'user_id' => NULL,
		  	 	'name' => 'Laravel Personal Access Client',
		  	 	'secret' => '4W4UDtYprj8DV9f5PbdozEtKnig53ebZRTHVIqhg',
		  	 	'provider' => 'users',
		  	 	'redirect' => 'http://localhost',
		  	 	'personal_access_client' => 0,
		  	 	'password_client' => 1,
		  	 	'revoked' => 0,
		    ]);

        DB::table('oauth_clients')->insert($oauth_clients);

        $oauth_personal_access_clients = array(
        	[
        		'client_id' => '9129b2e3-a510-4215-b431-ac5521854a94'
        	]);

        DB::table('oauth_personal_access_clients')->insert($oauth_personal_access_clients);
    }
}

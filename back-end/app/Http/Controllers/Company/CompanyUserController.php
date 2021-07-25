<?php

namespace App\Http\Controllers\Company;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class CompanyUserController extends ApiController
{

    /**
     * Display a listing of the resource.
     *Este método permite ver al cliente los usuarios registrados en una determinada compañia
     * @return \Illuminate\Http\Response
     */
    public function index(Company $company)
    {

        $user = Auth::user();

        switch ($user->type) {
            case User::SUPER_ADMIN:
                
                $users = User::all();
                return $this->showAll($users);

                break;

            case User::ADMIN:
            case User::USER:
                
                $users = $company->users;
                return $this->showAll($users);
                
                break;
            
            default:
                return $this->showAll([]);
                break;
        }
        
    }

   

  


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company, User $user)
    {
        //sync, attach, syncwithoutDetaching
        $company->users()->syncWithoutDetaching([$user->id]);

        return $this->showAll($company->users);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}

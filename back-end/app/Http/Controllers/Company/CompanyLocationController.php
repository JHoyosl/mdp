<?php

namespace App\Http\Controllers\Company;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyLocationController extends Controller
{
    public function __construct(){

        $this->middleware('client.credentials')->only(['index']);
        // $this->middleware('auth:api')->only(['index','show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Company $company)
    {
        $location = $company->locations;

        return $location;
    }





}

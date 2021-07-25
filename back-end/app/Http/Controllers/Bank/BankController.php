<?php

namespace App\Http\Controllers\Bank;

use App\Models\Bank;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BankController extends ApiController
{

    public function __construct(){

        
        $this->middleware('auth:api')->only(['index','show','update','store','destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banks = Bank::orderBy('name','desc')->get();

        return $this->showAll($banks);
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank $bank)
    {
        $bank->destroy();

        return $this->showOne($bank);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

         $rules = [

            'cod_comp' => 'required|string|unique:banks',
            'name' => 'required|string',
            'nit' => 'required|string',
            'portal' => 'required',
            'currency' => 'required',


        ];

        $request->validate($rules);

        $bankFields = $request->all();

        
        $bank = Bank::create($bankFields);

        return $this->showOne($bank, '', true);
        

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function show(Bank $bank)
    {
       return $this->showOne($bank,'',true);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank $bank)
    {
        $rules = [

            'cod_comp' => 'required|string|unique:banks,cod_comp,' . $bank->id,
            'name' => 'required|string',
            'nit' => 'required|string',
            'portal' => 'required',
            'currency' => 'required',


        ];

        $request->validate($rules);

        $bankFields = $request->all();

        $bank->cod_comp = $bankFields['cod_comp'];
        $bank->nit = $bankFields['nit'];
        $bank->name = $bankFields['name'];
        $bank->portal = $bankFields['portal'];
        $bank->currency = $bankFields['currency'];
        
        $bank->save();

        return $this->showOne($bank);
        
    }


}

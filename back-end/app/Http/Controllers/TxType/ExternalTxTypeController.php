<?php

namespace App\Http\Controllers\TxType;

use App\Models\Bank;
use App\Models\ExternalTxType;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ExternalTxTypeController extends ApiController
{

    public function __construct(){

        $this->middleware('auth:api')->only(['index','store']);


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $txTypes = ExternalTxType::with('banks')
                        ->get();

        return $this->showAll($txTypes);
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
            'description'=>'required|string',
            'tx'=>'required|string',
            'bank_id'=>'required|string',
            'reference'=>'required|string',
            'type'=>'required|string',
            'sign'=>'required|string',
        ];
        
        $request->validate($rules);

        $fields = $request->all();

        $txTypeCheck = ExternalTxType::where('description', $fields['description'])
                        ->where('tx',$fields['tx'])
                        ->where('bank_id',$fields['bank_id'])
                        ->where('reference',$fields['reference'])
                        ->where('type',$fields['type'])
                        ->where('sign',$fields['sign'])
                        ->first();

        if($txTypeCheck === null){

            $fields['description'] = trim($fields['description']);
            $txType = ExternalTxType::create($fields);

            return $this->showOne($txType,'Success',true);

        }else{

            return $this->showOne($txTypeCheck->first(),'Ya existe la transacción',false);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
                
        $rules = [
            'description'=>'required|string',
            'tx'=>'required|string',
            'bank_id'=>'required|string',
            'type'=>'required|string',
            'sign'=>'required|string',
        ];
        
        $request->validate($rules);

        $fields = $request->all();

        $txType = ExternalTxType::findOrFail($id);

        $txType->description = trim($fields['description']);
        $txType->tx = $fields['tx'];
        $txType->bank_id = $fields['bank_id'];
        $txType->reference = $fields['reference']==null?'':$fields['reference'];
        $txType->type = $fields['type'];
        $txType->sign = $fields['sign'];

        $txType->save();

        return $this->showOne($txType); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

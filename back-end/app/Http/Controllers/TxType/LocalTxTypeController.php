<?php

namespace App\Http\Controllers\TxType;

use App\Models\LocalTxType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class LocalTxTypeController extends ApiController
{
    

    protected $conciliar_external_tx_type = 'conciliar_local_tx_type';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->conciliar_local_tx_type = 'conciliar_local_tx_type_'.$user->current_company;
            

            return $next($request);
        });

        
        $this->middleware('auth:api')->only(['index','store','update']);


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        $txTypes = new LocalTxType($this->conciliar_local_tx_type);
        
        return $txTypes->get();
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
            'reference'=>'required|string',
            'sign'=>'required|string',
        ];
        
        $request->validate($rules);

        $user = Auth::user();


        $fields = $request->all();


        $txTypes = new LocalTxType($this->conciliar_local_tx_type);

        $txTypeCheck = $txTypes->where('description', $fields['description'])
                        ->where('tx',$fields['tx'])
                        ->where('company_id',$user->current_company)
                        ->where('reference',$fields['reference'])
                        ->where('sign',$fields['sign'])
                        ->first();


        if($txTypeCheck === null){

            $newTxTypes = new LocalTxType($this->conciliar_local_tx_type);
            $newTxTypes->description = trim($fields['description']);
            $newTxTypes->tx = $fields['tx'];
            $newTxTypes->company_id = $user->current_company;
            $newTxTypes->reference = $fields['reference'];
            $newTxTypes->sign = $fields['sign'];


            $newTxTypes->save();

            return $this->showOne($newTxTypes,'Success',true);

        }else{

            return $this->showOne($txTypeCheck->first(),'Ya existe la transacción',false);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LocalTxType  $localTxType
     * @return \Illuminate\Http\Response
     */
    public function show(LocalTxType $localTxType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LocalTxType  $localTxType
     * @return \Illuminate\Http\Response
     */
    public function edit(LocalTxType $localTxType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LocalTxType  $localTxType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'description'=>'required|string',
            'tx'=>'required|string',
            'reference'=>'required|string',
            'sign'=>'required|string',
        ];
        
        $request->validate($rules);

        $user = Auth::user();

        $fields = $request->all();

        $txTypes = new LocalTxType($this->conciliar_local_tx_type);

        $toUpdateTxType = $txTypes->findOrFail($id);

        $toUpdateTxType->description = trim($fields['description']);
        $toUpdateTxType->tx = $fields['tx'];
        $toUpdateTxType->company_id = $user->current_company;
        $toUpdateTxType->reference = $fields['reference'];
        $toUpdateTxType->sign = $fields['sign'];

        $toUpdateTxType->save();

        return $this->showOne($toUpdateTxType); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LocalTxType  $localTxType
     * @return \Illuminate\Http\Response
     */
    public function destroy(LocalTxType $localTxType)
    {
        //
    }
}

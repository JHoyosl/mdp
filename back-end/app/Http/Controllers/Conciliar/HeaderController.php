<?php

namespace App\MHttp\Controllers\Conciliar;

use App\Models\ConciliarHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\ApiController;

class HeaderController extends ApiController
{   

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->conciliar_headers_table = 'conciliar_headers_'.$user->current_company;
            $this->conciliar_items_table = 'conciliar_items_'.$user->current_company;
            $this->conciliar_external_values_table = 'conciliar_external_values_'.$user->current_company;
            $this->conciliar_tmp_external_values_table = 'conciliar_tmp_external_values_'.$user->current_company;
            $this->conciliar_local_values_table = 'conciliar_local_values_'.$user->current_company;
            $this->conciliar_tmp_local_values_table = 'conciliar_tmp_local_values_table_'.$user->current_company;
            $this->conciliar_local_tx_type = 'conciliar_local_tx_type_'.$user->current_company;
            $this->conciliar_external_tx_type = 'external_tx_types';

            return $next($request);
        });

        
        $this->middleware('auth:api')->only(['index']);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(!Schema::hasTable($this->conciliar_headers_table)){

            return $this->showArray([]);
        }
        $headers = new ConciliarHeader($this->conciliar_headers_table);

        return $headers->orderBy('fecha_end','ASC')
                ->with('usersCreate')
                ->with('usersClose')
                ->get();
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ConciliarHeader  $conciliarHeader
     * @return \Illuminate\Http\Response
     */
    public function show(ConciliarHeader $conciliarHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ConciliarHeader  $conciliarHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(ConciliarHeader $conciliarHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ConciliarHeader  $conciliarHeader
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConciliarHeader $conciliarHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ConciliarHeader  $conciliarHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConciliarHeader $conciliarHeader)
    {
        //
    }
}

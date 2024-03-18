<?php

namespace App\Http\Controllers\Reconciliation;

use App\Models\ConciliarItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $user = Auth::user();

            $this->conciliar_headers_table = 'conciliar_headers_' . $user->current_company;
            $this->conciliar_items_table = 'conciliar_items_' . $user->current_company;
            $this->conciliar_external_values_table = 'conciliar_external_values_' . $user->current_company;
            $this->conciliar_tmp_external_values_table = 'conciliar_tmp_external_values_' . $user->current_company;
            $this->conciliar_local_values_table = 'conciliar_local_values_' . $user->current_company;
            $this->conciliar_tmp_local_values_table = 'conciliar_tmp_local_values_table_' . $user->current_company;
            $this->conciliar_local_tx_type = 'conciliar_local_tx_type_' . $user->current_company;
            $this->conciliar_external_tx_type = 'external_tx_types';

            return $next($request);
        });


        $this->middleware('auth:api')->only(['index', 'getHeaderItems']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\ConciliarItem  $conciliarItem
     * @return \Illuminate\Http\Response
     */
    public function show(ConciliarItem $conciliarItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ConciliarItem  $conciliarItem
     * @return \Illuminate\Http\Response
     */
    public function edit(ConciliarItem $conciliarItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ConciliarItem  $conciliarItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConciliarItem $conciliarItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ConciliarItem  $conciliarItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConciliarItem $conciliarItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ConciliarItem  $conciliarItem
     * @return \Illuminate\Http\Response
     */
    public function getHeaderItems($headerId)
    {
        $user = Auth::user();

        $itemTable = new ConciliarItem($this->conciliar_items_table);


        return $itemTable->where('header_id', '=', $headerId)
            ->with('account')
            ->with('account.banks')
            ->get();
    }
}

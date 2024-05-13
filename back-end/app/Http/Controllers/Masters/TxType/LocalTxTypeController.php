<?php

namespace App\Http\Controllers\Masters\TxType;

use Exception;
use App\Models\LocalTxType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TxType\TxTypeService;
use App\Http\Controllers\ApiController;
use App\Http\Resources\TxType\LocalTxTypeResource;
use App\Http\Resources\TxType\LocalTxTypeCollection;
use App\Http\Requests\TxType\TxTypeStoreLocalRequest;
use App\Http\Requests\TxType\TxTypeUpdateLocalRequest;

class LocalTxTypeController extends ApiController
{

    private $user;
    private $companyId;
    private TxTypeService $txTypeService;
    protected $conciliar_local_tx_type = 'local_tx_types';

    public function __construct(TxTypeService $txTypeService)
    {

        $this->middleware(function ($request, $next) {

            $user = Auth::user();
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            $this->conciliar_local_tx_type = 'local_tx_types';


            return $next($request);
        });
        $this->middleware('auth:api')->only(['index', 'store', 'update', 'destroy']);
        $this->txTypeService = $txTypeService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $txs = $this->txTypeService->indexLocalTx($this->companyId);
        return $this->showMessage(new LocalTxTypeCollection($txs), 200);
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
    public function store(TxTypeStoreLocalRequest $request)
    {

        try {
            $newTx = $this->txTypeService->storeLocalTx(
                $this->companyId,
                $request->description,
                $request->tx,
                $request->reference,
                $request->type,
                $request->sign
            );
            return $this->showMessage(new LocalTxTypeResource($newTx), 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
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
    public function update(TxTypeUpdateLocalRequest $request, $id)
    {
        try {
            $updatedTx = $this->txTypeService->updateLocalTx(
                $id,
                $this->companyId,
                $request->description,
                $request->tx,
                $request->reference,
                $request->type,
                $request->sign,
            );
            return $this->showMessage(new LocalTxTypeResource($updatedTx));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LocalTxType  $localTxType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->txTypeService->deleteLocalTx($this->companyId, $id);

        if ($deleted) {
            return $this->showMessage('success', 200);
        } else {
            return $this->errorResponse('No deleted', 400);
        }
    }
}

<?php

namespace App\Http\Controllers\Masters\TxType;

use App\Models\Bank;
use Illuminate\Http\Request;
use App\Models\ExternalTxType;
use Illuminate\Support\Facades\Auth;
use App\Services\TxType\TxTypeService;
use App\Http\Controllers\ApiController;
use App\Http\Resources\TxType\ExternalTxTypeResource;
use App\Http\Resources\TxType\ExternalTxTypeCollection;
use App\Http\Requests\TxType\TxTypeStoreExternalRequest;
use App\Http\Requests\TxType\TxTypeUpdateExternalRequest;

class ExternalTxTypeController extends ApiController
{
    private $user;
    private $companyId;
    private TxTypeService $txTypeService;

    public function __construct(TxTypeService $txTypeService)
    {

        $this->middleware('auth:api')->only(['index', 'store', 'update', 'destroy']);
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            return $next($request);
        });

        $this->txTypeService = $txTypeService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $txs = $this->txTypeService->indexExternalTx();
        return $this->showMessage(ExternalTxTypeResource::collection($txs));
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
    public function store(TxTypeStoreExternalRequest $request)
    {
        try {
            $newTx = $this->txTypeService->storeExternalTx(
                $request->description,
                $request->tx,
                $request->reference,
                $request->type,
                $request->sign,
                $request->bankId
            );
            return $this->showMessage(new ExternalTxTypeResource($newTx));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
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
    public function update(TxTypeUpdateExternalRequest $request, $id)
    {
        try {
            $updatedTx = $this->txTypeService->updateExternalTx(
                $id,
                $request->description,
                $request->tx,
                $request->bankId,
                $request->type,
                $request->sign,
                $request->reference,
            );
            return $this->showMessage(new ExternalTxTypeResource($updatedTx));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->txTypeService->deleteExternalTx($id);
        if ($deleted) {
            return $this->showMessage('success', 200);
        } else {
            return $this->errorResponse('No deleted', 400);
        }
    }
}

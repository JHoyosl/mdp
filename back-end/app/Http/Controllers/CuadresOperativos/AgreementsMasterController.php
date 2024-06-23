<?php

namespace App\Http\Controllers\CuadresOperativos;

use App\Http\Controllers\ApiController;
use App\Services\CuadresOperativos\AgreementsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgreementsMasterController extends ApiController
{
    private AgreementsService $agreementsService;

    private $companyId;
    private $user;
    public function __construct(AgreementsService $agreementsService)
    {
        $this->middleware(function ($request, $next) {
            $this->companyId = Auth::user()->current_company;
            $this->user = Auth::user();

            return $next($request);
        });
        $this->agreementsService = $agreementsService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $agreementsMaster = $this->agreementsService->indexMaster($this->companyId);
        return $this->showAll($agreementsMaster);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        //
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

    public function uploadMaster(Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);

        return $this->agreementsService->uploadMaster($this->companyId, $request->file);
    }
}

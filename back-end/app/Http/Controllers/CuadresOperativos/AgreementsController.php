<?php

namespace App\Http\Controllers\CuadresOperativos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\CuadresOperativos\AgreementsService;
use App\Traits\TableNamming;

class AgreementsController extends ApiController
{
    use TableNamming;

    private AgreementsService $agreementsService;
    private $companyId;
    private $user;

    function __construct(
        AgreementsService $agreementsService
    ) {
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
        $info = $this->agreementsService->index($this->companyId);
        return $this->showAll($info);
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

    public function agreementsResult(Request $request)
    {
        $agreementsHeader = $this->getAgreemenetsHeadersTableName($this->companyId);
        $balanceHeader = $this->getBalanceSheetHeadersTableName($this->companyId);

        $request->validate([
            'date' => 'required|exists:' . $agreementsHeader . ',date',
            'date' => 'exists:' . $balanceHeader . ',fecha'
        ]);

        return $this->agreementsService->getAgreementsResult($this->companyId, $request->date);
    }

    public function upload(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');

        $agreementsHeader = $this->getAgreemenetsHeadersTableName($this->companyId);
        $balanceHeader = $this->getBalanceSheetHeadersTableName($this->companyId);

        $request->validate([
            'file' => 'required',
            'date' => 'required|unique:' . $agreementsHeader . ',date',
            'date' => 'exists:' . $balanceHeader . ',fecha'
        ]);

        $data = $this->agreementsService
            ->uploadAgreement($this->companyId, $this->user, $request->file, $request->date);

        return $this->showMessage($data);
    }
}

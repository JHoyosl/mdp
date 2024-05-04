<?php

namespace App\Http\Controllers\Accounting;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\HeaderAccountingInfo;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\Account\AccountingService;
use App\Services\Reconciliation\ReconciliationService;



class AccountingController extends ApiController
{
    private $companyId;
    private $user;

    protected AccountingService $accountingService;
    private ReconciliationService $reconciliationService;

    public function __construct(AccountingService $accountingService, ReconciliationService $reconciliationService)
    {
        $this->middleware('auth:api');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            return $next($request);
        });

        $this->accountingService = $accountingService;
        $this->reconciliationService = $reconciliationService;
    }


    public function index()
    {
        $companyId = $this->user->current_company;
        $info = $this->accountingService->index($companyId);
        return $this->showAll($info);
    }


    public function uploadAccountingInfo(Request $request)
    {
        //TODO: VALIDATE MIMES AND SIZE
        $validated = $request->validate([
            'accountingInfo' => 'required|mimes:xlsx,csv,xls',
            'startDate' => 'required',
            'endDate' => 'required',
        ]);

        $company =  Company::find($this->user->current_company);

        if ($company->map_id == null) {
            return $this->errorResponse("No hay un formato asociado", 400);
        }

        try {
            $data = $this->accountingService
                ->uploadAccountInfo(
                    $this->user,
                    $request->file('accountingInfo'),
                    $request->startDate,
                    $request->endDate,
                    $company
                );
            return $data;
            return $this->showOne($data);
        } catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function deleteLastUpload(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required',
            'startDate' => 'required',
            'endDate' => 'required'
        ]);

        if (!!$this->reconciliationService->hasReconciliationBefore($request->startDate, $request->endDate, $this->companyId)) {
            return $this->errorResponse('Existe una conciliación, asociada a este archivo, Debe reversar la coniliación', 400);
        }

        try {
            $result = $this->accountingService
                ->canBeDeleted($request->id, $request->startDate, $request->endDate, $this->user->current_company);
            return $this->showMessage($result, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAccountingItems(Request $request)
    {

        $validated = $request->validate([
            'headerId' => 'required'
        ]);

        $company =  Company::find($this->user->current_company);
        try {
            $items = $this->accountingService->getHeaderItems($request->headerId, $company->id);
            return $this->showAll($items);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}

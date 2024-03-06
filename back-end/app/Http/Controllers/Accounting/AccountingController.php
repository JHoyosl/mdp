<?php

namespace App\Http\Controllers\Accounting;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\Account\AccountingService;



class AccountingController extends ApiController
{
    private $companyId;
    private $user;

    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->middleware('auth:api');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user;
            return $next($request);
        });

        $this->accountingService = $accountingService;
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
            return $this->accountingService
                ->uploadAccountInfo(
                    $this->user,
                    $request->file('accountingInfo'),
                    $request->startDate,
                    $request->endDate,
                    $company
                );
        } catch (\Exception $e) {
            return json_encode($e->getMessage());
        }
    }

    public function deleteLastUpload(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required',
            'startDate' => 'required',
            'endDate' => 'required'
        ]);
        try {
            $result = $this->accountingService
                ->canBeDeleted($request->id, $request->startDate, $request->endDate, $this->user->current_company);
            return $this->showMessage($result, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}

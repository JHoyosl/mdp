<?php

namespace App\Http\Controllers\ThirdParties;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\ThirdParties\ThirdPartiesService;
use App\Services\Reconciliation\ReconciliationService;
use App\Services\ThirdParties\SetThirdPartiesTxService;


class ThirdPartiesController extends ApiController
{

    private $user;
    private $companyId;
    private ThirdPartiesService $thirdPartiesService;
    private ReconciliationService $reconciliationService;
    private SetThirdPartiesTxService $setThirdPartiesTxService;

    public function __construct(
        ThirdPartiesService $thirdPartiesService,
        ReconciliationService $reconciliationService,
        SetThirdPartiesTxService $setThirdPartiesTxService
    ) {
        $this->middleware('auth:api')->except([]);
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            return $next($request);
        });

        $this->thirdPartiesService = $thirdPartiesService;
        $this->reconciliationService = $reconciliationService;
        $this->setThirdPartiesTxService = $setThirdPartiesTxService;
    }

    public function index()
    {
        $accounts = $this->thirdPartiesService->getThirdPartiesAccounts($this->companyId);
        return $this->showAll($accounts);
    }

    public function getAccountHeaderInfo(Request $request)
    {
        $validated = $request->validate([
            'accountId' => 'required|exists:accounts,id'
        ]);
        $data = $this->thirdPartiesService->getAccountHeaderInfo($this->companyId, $request->accountId);
        return $this->showAll($data);
    }

    public function deleteLastUpload(Request $request)
    {

        $validated = $request->validate([
            "headerId" => "required|exists:header_third_parties_info,id",
            "startDate" => "required",
            "endDate" => "required",
            "accountId" => "required|exists:accounts,id",
        ]);

        if (!!$this->reconciliationService->hasReconciliationBefore($request->startDate, $request->endDate, $this->companyId, $request->accountId)) {
            return $this->errorResponse('Existe una conciliación, asociada a este archivo, Debe reversar la coniliación', 400);
        }

        try {

            $response = $this->thirdPartiesService->deletelastHeaderInfo(
                $request->headerId,
                $request->accountId,
                $request->startDate,
                $request->endDate,
                $this->companyId
            );
            return $this->showMessage($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function uploadAccountInfo(Request $request)
    {
        $validated = $request->validate([
            "accountId" => "required|exists:accounts,id",
            "startDate" => "required",
            "endDate" => "required"
        ]);

        try {
            $header = $this->thirdPartiesService->uploadAccountInfo(
                $this->user,
                $request->accountId,
                $this->companyId,
                $request->file,
                $request->startDate,
                $request->endDate
            );
            return $this->showOne($header);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public  function getHeaderItems(Request $request)
    {
        $validated = $request->validate([
            'headerId' => 'required|exists:header_third_parties_info,id'
        ]);

        try {
            $items = $this->thirdPartiesService->getHeaderItems($request->headerId);
            return $this->showAll($items);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function setTxType(Request $request)
    {
        $request->validate([
            'accounts' => 'required'
        ]);
        $accounts = json_decode($request->accounts);

        $this->setThirdPartiesTxService->updateSimpleTx($this->companyId, $accounts);
        $this->setThirdPartiesTxService->updateCompuestoTx($this->companyId, $accounts);
        $this->setThirdPartiesTxService->updateReferenceByRFGuionQuery($this->companyId, $accounts);
        // return $this->setThirdPartiesTxService->updateCompuestoTx($this->companyId, $accounts);
    }
}

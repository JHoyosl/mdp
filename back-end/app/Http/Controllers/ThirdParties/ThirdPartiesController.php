<?php

namespace App\Http\Controllers\ThirdParties;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\ThirdParties\ThirdPartiesService;


class ThirdPartiesController extends ApiController
{

    private $user;
    private $companyId;
    private  ThirdPartiesService $thirdPartiesService;

    public function __construct(ThirdPartiesService $thirdPartiesService)
    {
        $this->middleware('auth:api')->except([]);
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            return $next($request);
        });

        $this->thirdPartiesService = $thirdPartiesService;
    }

    public function index()
    {
        return $this->thirdPartiesService->getThirdPartiesAccounts($this->companyId);
    }

    public function deleteLastUpload(Request $request)
    {
        $validated = $request->validate([
            "headerId" => "required|exists:header_accounting_info,id",
            "startDate" => "required",
            "endDate" => "required"
        ]);

        try {

            $response = $this->thirdPartiesService->deletelastHeaderInfo(
                $request->headerId,
                $request->startDate,
                $request->endDate
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
}

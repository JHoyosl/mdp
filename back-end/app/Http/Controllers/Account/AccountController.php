<?php

namespace App\Http\Controllers\Account;

use App\Models\User;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class AccountController extends ApiController
{

    public function __construct()
    {

        $this->middleware('auth:api')->only(['index', 'show', 'update', 'store', 'setMap', 'getAccountsByCenter']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();


        if ($user->type == User::SUPER_ADMIN) {

            $accounts = Account::with('banks')
                ->with('companies')
                ->get();

            return $this->showAll($accounts, '', true);
        } else {

            $accounts = Account::where('company_id', $user->current_company)
                ->with('banks')
                ->with('companies')
                ->get();

            return $this->showAll($accounts, '', true);
        }
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
        $rules = [
            'bank_id' => 'required|string',
            'acc_type' => 'required|string',
            'bank_account' => 'required|string',
            'local_account' => 'required|string',
        ];

        $request->validate($rules);

        $user = Auth::user();

        $accountFields = $request->all();

        $accountCheck = Account::where('bank_id', $accountFields['bank_id'])
            ->where('company_id', $user->current_company)
            ->where('bank_account', $accountFields['bank_account'])
            ->where('local_account', $accountFields['local_account'])
            ->where('acc_type', $accountFields['acc_type'])
            ->first();


        $accountFields['map_id'] = null;
        if ($accountCheck === null) {

            $accountFields['company_id'] = $user->current_company;

            $account = Account::create($accountFields);

            return $this->showOne($account, 'Ya existe la cuenta', true);
        } else {

            return $this->showOne($accountCheck->first(), 'Ya existe la cuenta', false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     **/
    // public function show($company_id)
    // {
    //     $user = Auth::user();


    //     if ($user->type == User::SUPER_ADMIN) {

    //         $accounts = Account::findOrFail($id)
    //             ->with('banks')
    //             ->with('companies')
    //             ->get();

    //         return $this->showOne($accounts, '', true);
    //     } else {

    //         $accounts = Account::where('company_id', $user->current_company)
    //             ->findOrFail($id)
    //             ->with('banks')
    //             ->with('companies')
    //             ->get();

    //         return $this->showOne($accounts, '', true);
    //     }
    // }

    public function show($id)
    {
        $location = Location::findOrFail($id);

        return $this->showOne($location);
    }
    /*** Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        $rules = [
            'bank_id' => 'required|string',
            'acc_type' => 'required|string',
            'bank_account' => 'required|string',
            'local_account' => 'required|string',
        ];

        $request->validate($rules);

        $fields = $request->all();

        $account->bank_id = $fields['bank_id'];
        $account->acc_type = $fields['acc_type'];
        $account->bank_account = $fields['bank_account'];
        $account->local_account = $fields['local_account'];


        $account->save();

        return $this->showOne($account);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }

    public function setMap(Request $request)
    {

        $fields = $request->all();

        $account = Account::find($fields['acc_id']);

        $account->map_id = $fields['map_id'];

        $account->save();

        return $this->showOne($account);
    }

    public function getAccountsByCenter()
    {

        $user = Auth::user();

        $accounts = Account::where('company_id', $user->current_company)
            ->with('banks')
            ->with('companies')
            ->orderBy('bank_id')
            ->get();

        return $this->showAll($accounts, '', true);
    }
}

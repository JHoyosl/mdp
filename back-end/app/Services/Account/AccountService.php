<?php

namespace App\Services\Account;

use App\Models\Account;

class AccountService
{


    public function __construct()
    {
    }

    public function getAccountById(String $id)
    {

        $account = Account::findOrFail($id);
        return $account;
    }
}

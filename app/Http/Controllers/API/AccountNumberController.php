<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Http\Controllers\Controller;
use Exception;

class AccountNumberController extends Controller
{
    public function generateAccountNumbersForExistingAccounts()
    {
        try {
            $accounts = Account::whereNull('account_number')->orderBy('id')->get();
            $nextAccountNumber = 2000001;

            foreach ($accounts as $account) {
                $account->account_number = (string)$nextAccountNumber;
                $account->save();
                $nextAccountNumber++;
            }

            return response()->json([
                'message' => 'Account numbers generated successfully',
                'accounts_updated' => $accounts->count()
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while generating account numbers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

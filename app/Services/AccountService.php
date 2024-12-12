<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class AccountService
{
    /**
     * Create a new account
     */
    public function createAccount(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            // Generate next account code if not provided
            if (!isset($data['code'])) {
                $data['code'] = $this->generateNextCode($data['type']);
            }
            
            return Account::create($data);
        });
    }

    /**
     * Update an existing account
     */
    public function updateAccount(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data) {
            $account->update($data);
            return $account->fresh();
        });
    }

    /**
     * Update account balance based on journal entries
     */
    public function updateBalance(Account $account): Account
    {
        return DB::transaction(function () use ($account) {
            $totalDebit = $account->journalLines()
                ->whereHas('journalEntry', function ($query) {
                    $query->where('status', 'posted');
                })
                ->sum('debit');

            $totalCredit = $account->journalLines()
                ->whereHas('journalEntry', function ($query) {
                    $query->where('status', 'posted');
                })
                ->sum('credit');
            
            // Calculate balance based on account type
            $balance = match($account->type) {
                'asset', 'expense' => $totalDebit - $totalCredit,
                'liability', 'equity', 'income' => $totalCredit - $totalDebit,
                default => 0,
            };

            $account->update(['balance' => $balance]);
            
            // Update parent account balance if exists
            if ($account->parent_id) {
                $this->updateBalance($account->parent);
            }

            return $account;
        });
    }

    /**
     * Calculate period balances for an account
     */
    public function calculatePeriodBalance(Account $account, int $fiscalPeriodId): AccountBalance
    {
        return DB::transaction(function () use ($account, $fiscalPeriodId) {
            $balance = AccountBalance::firstOrNew([
                'account_id' => $account->id,
                'fiscal_period_id' => $fiscalPeriodId
            ]);

            $entries = $account->journalLines()
                ->whereHas('journalEntry', function ($query) use ($fiscalPeriodId) {
                    $query->where('fiscal_period_id', $fiscalPeriodId)
                        ->where('status', 'posted');
                });

            $balance->total_debit = $entries->sum('debit');
            $balance->total_credit = $entries->sum('credit');
            $balance->closing_balance = match($account->type) {
                'asset', 'expense' => $balance->total_debit - $balance->total_credit,
                'liability', 'equity', 'income' => $balance->total_credit - $balance->total_debit,
                default => 0,
            };

            $balance->save();
            return $balance;
        });
    }

    /**
     * Get chart of accounts in hierarchical structure
     */
    public function getChartOfAccounts(): Collection
    {
        return Account::with('children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();
    }

    /**
     * Generate next available account code
     */
    protected function generateNextCode(string $type): string
    {
        $prefixMap = [
            'asset' => '1',
            'liability' => '2',
            'equity' => '3',
            'income' => '4',
            'expense' => '5'
        ];

        $prefix = $prefixMap[$type] ?? '1';
        
        $lastAccount = Account::where('type', $type)
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastAccount) {
            return $prefix . '0001';
        }

        $lastCode = intval(substr($lastAccount->code, 1));
        $newCode = $lastCode + 1;
        
        return $prefix . str_pad($newCode, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get account balance at a specific date
     */
    public function getBalanceAsOf(Account $account, string $date)
    {
        $totalDebit = $account->journalLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('entry_date', '<=', $date)
                    ->where('status', 'posted');
            })
            ->sum('debit');

        $totalCredit = $account->journalLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('entry_date', '<=', $date)
                    ->where('status', 'posted');
            })
            ->sum('credit');

        return match($account->type) {
            'asset', 'expense' => $totalDebit - $totalCredit,
            'liability', 'equity', 'income' => $totalCredit - $totalDebit,
            default => 0,
        };
    }

    /**
     * Check if account can be deleted
     */
    public function canDelete(Account $account): bool
    {
        // Check if account has any transactions
        if ($account->journalLines()->exists()) {
            return false;
        }

        // Check if account has any children
        if ($account->children()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Delete an account if possible
     */
    public function deleteAccount(Account $account): bool
    {
        if (!$this->canDelete($account)) {
            return false;
        }

        return DB::transaction(function () use ($account) {
            $account->delete();
            return true;
        });
    }
}
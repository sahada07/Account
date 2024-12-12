<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    protected $accountService;

    // public function __construct(AccountService $accountService)
    // {
    //     $this->accountService = $accountService;
    //     $this->middleware('auth');
    // }

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
        // Removed middleware
    }

    /**
     * Display accounts list
     */
    public function index()
    {
        $accounts = Account::with('parent')
            ->orderBy('code')
            ->paginate(20);
        
        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show create account form
     */
    public function create()
    {
        $parentAccounts = Account::where('level', 'parent')
            ->orderBy('code')
            ->get();
            
        return view('accounts.create', compact('parentAccounts'));
    }

    /**
     * Store new account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|unique:accounts',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'level' => 'required|in:parent,child',
            'parent_id' => 'required_if:level,child|exists:accounts,id|nullable',
            'description' => 'nullable|string'
        ]);

        try {
            $account = $this->accountService->createAccount($validated);
            return redirect()
                ->route('accounts.index')
                ->with('success', 'Account created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating account: ' . $e->getMessage());
        }
    }

    /**
     * Show edit account form
     */
    public function edit(Account $account)
    {
        $parentAccounts = Account::where('level', 'parent')
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();
            
        return view('accounts.edit', compact('account', 'parentAccounts'));
    }

    /**
     * Update account
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'level' => 'required|in:parent,child',
            'parent_id' => 'required_if:level,child|exists:accounts,id|nullable',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            $account = $this->accountService->updateAccount($account, $validated);
            return redirect()
                ->route('accounts.index')
                ->with('success', 'Account updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating account: ' . $e->getMessage());
        }
    }

    /**
     * Show account details
     */
    public function show(Account $account)
    {
        $account->load('children', 'parent', 'journalLines.journalEntry');
        return view('accounts.show', compact('account'));
    }

    /**
     * Delete account
     */
    public function destroy(Account $account)
    {
        try {
            if (!$this->accountService->canDelete($account)) {
                return back()->with('error', 'Account cannot be deleted as it has transactions or child accounts');
            }

            $this->accountService->deleteAccount($account);
            return redirect()
                ->route('accounts.index')
                ->with('success', 'Account deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting account: ' . $e->getMessage());
        }
    }

    /**
     * Get account balance as of date
     */
    public function getBalance(Request $request, Account $account)
    {
        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        $balance = $this->accountService->getBalanceAsOf($account, $validated['date']);
        
        if ($request->ajax()) {
            return response()->json(['balance' => $balance]);
        }

        return back()->with('balance', $balance);
    }

    /**
     * Display chart of accounts
     */
    public function chart()
    {
        $accounts = $this->accountService->getChartOfAccounts();
        return view('accounts.chart', compact('accounts'));
    }
}
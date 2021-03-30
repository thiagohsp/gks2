<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBatchRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\Request;
use App\Repository\AccountRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function index() {
        //dd(Auth::user());
        return response()->json($this->accountRepository->all());
    }

    public function store(Request $request) {
        dd($request);
        return response()->json($request);
    }

}

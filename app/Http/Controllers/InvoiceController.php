<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    private $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index() {
        //dd(Auth::user());
        $response = $this->invoiceRepository->query()->with('customer')->latest('number')->get();
        //dd($response);
        return Inertia::render('Invoices/Index', [
            'invoices' => $response

        ]);
        //return response()->json($this->invoiceRepository->paginate(20, ['*'], ['customer']), 200);
    }

    public function query(Request $req) {

        $query = $this->invoiceRepository->query()->with('customer');

        if ($req->customer_id) $query->where('customer_id', '=', $req->customer_id);

        if ($req->data_ini) $query->where('date', '>=', $req->data_ini);

        if ($req->data_fin) $query->where('date', '<=', $req->data_fin);

        if ($req->nf_ini) $query->where('number', '>=', $req->nf_ini);

        if ($req->nf_fin) $query->where('number', '<=', $req->nf_fin);

        if ($req->agente) $query->where('agent', '=', $req->agente);

        if ($req->agente2) $query->where('agent_2', '=', $req->agente2);

        if ($req->saldo_ini) $query->where('balance', '<=', $req->saldo_ini);

        if ($req->saldo_fin) $query->where('balance', '<=', $req->saldo_fin);

        $result = $query->latest('number')->paginate(10, ['*']);

        return response()->json($result, 200);
    }

    public function store(Request $req) {
        //return response()->json($this->invoiceRepository->all(), 200);
        return;
    }
}

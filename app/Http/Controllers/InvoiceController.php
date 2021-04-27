<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
        $response = $this->invoiceRepository->query()->where('status','!=', 'C')->with('customer')->latest('number')->get();
        //dd($response);
        return Inertia::render('Invoices/Index', [
            'invoices' => $response

        ]);
        //return response()->json($this->invoiceRepository->paginate(20, ['*'], ['customer']), 200);
    }

    public function query(Request $req) {

        $query = $this->invoiceRepository->query()->where('status' , '!=', 'C');

        if ($req->customer_id) $query->where('customer_id', '=', $req->customer_id);

        if ($req->data_ini) $query->where('date', '>=', $req->data_ini);

        if ($req->data_fin) $query->where('date', '<=', $req->data_fin);

        if ($req->nf_ini) $query->where('number', '>=', $req->nf_ini);

        if ($req->nf_fin) $query->where('number', '<=', $req->nf_fin);

        if ($req->agente) $query->where('agent', '=', $req->agente);

        if ($req->agente_2) $query->where('agent_2', '=', $req->agente_2);

        if ($req->saldo_nf) $query->where('falta_faturar', '>=',  $req->saldo_nf);

        if ($req->saldo_cli) {

            $saldoCliente = $req->saldo_cli;
            $query->whereHas('customer', function (Builder $query) use($saldoCliente) {
                $query->where('falta_faturar', '>=', $saldoCliente);
            })->with('customer');

        } else {
            $query->with('customer');
        }

        $result = $query->latest('number')->get();

        return response()->json($result, 200);
    }

    public function store(Request $req) {
        //return response()->json($this->invoiceRepository->all(), 200);
        return;
    }
}

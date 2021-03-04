<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Repository\InvoiceRepositoryInterface;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    private $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index() {
        // return Inertia::render('Invoices/Index', [
        //    'invoices' => $this->invoiceRepository->paginate(20, ['*'], ['customer']),
        // ]);
        return response()->json($this->invoiceRepository->paginate(20, ['*'], ['customer']), 200);
    }

    public function store(Request $req) {
        //return response()->json($this->invoiceRepository->all(), 200);
        return;
    }
}

<?php

namespace App\Http\Controllers;

use App\Repository\BatchRepositoryInterface;
use App\Repository\BillRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class BillController extends Controller
{
    private $batchRepository;
    private $billRepository;
    private $invoiceRepository;

    public function __construct(
        BatchRepositoryInterface   $batchRepository,
        BillRepositoryInterface    $billRepository
    )
    {
        $this->batchRepository   = $batchRepository;
        $this->billRepository    = $billRepository;
    }

    public function index($batchId) {

        $response = $this->billRepository->query()
            ->where('batch_id', '=', $batchId)
            ->with(['invoice.customer'])
            ->oldest('bill_number')
            ->get();
        //$response = $this->batchRepository->findById($batchId, ['*'], ['bills']);

        //dd($response);

        return Inertia::render('Bill/Index', [
            'data' => $response
        ]);

    }

}

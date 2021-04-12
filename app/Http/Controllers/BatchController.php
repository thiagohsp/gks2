<?php

namespace App\Http\Controllers;

use App\Repository\BatchRepositoryInterface;
use App\Repository\BillRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\CreateBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class BatchController extends Controller
{
    private $batchRepository;
    private $billRepository;
    private $invoiceRepository;

    public function __construct(
        BatchRepositoryInterface   $batchRepository,
        BillRepositoryInterface    $billRepository,
        InvoiceRepositoryInterface $invoiceRepository
    )
    {
        $this->batchRepository   = $batchRepository;
        $this->billRepository    = $billRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index() {

        $response = $this->batchRepository->all();

        return Inertia::render('Batches/Index', [
            'data' => $response
        ]);

    }

    public function store(Request $req) {

        $createBatchService = new CreateBatchService(
            $this->batchRepository,
            $this->billRepository,
            $this->invoiceRepository
        );

        $batch = $createBatchService->execute(
            $req->codigo_lote,
            $req->total_lote,
            $req->valor_maximo_cobranca,
            $req->email,
            Carbon::parse($req->data_vencimento),
            $req->conta_corrente,
            $req->notas_fiscais
        );

        $response = $this->batchRepository->all();

        return Inertia::render('Batches/Index', [
            'data' => $response
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmail;
use App\Mail\SendBatchMail;
use App\Repository\AccountRepositoryInterface;
use App\Repository\BatchRepositoryInterface;
use App\Repository\BillRepositoryInterface;
use App\Repository\CustomerRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\CreateBatchService;
use App\Services\DeleteBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BatchController extends Controller
{
    private $batchRepository;
    private $billRepository;
    private $invoiceRepository;
    private $accountRepository;
    private $customerRepository;

    public function __construct(
        BatchRepositoryInterface    $batchRepository,
        BillRepositoryInterface     $billRepository,
        InvoiceRepositoryInterface  $invoiceRepository,
        AccountRepositoryInterface  $accountRepository,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->batchRepository   = $batchRepository;
        $this->billRepository    = $billRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->accountRepository = $accountRepository;
        $this->customerRepository = $customerRepository;
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
            $this->invoiceRepository,
            $this->accountRepository,
            $this->customerRepository
        );

        try {
            $batch = $createBatchService->execute(
                $req->codigo_lote,
                $req->total_lote,
                $req->valor_maximo_cobranca,
                $req->email,
                Carbon::parse($req->data_vencimento),
                $req->conta_corrente,
                $req->notas_fiscais
            );

            // Send mail
            $details = ['email' => $req->email, 'batch_id' => $batch->id];
            SendEmail::dispatch($details)->delay(now()->addSeconds(10));

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response($th->getMessage(), 500);
        }

        // if ($batch == null) {
        //     return response('Internal server error', 500);
        // }

        Log::info("Batch created!".$batch);

        $response = $this->batchRepository->all();

        return redirect()->route('lotes');
    }

    public function destroy($id) {

        $deleteBatchService = new DeleteBatchService(
            $this->batchRepository,
            $this->billRepository,
            $this->invoiceRepository,
            $this->customerRepository
        );

        $deleteBatchService->execute($id);

        return redirect()->route('lotes');

    }
}

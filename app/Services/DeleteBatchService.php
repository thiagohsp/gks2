<?php

namespace App\Services;

use App\Repository\Eloquent\BatchRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class DeleteBatchService
{

    private BatchRepository $batchRepository;
    private BillRepository  $billRepository;
    private InvoiceRepository $invoiceRepository;

	public function __construct(BatchRepository $batchRepository,
                                BillRepository  $billRepository,
                                InvoiceRepository  $invoiceRepository )
	{
		$this->batchRepository    = $batchRepository;
		$this->billRepository     = $billRepository;
		$this->invoiceRepository  = $invoiceRepository;
	}

	public function execute(string $id)
	{
        // Incluir um novo lote
        // Criar os boletos de acordo com o Valor Total / Valor Max. por Boleto
        // Vincular boletos ao lote
        // enviar email ao cliente

        Log::info('Executing DeleteBatchService...');

        $batch = $this->batchRepository->findById($id, ['*'], ['bills']);

        if ($batch == null || !isset($batch)) {
            throw new Exception("Batch Not Found", 1);
            return;
        }


        foreach ($batch->bills as $bill) {
            # code...
            $deleteBillService = new SoftDeleteBillService(
                $this->billRepository,
                $this->invoiceRepository
            );

            $result = $deleteBillService->execute($bill->id);

        }

        $this->batchRepository->deleteById($id);

        return $batch->refresh();
	}

}

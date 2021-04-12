<?php

namespace App\Services;

use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class SoftDeleteBillService {

    private BillRepository     $billRepository;
    private InvoiceRepository  $invoiceRepository;

    public function __construct(BillRepository     $billRepository,
                                InvoiceRepository  $invoiceRepository)
	{
		$this->billRepository       = $billRepository;
		$this->invoiceRepository    = $invoiceRepository;
	}

    public function execute(string $billId) {

        Log::info('SoftDeleteBillService: '.$billId);

        $bill = $this->billRepository->findById($billId);

        $invoice = $bill->invoice();

        if ($invoice == null || !isset($invoice)) {
            throw new Exception("Bill Not Found!", 0);
            return;
        }

        $previousLetter = $invoice->getPreviousLetter();

        $result = $this->invoiceRepository->update($invoice->id, [
                'balance' => $invoice->balance + $bill->value,
                'falta_faturar' =>  $invoice->falta_faturar + $bill->value,
                'last_letter' => $previousLetter
            ]
        );

        $result = $this->billRepository->deleteById($bill->id);

        $bill->update([
            'status' => 'C'
        ]);

        $bill->refresh();

        $invoice->refresh();

        return $bill;

    }
}

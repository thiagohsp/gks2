<?php

namespace App\Services;

use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class SoftDeleteBillService {

    private BillRepository     $billRepository;
    private InvoiceRepository  $invoiceRepository;
    private CustomerRepository  $customerRepository;

    public function __construct(BillRepository      $billRepository,
                                InvoiceRepository   $invoiceRepository,
                                CustomerRepository  $customerRepository)
	{
		$this->billRepository       = $billRepository;
		$this->invoiceRepository    = $invoiceRepository;
		$this->customerRepository    = $customerRepository;
	}

    public function execute(string $billId) {

        // Log::info('SoftDeleteBillService: '.$billId);

        $bill = $this->billRepository->findById($billId);

        $invoice = $bill->invoice;

        if ($invoice == null || !isset($invoice)) {
            throw new Exception("Bill Not Found!", 0);
            return;
        }

        $previousLetter = $invoice->getPreviousLetter($invoice->last_letter);

        $result = $this->invoiceRepository->update($invoice->id, [
                'balance' => $invoice->balance + $bill->value,
                'total_faturado' =>  $invoice->total_faturado - $bill->value,
                'falta_faturar' =>  $invoice->falta_faturar + $bill->value,
                'last_letter' => $previousLetter
            ]
        );

        $result = $this->customerRepository->update($invoice->customer->id, [
                'balance' => $invoice->customer->balance + $bill->value,
                'total_faturado' =>  $invoice->customer->total_faturado - $bill->value,
                'falta_faturar' =>  $invoice->customer->falta_faturar + $bill->value,
            ]
        );

        if ($result) {

            $result = $this->billRepository->deleteById($bill->id);

            $bill->update([
                'status' => 'C'
            ]);

            $bill->refresh();

            $invoice->refresh();
        }

        //dd($result);

        return $result;

    }
}

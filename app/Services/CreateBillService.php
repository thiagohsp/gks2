<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

class CreateBillService {

    private BillRepository     $billRepository;
    private InvoiceRepository  $invoiceRepository;

    public function __construct(BillRepository     $billRepository,
                                InvoiceRepository  $invoiceRepository)
	{
		$this->billRepository       = $billRepository;
		$this->invoiceRepository    = $invoiceRepository;
	}

    public function execute(string  $invoiceId,
                            string  $batchId,
                            string  $accountId,
                            float   $value,
                            Carbon  $dueDate) {

        Log::info('Invoice: '.$invoiceId);

        $invoice = $this->invoiceRepository->findById($invoiceId, ['*'], ['customer']);

        Log::info('Invoice Model: '.$invoice);

        if ($invoice == null || !isset($invoice)) {
            throw new Exception("Nota Fiscal não encontrada!", 0);
        }


        $nextLetter = $invoice->getNextLetter();

        $bill = $this->billRepository->create([
            'bill_number'   => $invoice->number.'-'.$nextLetter,
            'due_date'      => $dueDate,
            'value'         => $value,
            'payment_date'  => null,
            'payment_value' => null,
            'net_value'     => $value,
            'link'          => null,
            'invoice_id'    => $invoice->id,
            'account_id'    => $accountId,
            'batch_id'      => $batchId
        ]);

        $bill->refresh();

        $this->invoiceRepository->update($invoice->id, [
            'last_letter' => $nextLetter,
            'balance' => $invoice->balance - $value,
        ]);

        $invoice->refresh();

        return $bill;

    }
}

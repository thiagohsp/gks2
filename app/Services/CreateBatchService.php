<?php

namespace App\Services;

use App\Mail\SendBatchMail;
use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\BatchRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateBatchService
{

    private BatchRepository $batchRepository;
    private BillRepository  $billRepository;
    private InvoiceRepository $invoiceRepository;
    private AccountRepository $accountRepository;

	public function __construct(BatchRepository $batchRepository,
                                BillRepository  $billRepository,
                                InvoiceRepository  $invoiceRepository,
                                AccountRepository $accountRepository )
	{
		$this->batchRepository    = $batchRepository;
		$this->billRepository     = $billRepository;
		$this->invoiceRepository  = $invoiceRepository;
		$this->accountRepository  = $accountRepository;
	}

	public function execute(string $code,
                            float  $totalValue,
                            float  $maxValuePerBill = 0,
                            string $email,
                            Carbon $dueDate,
                            string $accountId,
                            Array  $invoices)
	{
        // Incluir um novo lote
        // Criar os boletos de acordo com o Valor Total / Valor Max. por Boleto
        // Vincular boletos ao lote
        // enviar email ao cliente

        Log::info('Executing CreateBatchService...');

        $batch = $this->batchRepository->create([
            'code' => $code,
            'total_value' => $totalValue,
            'max_bill_value' => $maxValuePerBill,
            'email' => $email,
            'status' => 'A', // Aberto
        ]);


        $batch->refresh();

        Log::info('Batch Created: '.$batch);

        $saldoAEmitir = $totalValue;

        $saldoMaxBoleto = $maxValuePerBill > 0 ? $maxValuePerBill : $saldoAEmitir;

        foreach ($invoices as $invoice) {

            // $object = json_decode(json_encode($array), FALSE);

            Log::info('Looping invoices: '.$invoice['id']);

            # code...
            // Busca o saldo da Nota Fiscal
            $saldoNotaFiscal = $invoice['falta_faturar'];
            $sair = false;

            // Verifica se ainda existe saldo do lote a emitir
            if ($saldoAEmitir == 0) break;

            // Loop nos valores para geração dos boletos (enquanto houver saldo)
            while ($saldoAEmitir > 0 && $saldoNotaFiscal > 0 && !$sair) {

                // Se o saldo a emitir, for menor ou igual que o valor máximo por
                // boleto, então, este terá o valor do saldo a emitir
                if ($saldoAEmitir <= $maxValuePerBill) {
                    $saldoMaxBoleto = $saldoAEmitir;
                }

                // Se o saldo da Nota Fiscal for menor que o saldo máximo do boleto
                // Este sairá com o valor do saldo da nota
                if ($saldoNotaFiscal <= $saldoMaxBoleto) {
                    $valorBoleto = $saldoNotaFiscal;
                    $saldoAEmitir = $saldoAEmitir - $valorBoleto;
                    $saldoNotaFiscal = 0;
                    $sair = true;
                } else {
                    $valorBoleto = $saldoMaxBoleto;
                    $saldoAEmitir = $saldoAEmitir - $valorBoleto;
                    $saldoNotaFiscal = $saldoNotaFiscal - $valorBoleto;
                }

                // Cria o novo boleto
                $createBillService = new CreateBillService(
                    $this->billRepository,
                    $this->invoiceRepository,
                    $this->accountRepository
                );

                $bill = $createBillService->execute(
                    $invoice['id'],
                    $batch->id,
                    $accountId,
                    $valorBoleto,
                    $dueDate
                );

            }

        }

        // Send mail
        Mail::to($email)->send(new SendBatchMail($batch->id));

        return $batch;

	}

}

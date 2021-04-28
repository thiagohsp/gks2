<?php

namespace App\Services;

use App\Jobs\SendEmail;
use App\Mail\SendBatchMail;
use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\BatchRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateBatchService
{

    private BatchRepository $batchRepository;
    private BillRepository  $billRepository;
    private InvoiceRepository $invoiceRepository;
    private AccountRepository $accountRepository;
    private CustomerRepository $customerRepository;
    private Client $mainoClient;

	public function __construct(BatchRepository $batchRepository,
                                BillRepository  $billRepository,
                                InvoiceRepository  $invoiceRepository,
                                AccountRepository $accountRepository,
                                CustomerRepository $customerRepository )
	{
		$this->batchRepository    = $batchRepository;
		$this->billRepository     = $billRepository;
		$this->invoiceRepository  = $invoiceRepository;
		$this->accountRepository  = $accountRepository;
		$this->customerRepository  = $customerRepository;

        $url = App::environment('local')
            ? 'https://testes.maino.com.br/api/v2/'
            : 'https://api.maino.com.br/api/v2/';

        $this->mainoClient = new Client([
            // Base URI is used with relative requests
            'base_uri' => $url,
            'headers' => [
                'Content-Type'     => 'application/json',
                'X-Api-Key'        => env('MAINO_KEY')
            ],
        ]);
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

        // Log::info('Executing CreateBatchService...');

        $batch = $this->batchRepository->create([
            'code' =>  strtoupper($code),
            'total_value' => $totalValue,
            'max_bill_value' => $maxValuePerBill,
            'email' => $email,
            'status' => 'A', // Aberto
        ]);


        $batch->refresh();

        $createBillService = new CreateBillService(
            $this->billRepository,
            $this->invoiceRepository,
            $this->accountRepository,
            $this->customerRepository
        );

        $boletosGerados = array();

        $saldoAEmitir = $totalValue;

        $saldoMaxBoleto = $maxValuePerBill > 0 ? $maxValuePerBill : $saldoAEmitir;

        foreach ($invoices as $invoice) {

            // $object = json_decode(json_encode($array), FALSE);

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

                try {
                    $bill = $createBillService->execute(
                        $invoice['id'],
                        $batch->id,
                        $accountId,
                        $valorBoleto,
                        $dueDate
                    );

                    if ($bill != null) {
                        array_push($boletosGerados, $bill);
                    }

                } catch (\Throwable $th) {
                    $boletosGerados = array();
                    Log::error($th->getMessage());
                    return null;
                }

            }

        }

        if (!empty($boletosGerados)) {

            sleep(5);

            foreach ($boletosGerados as $boleto) {
                # code...

                $response = $this->mainoClient->get('contas_a_recebers/'.$boleto->maino_bill_id, [
                    'connect_timeout' => 12
                ]);

                $result = json_decode(($response->getBody()->getContents()));

                $this->billRepository->update($boleto->id, ['link' => $result->link_boleto]);
            }

        }

        return $batch;

	}

}

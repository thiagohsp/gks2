<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CreateBillService {

    private BillRepository     $billRepository;
    private InvoiceRepository  $invoiceRepository;
    private AccountRepository  $accountRepository;
    private CustomerRepository  $customerRepository;
    private $pjBankClient;
    private $mainoClient;

    public function __construct(BillRepository     $billRepository,
                                InvoiceRepository  $invoiceRepository,
                                AccountRepository  $accountRepository,
                                CustomerRepository  $customerRepository)
	{
		$this->billRepository       = $billRepository;
		$this->invoiceRepository    = $invoiceRepository;
		$this->accountRepository    = $accountRepository;
		$this->customerRepository   = $customerRepository;

        $this->pjBankClient = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://sandbox.pjbank.com.br/recebimentos/27b70e6936a45479c637858b3f412832656d8887/',
            'headers' => [
                'Content-Type'     => 'application/json',
                //'X-Api-Key'        => 'e2f9a7d686625ca2329004bb8241f15f'
            ],
        ]);

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

    public function execute(string  $invoiceId,
                            string  $batchId,
                            string  $accountId,
                            float   $value,
                            Carbon  $dueDate) {

        // Log::info('Invoice: '.$invoiceId);

        $invoice  = $this->invoiceRepository->findById($invoiceId, ['*'], ['customer']);
        $account  = $this->accountRepository->findById($accountId, ['codigo_conta_corrente_maino']);

        // Log::info('Invoice Model: '.$invoice);

        if ($invoice == null || !isset($invoice)) {
            throw new Exception("Nota Fiscal não encontrada!", 0);
        }

        $nextLetter = $invoice->getNextLetter();

        $conta_receber = (object) [
            'contas_a_receber' => [
                'valor_a_receber'       => $value,
                'data_prevista'         => $dueDate->format('d/m/Y'),
                'descricao'             => 'VENDA MERCANTIL',
                'numero_titulo'         => str_pad($invoice->number,9,'0',STR_PAD_LEFT).'-'.$nextLetter,
                'numero_fatura'         => str_pad($invoice->number,9,'0',STR_PAD_LEFT),
                'forma_de_pagamento'    => 'Boleto',
                'numero_da_nota_fiscal' => $invoice->number,
                'codigo_conta_corrente' => $account->codigo_conta_corrente_maino
            ],
        ];

        $result = "";

        try {
            //'https://api.maino.com.br/api/v2/notas_fiscais_emitidas'
            $response = $this->mainoClient->post('contas_a_recebers', [
                'connect_timeout' => 10,
                'json' => $conta_receber
            ]);

            $result = json_decode(($response->getBody()->getContents()));

            // if ($result != null) {

            //     sleep(3);

            //     $response = $this->mainoClient->get('contas_a_recebers/'.$result->id, [
            //         'connect_timeout' => 12
            //     ]);

            //     //Log::info($response->getBody()->getContents());

            //     $result = json_decode(($response->getBody()->getContents()));

            // }

        } catch (ClientException $e) {
            $errors = json_decode(substr($e->getMessage(), strpos($e->getMessage(), '{')), true);
            if ($errors == null) {
                $errors  = [
                    'contas_a_recebers' => [
                        $e->getMessage()
                    ]
                ];
            }
        }

        $bill = $this->billRepository->create([
            'bill_number'   => $invoice->number.'-'.$nextLetter,
            'due_date'      => $dueDate,
            'value'         => $value,
            'payment_date'  => null,
            'payment_value' => null,
            'net_value'     => $value,
            'link'          => $result->link_boleto,
            'invoice_id'    => $invoice->id,
            'account_id'    => $accountId,
            'batch_id'      => $batchId,
            'maino_bill_id' => $result->id
        ]);

        $bill->refresh();

        $this->invoiceRepository->update($invoice->id, [
            'last_letter' => $nextLetter,
            'balance' => $invoice->balance - $value,
            'total_faturado' => $invoice->total_faturado + $value,
            'falta_faturar' => $invoice->falta_faturar - $value,
        ]);

        $this->customerRepository->update($invoice->customer->id, [
            'balance' => $invoice->customer->balance - $value,
            'total_faturado' => $invoice->customer->total_faturado + $value,
            'falta_faturar' => $invoice->customer->falta_faturar - $value,
        ]);

        $invoice->refresh();

        if (App::environment('local')) {
            /**
             * Apenas para Testes, gera o boleto no PJBank              *
             */
            try {
                //'https://api.maino.com.br/api/v2/notas_fiscais_emitidas'
                $responsePjBank = $this->pjBankClient->post('transacoes', [
                    'json' => (object) [
                        'vencimento' => $dueDate->format('m/d/Y'),
                        'valor' => $value,
                        'juros' => 0,
                        'juros_fixo' => 0,
                        'multa' => 0,
                        'multa_fixo' => 0,
                        'nome_cliente' => $invoice->customer->social_name,
                        'email_cliente' => $invoice->customer->email,
                        'telefone_cliente' => '',
                        'cpf_cliente' => $invoice->customer->document_number,
                        'endereco_cliente' => $invoice->customer->adress_street,
                        'numero_cliente' => $invoice->customer->adress_number,
                        'bairro_cliente' => $invoice->customer->adress_district,
                        'cidade_cliente' => $invoice->customer->adress_city,
                        'estado_cliente' => $invoice->customer->adress_state,
                        'cep_cliente' => $invoice->customer->adress_zipcode,
                        'texto' => "",
                        'instrucoes' => "Este é um boleto de exemplo",
                        'instrucao_adicional' => "Este boleto não deve ser pago pois é um exemplo",
                        'grupo' => "",
                        //'webhook' => "http://example.com.br",
                        'pedido_numero' => str_pad($invoice->number,9,'0',STR_PAD_LEFT).'-'.$nextLetter,
                        'especie_documento' => "DS"
                    ]
                ]);

                $resultPjBank = json_decode(($responsePjBank->getBody()->getContents()));

            } catch (ClientException $e) {
                $errors = json_decode(substr($e->getMessage(), strpos($e->getMessage(), '{')), true);
                if ($errors == null) {
                    $errors  = [
                        'contas_a_recebers' => [
                            $e->getMessage()
                        ]
                    ];
                }
            }

            $bill = $this->billRepository->update($bill->id, [
                'link' => $resultPjBank->linkBoleto
            ]);
        }

        return $bill;

    }

}

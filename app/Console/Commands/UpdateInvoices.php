<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use App\Services\CreateInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class UpdateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-invoices:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to Maino API and Upsert Invoices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->http = Http::baseUrl('https://api.maino.com.br/api/v2/')
            ->contentType('application/json')
            ->withHeaders([
                'X-Api-Key' => env('MAINO_KEY')
            ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hasNextPage = true;
        $page = 1;
        $invoiceRepository  = new InvoiceRepository(app(Invoice::class));
        $customerRepository = new CustomerRepository(app(Customer::class));
        $accountRepository  = new AccountRepository(app(Account::class));
        $invoiceService = new CreateInvoiceService($invoiceRepository, $customerRepository);

        while ($hasNextPage) {
            # code...

            // Busca as contas correntes //
            // $response = $this->http->get('contas_correntes')->object();

            // if (!isset($response->contas_correntes)) {
            //     $hasNextPage = false;
            //     break;
            // }

            // foreach ($response->contas_correntes as $conta_corrente) {
            //     $contaCorrente = $accountRepository->query()->where(
            //         'codigo_conta_corrente_maino', '=' , $conta_corrente->codigo_conta_corrente
            //     )->first();

            //     if (!isset($contaCorrente) || $contaCorrente->isEmpty()) {
            //         $accountRepository->create([
            //             'codigo_conta_corrente_maino' => $conta_corrente->codigo_conta_corrente,
            //             'bank_number' => $conta_corrente->numero_banco,
            //             'bank_name' => $conta_corrente->nome_banco,
            //             'label' => $conta_corrente->descricao,
            //             'agency' => $conta_corrente->agencia,
            //             'account' => $conta_corrente->conta_corrente,
            //             'allow_pjbank_bills' => $conta_corrente->emite_boleto_pjbank,
            //             'active' => $conta_corrente->ativa,
            //         ]);
            //     }
            // }

            // return;


            $response = $this->http->get('notas_fiscais_emitidas', [
                'page' => $page
            ])->object();

            if (!isset($response->notas_fiscais)) {
                $hasNextPage = false;
                break;
            }

            $cfops_validos = array("6102","5102","6403","5403","6106", "5106");

            foreach ($response->notas_fiscais as $nota_fiscal) {

                $invoice = $invoiceRepository->findByKey($nota_fiscal->chave_acesso_nfe);

                if (!in_array($nota_fiscal->cfop->codigo, $cfops_validos)) {
                    continue;
                }

                Log::alert('Found Invoice by Key: '.$invoice);

                if ($invoice->isEmpty()) {

                    $nota_fiscal->destinatario = (object) array_merge( (array) $nota_fiscal->destinatario,
                        array(
                            'cidade' => $nota_fiscal->municipio->nome,
                            'uf' => $nota_fiscal->uf->nome)
                        );

                    $invoice = $invoiceService->create(
                            $nota_fiscal->chave_acesso_nfe,
                            $nota_fiscal->numero_nfe,
                            Carbon::createFromFormat('Y-m-d?H:i:s.uP', $nota_fiscal->dthr_emissao),
                            $nota_fiscal->serie,
                            $nota_fiscal->cfop->codigo,
                            $nota_fiscal->valor_nota_nfe,
                            $nota_fiscal->status,
                            $nota_fiscal->valor_nota_nfe,
                            'A',
                            $nota_fiscal->destinatario

                    );

                    Log::info($invoice);
                } else {
                    $invoice = $invoice->first();
                }

                $invoiceRepository->update($invoice->id,
                    [
                        'status'                => $nota_fiscal->status,
                        'total_devolucoes'      => $nota_fiscal->financeiro->total_devolucoes,
                        'valor_pedido_liquido'  => $nota_fiscal->financeiro->valor_pedido_liquido,
                        'total_faturado'        => $nota_fiscal->financeiro->total_faturado,
                        'total_liquidado'       => $nota_fiscal->financeiro->total_liquidado,
                        'falta_faturar'         => $nota_fiscal->financeiro->falta_faturar,
                        'falta_liquidar'        => $nota_fiscal->financeiro->falta_liquidar,
                    ]
                );

                // Consulta contas a receber
                $responseContasAReceber = $this->http->get('contas_a_recebers', [
                    'numero_da_nota_fiscal' => $nota_fiscal->numero_nfe,
                    'page' => $page
                ])->object();

                foreach ($responseContasAReceber->contas as $boleto) {

                    foreach ($boleto->tags as $tag) {
                        switch ($tag->nome) {
                            case 'AM':
                            case 'MS':
                            case 'RM':
                            case 'GN':
                                $agent = $tag->nome;
                                break;
                            case 'C':
                                break;
                            default:
                                if (strpos($tag->nome, 'LOTE_') >= 0 or
                                    strpos($tag->nome, 'CAMBIO_') >= 0 or
                                    strpos($tag->nome, 'CÂMBIO_') >= 0 ) {
                                        $operation = $tag->nome;
                                } else {
                                    $agent_2 = $tag->nome;
                                }
                                break;
                        }
                    }

                    $invoiceRepository->update($invoice->id,
                        [
                            //'balance' => $invoice->balance - $boleto->valor,
                            'last_letter' => strpos(trim($boleto->numero_titulo), '-')
                                ?  trim(explode('-',trim($boleto->numero_titulo))[1])
                                :  'A',
                            'agent' => isset($agent) ? $agent : null,
                            'agent_2' => isset($agent_2) ? $agent_2 : null,
                            'operation' => isset($operation) ? $operation : null,

                        ]
                    );

                }

            }

            $hasNextPage = isset($response->pagination->next_page);
            $page++;

        }

    }
}

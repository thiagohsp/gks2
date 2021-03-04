<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateInvoicesCron extends Command
{

    private $http;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-invoice:cron';

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
                'X-Api-Key' => 'e2f9a7d686625ca2329004bb8241f15f'
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
        $invoiceRepository = new InvoiceRepository(app(Invoice::class));
        $customerRepository = new CustomerRepository(app(Customer::class));
        $invoiceService = new InvoiceService($invoiceRepository, $customerRepository);


        while ($hasNextPage) {
            # code...
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
                            'A',
                            $nota_fiscal->valor_nota_nfe,
                            'A',
                            $nota_fiscal->destinatario

                    );

                    Log::info($invoice);
                }

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
                            'balance' => $invoice->balance - $boleto->valor,
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

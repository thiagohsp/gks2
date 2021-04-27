<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Repository\Eloquent\CustomerRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Continue_;

class UpdateCustomersMaino extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-customers-financial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update financial values of Customers from Maino';

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
        $customerRepository = new CustomerRepository(app(Customer::class));

        $hasNextPage = true;
        $page = 1;

        while ($hasNextPage) {
            $response = $this->http->get('stakeholders', [
                'page' => $page
            ])->object();

            if (!isset($response->stakeholders)) {
                $hasNextPage = false;
                break;
            }

            foreach ($response->stakeholders as $stakeholder) {

                if ($stakeholder->cnpj === null) continue;

                $customer = $customerRepository->findByDocument($stakeholder->cnpj)->first();

                //dd($stakeholder);

                if (isset($customer->id)) {


                    $financeiroResponse = $this->http->get('stakeholders/'.$stakeholder->id.'/financeiro_notas_fiscais', [])->object();

                    if (!isset($financeiroResponse->financeiro_notas_fiscais)) continue;

                    $customerRepository->update(
                        $customer->id,
                        [
                            'maino_customer_id'     => $stakeholder->id,
                            'valor_nota_nfe'        => $financeiroResponse->financeiro_notas_fiscais->valor_nota_nfe,
                            'total_devolucoes'      => $financeiroResponse->financeiro_notas_fiscais->total_devolucoes,
                            'valor_pedido_liquido'  => $financeiroResponse->financeiro_notas_fiscais->valor_pedido_liquido,
                            'total_faturado'        => $financeiroResponse->financeiro_notas_fiscais->total_faturado,
                            'total_liquidado'       => $financeiroResponse->financeiro_notas_fiscais->total_liquidado,
                            'falta_faturar'         => $financeiroResponse->financeiro_notas_fiscais->falta_faturar,
                            'falta_liquidar'        => $financeiroResponse->financeiro_notas_fiscais->falta_liquidar,
                        ]
                    );

                }
            }

            $hasNextPage = isset($response->pagination->next_page);
            $page++;
        }

    }
}

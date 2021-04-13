<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Repository\Eloquent\AccountRepository;
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
    protected $signature = 'update-customers-status:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to Receita WS API and Update Customer Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->http = Http::baseUrl('https://www.receitaws.com.br/v1/')
            ->contentType('application/json');

        // parent::__construct();
        // $this->http = Http::baseUrl('https://api.maino.com.br/api/v2/')
        //     ->contentType('application/json')
        //     ->withHeaders([
        //         'X-Api-Key' => env('MAINO_KEY')
        //     ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $hasNextPage = true;
        // $accountRepository  = new AccountRepository(app(Account::class));

        // while ($hasNextPage) {

        //     // Busca as contas correntes //
        //     $response = $this->http->get('contas_correntes')->object();

        //     if (!isset($response->contas_correntes)) {
        //         $hasNextPage = false;
        //         break;
        //     }

        //     foreach ($response->contas_correntes as $conta_corrente) {
        //         $contaCorrente = $accountRepository->query()->where(
        //             'codigo_conta_corrente_maino', '=' , $conta_corrente->codigo_conta_corrente
        //         )->first();

        //         if (!$contaCorrente || !isset($contaCorrente)) {
        //             $accountRepository->create([
        //                 'codigo_conta_corrente_maino' => $conta_corrente->codigo_conta_corrente,
        //                 'bank_number' => $conta_corrente->numero_banco,
        //                 'bank_name' => $conta_corrente->nome_banco,
        //                 'label' => $conta_corrente->descricao,
        //                 'agency' => $conta_corrente->agencia,
        //                 'account' => $conta_corrente->conta_corrente,
        //                 'allow_pjbank_bills' => $conta_corrente->emite_boleto_pjbank,
        //                 'active' => $conta_corrente->ativa,
        //             ]);
        //         }
        //     }

        // }

        // return;

        Log::info('Starting batch process of customers');

        $customerRepository = new CustomerRepository(app(Customer::class));

        $customers = $customerRepository->query()->where('updated_at', '<=', '2021-04-10')->get();
        $count = 0;

        foreach ($customers as $customer) {

            $count++;
            //Log::info($count.' | '.$customer->document_number);

            $response = $this->http->get('cnpj/'.$customer->document_number, [])->object();

            $result = $customerRepository->update($customer->id,
                [ 'is_active' => ($response->situacao == "ATIVA") ]
            );

            Log::notice($result. ' | '.$customer->id. ' is '. ($response->situacao == "ATIVA" ? '' : 'NOT ' ) .'active ');

            if ($count == 3) {
                Log::info('Sleeping...');
                sleep(60);
                $count = 0;
            }

        }


    }

}

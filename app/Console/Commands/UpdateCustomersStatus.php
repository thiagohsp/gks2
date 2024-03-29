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

class UpdateCustomersStatus extends Command
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

        Log::info('Starting batch process of customers');

        $customerRepository = new CustomerRepository(app(Customer::class));

        //$customers = $customerRepository->query()/*->where('updated_at', '<=', '2021-04-10')*/->get();
        $customers = $customerRepository->query()->whereNull('status_cnpj')->get();

        $count = 0;

        foreach ($customers as $customer) {

            $count++;
            //Log::info($count.' | '.$customer->document_number);

            $response = $this->http->get('cnpj/'.$customer->document_number, [])->object();

            $result = $customerRepository->update($customer->id,
                [
                    'is_active' => ($response->situacao == "ATIVA") ,
                    'status_cnpj' => ($response->situacao) ,
                ]
            );

            Log::notice($result. ' | '.$customer->id. ' is '. $response->situacao);

            if ($count == 3) {
                Log::info('Sleeping...');
                sleep(60);
                $count = 0;
            }

        }


    }

}

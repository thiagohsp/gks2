<?php

namespace App\Services;

use App\Repository\CustomerRepositoryInterface;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class InvoiceService
{

	private InvoiceRepository $invoiceRepository;
	private CustomerRepository $customerRepository;

	public function __construct(InvoiceRepositoryInterface $invoiceRepository, CustomerRepositoryInterface $customerRepository)
	{
		$this->invoiceRepository = $invoiceRepository;
		$this->customerRepository = $customerRepository;
	}

	public function create(
        $key,
        $number,
        $date,
        $serie,
        $cfop,
        $value,
        $status,
        $balance,
        $last_letter,
        $destinatario)
	{

        $customer = $this->customerRepository->findByDocument($destinatario->numero_do_documento)->first();

        if (!$customer) {

            Log::info('Cliente não encontrado!');

            $customer = $this->customerRepository->create([
                'document_number'   => $destinatario->numero_do_documento,
                'social_name'       => $destinatario->razao_social,
                'adress_street'     => $destinatario->endereco,
                'adress_number'     => $destinatario->numero,
                'adress_complement' => $destinatario->complemento,
                'adress_district'   => $destinatario->bairro,
                'adress_zipcode'    => $destinatario->cep,
                'adress_city'       => $destinatario->cidade,
                'adress_state'      => $destinatario->uf,
                'adress_country'    => 'BR',
                'email'             => '',
                'customer_balance'  => $value
            ]);

            $customer->refresh();

        }

        $customer->customer_balance = $customer->getOriginal('customer_balance') + $value;
        $customer->save();

        Log::info('Cliente: '.$customer);

		$invoice = $this->invoiceRepository->create([
			'key' => $key,
            'number' => $number,
            'date' => $date,
            'serie' => $serie,
            'cfop' => $cfop,
            'value' => $value,
            'status' => $status,
            'balance' => $balance,
            'last_letter' => $last_letter,
            'customer_id' => $customer->id
		]);

        $invoice->refresh();

        Log::info('Nota Fiscal'.$invoice);

        return $invoice;

	}

}

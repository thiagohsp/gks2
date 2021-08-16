<?php

namespace App\Http\Controllers;

use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    private $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function query(Request $req) {

        $agents_2 = DB::table('invoices')->select(['agent_2'])->distinct()->orderBy('agent_2', 'asc')->get('agent_2');
        $agents = DB::table('invoices')->select(['agent'])->distinct()->orderBy('agent', 'asc')->get('agent_2');

        $result = [
            'agents_2' => $agents_2,
            'agents' => $agents
        ];

        return response()->json($result, 200);
    }
}

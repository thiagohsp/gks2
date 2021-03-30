<?php

namespace App\Http\Controllers;

use App\Repository\BatchRepositoryInterface;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    private $batchRepository;

    public function __construct(BatchRepositoryInterface $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function index() {
        //dd(Auth::user());
        return response()->json($this->batchRepository->all());
    }

    public function store(Request $req) {
        //dd(Auth::user());
        return response()->json($req->all());
    }



}

<?php

namespace App\Http\Controllers;

use App\Repository\BatchRepositoryInterface;
use App\Services\DownloadZipBatchFileService;
use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DownloadController extends Controller
{
    private $batchRepository;

    public function __construct(
        BatchRepositoryInterface   $batchRepository,
    )
    {
        $this->batchRepository   = $batchRepository;
    }

    public function getBatchDownload(Request $req) {

        $batchId = $req->d;

        $batch = $this->batchRepository->findById($batchId, ['*'], ['bills']);

        $downloadBatchService = new DownloadZipBatchFileService($this->batchRepository);

        $zip_file = $downloadBatchService->execute($batchId);

        dd($zip_file);

        if (file_exists($zip_file)) {
          return response()->download($zip_file, $batch->code.'.zip', array('Content-Type: application/zip','Content-Length: '. filesize($zip_file)))->deleteFileAfterSend(true);
        }

        // We return the file immediately after download
        // return response()->download($zip_file);

    }

}

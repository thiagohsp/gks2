<?php

namespace App\Services;

use App\Mail\SendBatchMail;
use App\Models\Batch;
use App\Repository\BatchRepositoryInterface;
use App\Repository\Eloquent\BatchRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\InvoiceRepository;
use FilesystemIterator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class DownloadZipBatchFileService
{

    private BatchRepositoryInterface $batchRepository;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
    )
    {
        $this->batchRepository = $batchRepository;
    }

	public function execute(string $id)
	{

        $batch = $this->batchRepository->findById($id, ['*'], ['bills']);

        if ($batch == null || !isset($batch)) {
            return null;
        }

        $path = storage_path('app') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $batch->code . DIRECTORY_SEPARATOR;
        $zip_file = storage_path('app') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $batch->code . '.zip'; // Name of our archive to download
        $zip = new \ZipArchive();

        /* Deleta todos os arquivos da pasta caso já exista */
        $this->deleteRemittanceFiles($path, true);

        $count = 0;

        $directoryCreated = false;

        foreach ($batch->bills as $bill) {

            if (isset($bill->link)) {

                if (!$directoryCreated)
                    $directoryCreated = File::makeDirectory($path, $mode = 0777, true, true);

                $url  = $bill->link;
                $billPath = $path .
                $bill->bill_number . '_[' .number_format(round($bill->value, 2), 2, ',', '.') . '].pdf';

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_REFERER, $url);

                $data = curl_exec($ch);

                curl_close($ch);

                $result = file_put_contents($billPath, $data);

                if ($result) $count++;
            }
        }

        if ($count <= 0) {
            return null;
        }

        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file) {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $batch->code . '/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        $this->deleteRemittanceFiles($path, true);

        if (file_exists($zip_file)) {
            return $zip_file;
        }

	}

    private function deleteRemittanceFiles($path, $deleteCurrentFolder = false)
    {
        if ($path !== false and !is_dir($path)) return;
        $dir = $path;
        $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ?  rmdir($file) : unlink($file);
        }

        if ($deleteCurrentFolder) {
            rmdir($path);
        }

        return;
    }

}

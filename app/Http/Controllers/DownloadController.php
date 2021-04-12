<?php

namespace App\Http\Controllers;

use App\Repository\BatchRepositoryInterface;
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

        $response = $this->batchRepository->findById($batchId, ['*'], ['bills']);

        if ($response == null || !isset($response)) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid Batch Id'
            ]);
        }

        $path = storage_path('app') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $response->code . DIRECTORY_SEPARATOR;
        $zip_file = storage_path('app') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $response->code . '.zip'; // Name of our archive to download
        $zip = new \ZipArchive();

        /* Deleta todos os arquivos da pasta caso já exista */
        $this->deleteRemittanceFiles($path, true);

        $count = 0;

        foreach ($response->bills as $bill) {

            if (isset($bill->link)) {

                File::makeDirectory($path, $mode = 0777, true, true);

                $url  = $bill->link;
                $billPath = $path .
                    $bill->document . '_[' .number_format(round($bill->value, 2), 2, ',', '.') . '].pdf';

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
            return response('Invalid Batch content', 404);
        }

        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file) {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $response->code . '/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        $this->deleteRemittanceFiles($path, true);

        $headers = array( 'Content-Type: application/zip' );

        if (file_exists($zip_file)) {
            return response()->download($zip_file, $response->code.'.zip', array('Content-Type: application/zip','Content-Length: '. filesize($zip_file)))->deleteFileAfterSend(true);
        } else {
            return ['status'=>'zip file does not exist'];
        }

        // We return the file immediately after download
        // return response()->download($zip_file);

    }

    public function deleteRemittanceFiles($path, $deleteCurrentFolder = false)
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

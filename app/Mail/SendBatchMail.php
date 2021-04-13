<?php

namespace App\Mail;

use App\Models\Batch;
use App\Repository\BatchRepositoryInterface;
use App\Repository\Eloquent\BatchRepository;
use App\Services\DownloadZipBatchFileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBatchMail extends Mailable
{
    use Queueable, SerializesModels;

    private $batchId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $batch = Batch::find($this->batchId);
        $downloadZipBatchFileService = new DownloadZipBatchFileService(new BatchRepository($batch));
        $zipAttachment = $downloadZipBatchFileService->execute($this->batchId);

        return $this->from('naoresponda@gekkoimport.com.br')
            ->view('mails.batch')
            ->subject('Gekko - Novo Lote de Cobrança')
            ->attach($zipAttachment, [
                'as' => $batch->code.'.zip',
                'mime' => 'application/zip',
           ])
            ->with(['batch' => $batch]);
    }
}

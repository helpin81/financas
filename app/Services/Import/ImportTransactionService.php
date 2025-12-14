<?php

namespace App\Services\Import;

use App\Models\ImportModel;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\CategorizationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportTransactionService
{
    protected OfxParserService $ofxParser;
    protected PdfParserService $pdfParser;
    protected CategorizationService $categorizationService;

    public function __construct(
        OfxParserService $ofxParser, 
        PdfParserService $pdfParser,
        CategorizationService $categorizationService
    )
    {
        $this->ofxParser = $ofxParser;
        $this->pdfParser = $pdfParser;
        $this->categorizationService = $categorizationService;
    }

    public function importFile(UploadedFile $file, Account $account, ?ImportModel $model = null): int
    {
        return $this->importFileFromPath($file->getRealPath(), $account, $model, $file->getClientOriginalExtension());
    }

    public function importFileFromPath(string $filePath, Account $account, ?ImportModel $model = null, ?string $extension = null): int
    {
        if (!$extension) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        }
        
        $entries = [];

        if (strtolower($extension) === 'ofx') {
            $entries = $this->ofxParser->parse($filePath);
        } elseif (strtolower($extension) === 'pdf' && $model) {
            $entries = $this->pdfParser->parse($filePath, $model);
        } else {
             throw new \Exception("Formato não suportado ou modelo ausente.");
        }

        return $this->saveTransactions($entries, $account);
    }

    private function saveTransactions(array $entries, Account $account): int
    {
        $count = 0;
        DB::transaction(function () use ($entries, $account, &$count) {
            foreach ($entries as $entry) {
                // Duplicate check: Same account, same date, same amount, same description (fuzzy?)
                // Or use ext_id if available (OFX)
                
                $exists = Transaction::where('account_id', $account->id)
                    ->where('date', $entry['date'])
                    ->where('amount', $entry['amount'])
                    ->where('description', $entry['description'])
                    ->exists();

                if (!$exists) {
                    $transaction = Transaction::create([
                        'user_id' => $account->user_id,
                        'account_id' => $account->id,
                        'date' => $entry['date'],
                        'description' => $entry['description'],
                        'amount' => $entry['amount'],
                        'type' => $entry['type'],
                        'status' => 'paid', // Imported usually means processed
                    ]);
                    
                    $this->categorizationService->categorize($transaction);
                    
                    $count++;
                }
            }
        });

        return $count;
    }
}

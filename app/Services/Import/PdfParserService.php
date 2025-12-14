<?php

namespace App\Services\Import;

use App\Models\ImportModel;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Carbon;

class PdfParserService
{
    public function parse(string $filePath, ImportModel $model): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();
        
        // Split by lines
        $lines = explode("\n", $text);
        $transactions = [];

        // Rules from ImportModel
        // Expected rules format:
        // [
        //   'line_regex' => '/^(\d{2}\/\d{2})\s+(.+?)\s+(-?[\d.,]+)$/', 
        //   'date_pos' => 1,
        //   'desc_pos' => 2,
        //   'amount_pos' => 3
        // ]
        
        $rules = $model->rules;
        $regex = $rules['line_regex'] ?? null;

        if (!$regex) {
            throw new \Exception("No regex rule defined for this model.");
        }

        foreach ($lines as $line) {
            if (preg_match($regex, trim($line), $matches)) {
                
                $dateRaw = $matches[$rules['date_pos'] ?? 1];
                $desc = trim($matches[$rules['desc_pos'] ?? 2]);
                $amountRaw = $matches[$rules['amount_pos'] ?? 3];

                // Normalize Amount (replace , with . and remove thousands separators usually)
                // Brazilian format: 1.234,56 -> 1234.56
                $amount = $this->normalizeAmount($amountRaw);
                
                // Normalize Date (DD/MM -> YYYY-MM-DD or similar)
                // Assuming current year if missing
                $date = $this->normalizeDate($dateRaw);

                $transactions[] = [
                    'date' => $date,
                    'description' => $desc,
                    'amount' => $amount,
                    'type' => $amount < 0 ? 'expense' : 'income',
                ];
            }
        }

        return $transactions;
    }

    private function normalizeAmount(string $amount): float
    {
        // Remove dots (thousands)
        $amount = str_replace('.', '', $amount);
        // Replace comma with dot
        $amount = str_replace(',', '.', $amount);
        return (float) $amount;
    }

    private function normalizeDate(string $datePart): string
    {
        // Assume DD/MM format
        // If YYYY missing, assume current year? Problematic for past years history.
        // MVP: Assume current year.
        if (preg_match('/^\d{2}\/\d{2}$/', $datePart)) {
            $datePart .= '/' . date('Y');
        }
        
        return Carbon::createFromFormat('d/m/Y', $datePart)->format('Y-m-d');
    }
}

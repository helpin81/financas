<?php

namespace App\Services\Import;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Carbon;
use SimpleXMLElement;

class OfxParserService
{
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        // Basic OFX cleanup to make it XML compatible if needed
        // (Often OFX headers are not valid XML)
        $xmlContent = $this->cleanupOfx($content);

        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse OFX XML: " . $e->getMessage());
        }

        $transactions = [];
        // Navigate XML structure (BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKTRANLIST/STMTTRN)
        // This path differs slightly between banks, so we need a flexible finder or standard path
        // For basic OFX 1.0.2:
        $bankTranList = $xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST ?? null;

        if (!$bankTranList) {
             // Try Credit Card structure
             $bankTranList = $xml->CREDITCARDMSGSRSV1->CCSTMTTRNRS->CCSTMTRS->BANKTRANLIST ?? null;
        }

        if (!$bankTranList) {
            throw new \Exception("No transactions found in OFX file.");
        }

        foreach ($bankTranList->STMTTRN as $trn) {
            $transactions[] = [
                'date' => $this->parseDate((string)$trn->DTPOSTED),
                'amount' => (float)$trn->TRNAMT,
                'description' => (string)$trn->MEMO,
                'type' => ((float)$trn->TRNAMT) < 0 ? 'expense' : 'income',
                'external_id' => (string)$trn->FITID,
            ];
        }

        return $transactions;
    }

    private function cleanupOfx(string $content): string
    {
        // OFX Header handling: remove header properties before the SGML/XML
        $pos = strpos($content, '<OFX>');
        if ($pos !== false) {
             $content = substr($content, $pos);
        }
        
        // Remove known non-closing tags in older SGML OFX
        // This is a naive cleanup. Robust parsers often use a library.
        // For MVP, we'll assume relatively clean XML or use a basic regex.
        return $content;
    }

    private function parseDate(string $ofxDate): string
    {
        // Format YYYYMMDDHHMMSS... usually
        return Carbon::parse(substr($ofxDate, 0, 8))->format('Y-m-d');
    }
}

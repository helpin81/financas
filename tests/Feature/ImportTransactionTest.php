<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ImportModel;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Import\ImportTransactionService;
use App\Services\Import\OfxParserService;
use App\Services\Import\PdfParserService;
use App\Services\CategorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class ImportTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_ofx_transactions()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'bank',
        ]);

        // Mock OFX Parser
        $mockOfxParser = Mockery::mock(OfxParserService::class);
        $mockOfxParser->shouldReceive('parse')
            ->once()
            ->andReturn([
                [
                    'date' => '2025-01-01',
                    'amount' => -100.50,
                    'description' => 'Supermercado Teste',
                    'type' => 'expense',
                    'external_id' => '123',
                ],
                [
                    'date' => '2025-01-02',
                    'amount' => 2000.00,
                    'description' => 'Salário',
                    'type' => 'income',
                    'external_id' => '124',
                ],
            ]);

        $service = new ImportTransactionService(
            $mockOfxParser,
            new PdfParserService(),
            new CategorizationService()
        );

        // We use a dummy file as we mocked the parser
        $count = $service->importFileFromPath('dummy.ofx', $account, null, 'ofx');

        $this->assertEquals(2, $count);
        $this->assertDatabaseHas('transactions', [
            'description' => 'Supermercado Teste',
            'amount' => -100.50,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'description' => 'Salário',
            'amount' => 2000.00,
            'user_id' => $user->id,
        ]);
    }

    public function test_can_import_pdf_transactions_with_model()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Card',
            'type' => 'credit_card',
        ]);

        $model = ImportModel::create([
            'user_id' => $user->id,
            'institution_name' => 'Test Bank',
            'file_type' => 'pdf',
            'type' => 'credit_card',
            'rules' => [
                'line_regex' => '/^(\d{2}\/\d{2})\s+(.+?)\s+(-?[\d.,]+)$/',
                'date_pos' => 1,
                'desc_pos' => 2,
                'amount_pos' => 3
            ]
        ]);

        // Mock PDF Parser
        $mockPdfParser = Mockery::mock(PdfParserService::class);
        $mockPdfParser->shouldReceive('parse')
            ->once()
            ->andReturn([
                [
                    'date' => '2025-05-10',
                    'description' => 'UBER *VIAGEM',
                    'amount' => -15.90,
                    'type' => 'expense',
                ]
            ]);

        $service = new ImportTransactionService(
            new OfxParserService(),
            $mockPdfParser,
            new CategorizationService()
        );

        $count = $service->importFileFromPath('dummy.pdf', $account, $model, 'pdf');

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('transactions', [
            'description' => 'UBER *VIAGEM',
            'amount' => -15.90,
        ]);
    }

    public function test_categorizes_transactions_based_on_history()
    {
        $user = User::factory()->create();
        $account = Account::create(['user_id' => $user->id, 'name' => 'Test', 'type' => 'bank']);
        $category = \App\Models\Category::create(['user_id' => $user->id, 'name' => 'Food', 'color' => 'red']);

        // Create history transaction
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => '2025-01-01',
            'description' => 'Ifood Pedido',
            'amount' => -50.00,
            'type' => 'expense',
            'status' => 'paid',
        ]);

        $mockOfxParser = Mockery::mock(OfxParserService::class);
        $mockOfxParser->shouldReceive('parse')
            ->once()
            ->andReturn([
                [
                    'date' => '2025-02-01', // New date
                    'amount' => -60.00,
                    'description' => 'Ifood Pedido', // Same description
                    'type' => 'expense',
                    'external_id' => '125',
                ],
                [
                    'date' => '2025-02-02',
                    'amount' => -100.00,
                    'description' => 'Unknown',
                    'type' => 'expense',
                    'external_id' => '126',
                ]
            ]);

        $service = new ImportTransactionService(
            $mockOfxParser,
            new PdfParserService(),
            new CategorizationService()
        );

        $service->importFileFromPath('dummy.ofx', $account, null, 'ofx');

        $this->assertDatabaseHas('transactions', [
            'description' => 'Ifood Pedido',
            'category_id' => $category->id, // Should match history
            'amount' => -60.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Unknown',
            'category_id' => null, // Should fail
        ]);
    }
}

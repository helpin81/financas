# Implementation Plan - Personal Finance App (MVP)

# Goal Description
Build a responsive web application for personal financial control using Laravel 12 and Filament 4.
Key features: Import OFX/PDF, automatic categorization, and monthly dashboards.

## User Review Required
> [!IMPORTANT]
> **Filament as Main UI:** The plan assumes we are using Filament's Admin Panel as the primary user interface given the requirements for "Admin/UI: Filament 4". If a separate non-admin frontend is needed, please clarify.

## Proposed Changes

### Database Schema (Migrations)

#### [NEW] [create_accounts_table](file:///database/migrations/xxxx_create_accounts_table.php)
- `id`, `user_id`
- `type` (enum: bank, credit_card)
- `name`, `color`
- `limit` (decimal, nullable)
- `closing_day` (int, nullable), `due_day` (int, nullable)

#### [NEW] [create_categories_table](file:///database/migrations/xxxx_create_categories_table.php)
- `id`, `user_id`
- `name`, `color`, `icon`
- `parent_id` (nullable, for nested categories)
- `monthly_limit` (decimal, nullable)

#### [NEW] [create_transactions_table](file:///database/migrations/xxxx_create_transactions_table.php)
- `id`, `user_id`, `account_id`, `category_id` (nullable)
- `date` (date)
- `description` (string)
- `amount` (decimal, signed)
- `type` (enum: income, expense)
- `is_installment` (boolean)
- `installment_id` (nullable)
- `status` (enum: pending, paid, etc.)

#### [NEW] [create_installments_table](file:///database/migrations/xxxx_create_installments_table.php)
- `id`, `user_id`, `transaction_id` (parent)
- `total_amount`, `total_installments`
- `current_installment`

#### [NEW] [create_import_models_table](file:///database/migrations/xxxx_create_import_models_table.php)
- `id`, `user_id`
- `institution_name`
- `file_type` (pdf, ofx, csv)
- `rules` (json: regex patterns for date, desc, amount)

### Filament Resources

#### [NEW] [AccountResource](file:///app/Filament/Resources/AccountResource.php)
- CRUD for Bank Accounts and Credit Cards.

#### [NEW] [CategoryResource](file:///app/Filament/Resources/CategoryResource.php)
- Tree-like or grouped view for Categories.

#### [NEW] [TransactionResource](file:///app/Filament/Resources/TransactionResource.php)
- List view with filters (Date, Account, Category).
- Create/Edit/Delete transactions manually.
- Bulk actions for categorization.

#### [NEW] [ImportModelResource](file:///app/Filament/Resources/ImportModelResource.php)
- Manage regex rules for PDF imports.

### Import Services

#### [NEW] [ImportTransactionService]
- Handle file upload (OFX/PDF).
- Dispatch Jobs for processing.
- Apply `CategorizationEngine`.

## Verification Plan

### Automated Tests
- Unit tests for `CategorizationEngine`.
- Unit tests for `PdfParser` using sample PDFs (mocked text).

### Manual Verification
- Create a Bank Account and a Credit Card.
- Import a sample OFX file.
- Verify transactions appear in the Dashboard.

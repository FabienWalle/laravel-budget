<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class ImportTransactions extends Command
{
    protected $signature = 'import:transactions {filename} {email} {--T|test}';
    protected $description = 'Import transactions from CSV file';

    public function handle(): void
    {
        try {
            $this->executeImportProcess();
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'import: " . $e->getMessage());
        }
    }

    private function executeImportProcess(): void
    {
        $user = $this->getUser($this->argument('email'));
        if (!$user) return;

        $processedData = $this->prepareImportData($user);
        if (!$processedData) return;

        if ($this->option('test')) {
            $this->executeTestMode($processedData, $user);
            return;
        }

        $this->executeImportMode($processedData, $user);
    }

    private function prepareImportData(User $user): ?array
    {
        $csvData = $this->readCsvFile($this->argument('filename'));
        return $csvData ? $this->processCsvData($csvData, $user) : null;
    }

    private function executeTestMode(array $processedData, User $user): void
    {
        $duplicates = $this->findDuplicates($processedData, $user);
        $this->displayTestResults(count($processedData), $duplicates);
    }

    private function executeImportMode(array $processedData, User $user): void
    {
        $duplicates = $this->findDuplicates($processedData, $user);
        $uniqueData = array_filter($processedData, fn($item) => !in_array($item, $duplicates));

        $this->insertTransactions($uniqueData);
        $this->displayImportResults(count($uniqueData), count($duplicates));
    }

    private function getUser(string $email): ?User
    {
        return User::query()->where('email', $email)->first() ?? $this->error("Utilisateur $email non trouvé");
    }

    private function readCsvFile(string $filename): ?array
    {
        $path = storage_path("csv/$filename");

        if (!file_exists($path)) {
            $this->error("Fichier $filename non trouvé dans storage/csv");
            return null;
        }

        try {
            return $this->parseCsvFile($path);
        } catch (\Exception $e) {
            $this->error("Erreur de lecture du CSV: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @throws UnavailableStream
     * @throws InvalidArgument
     * @throws Exception
     */
    private function parseCsvFile(string $path): array
    {
        $content = file_get_contents($path);
        $utf8Content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');

        $tempPath = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempPath, $utf8Content);

        $csv = Reader::createFromPath($tempPath, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());
        unlink($tempPath);

        return $records;
    }

    private function processCsvData(array $csvData, User $user): array
    {
        return array_filter(array_map(function ($row) use ($user) {
            try {
                return $this->createTransactionFromCsvRow($row, $user);
            } catch (\Exception $e) {
                $this->warn("Erreur de traitement d'une ligne: " . $e->getMessage());
                return null;
            }
        }, $csvData));
    }

    private function createTransactionFromCsvRow(array $row, User $user): array
    {
        $amount = $this->calculateAmount($row);

        return [
            'user_id' => $user->getAttribute('id'),
            'operation_date' => Carbon::createFromFormat('Y/m/d', $row['Date operation'])->format('Y-m-d'),
            'value_date' => Carbon::createFromFormat('Y/m/d', $row['Date de valeur'])->format('Y-m-d'),
            'category' => $row['Categorie'],
            'sub_category' => $row['Sous categorie'],
            'description' => $row['Libelle operation'],
            'short_description' => $row['Libelle simplifie'],
            'amount' => (int)round($amount * 100),
            'type' => $row['Type operation'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function calculateAmount(array $row): float
    {
        return !empty($row['Debit'])
            ? (float)str_replace(',', '.', ltrim($row['Debit'], '-')) * -1
            : (float)str_replace(',', '.', $row['Credit']);
    }

    private function findDuplicates(array $data, User $user): array
    {
        $existingTransactions = $this->getExistingTransactions($user);
        $duplicates = [];

        foreach ($data as $item) {
            if ($this->isDuplicateTransaction($item, $existingTransactions)) {
                $duplicates[] = $item;
            }
        }

        return $duplicates;
    }

    private function getExistingTransactions(User $user): array
    {
        return Transaction::query()
            ->where('user_id', $user->getAttribute('id'))
            ->get()
            ->map(function ($transaction) {
                $transaction->amount = (int)round($transaction->amount * 100);
                return $transaction;
            })
            ->keyBy(fn($t) => $this->createTransactionKey(
                $t->operation_date,
                $t->amount,
                $t->description
            ))
            ->toArray();
    }

    private function isDuplicateTransaction(array $item, array $existingTransactions): bool
    {
        $key = $this->createTransactionKey(
            $item['operation_date'],
            $item['amount'],
            $item['description']
        );

        return isset($existingTransactions[$key]);
    }

    private function createTransactionKey(string $operationDate, int $amount, string $description): string
    {
        return sprintf("%s|%s|%s",
            Carbon::parse($operationDate)->format('Y-m-d'),
            $amount,
            trim($description)
        );
    }

    private function displayTestResults(int $totalTransactions, array $duplicates): void
    {
        $this->line('=== TEST DE DETECTION DE DOUBLONS ===');
        $this->line("Transactions analysées: $totalTransactions / Doublons détectés: " . count($duplicates));

        if (!empty($duplicates)) {
            $this->displayDuplicatesTable($duplicates);
        }
    }

    private function displayImportResults(int $importedCount, int $duplicatesCount): void
    {
        $this->info("Import terminé. $importedCount transactions ajoutées.");
        $this->info("$duplicatesCount doublons ignorés.");
    }

    private function displayDuplicatesTable(array $duplicates): void
    {
        $this->table(
            ['Date Opération', 'Montant', 'Description'],
            array_map(fn($d) => [
                $d['operation_date'],
                $this->formatAmount($d['amount']),
                substr($d['description'], 0, 50)
            ], $duplicates)
        );
    }

    private function formatAmount(int $amount): string
    {
        $montant = $amount / 100;
        return ($montant < 0 ? '-' : '') . number_format(abs($montant), 2);
    }

    private function insertTransactions(array $data): void
    {
        if (empty($data)) {
            $this->info("Aucune nouvelle transaction à importer.");
            return;
        }

        $this->executeBatchInsert($data);
    }

    private function executeBatchInsert(array $data): void
    {
        $progressBar = $this->output->createProgressBar(count(array_chunk($data, 500)));

        foreach (array_chunk($data, 500) as $chunk) {
            try {
                DB::table('transactions')->insert($chunk);
            } catch (\Exception $e) {
                $this->error("Erreur d'insertion: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }
}

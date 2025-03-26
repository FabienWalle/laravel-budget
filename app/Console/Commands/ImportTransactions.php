<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportTransactions extends Command
{
    protected $signature = 'import:transactions {filename} {email} {--T|test}';
    protected $description = 'Import transactions from CSV file';

    public function handle(): void
    {
        $filename = $this->argument('filename');
        $userEmail = $this->argument('email');

        $this->info("Début de l'import pour $userEmail...");

        $user = $this->getUser($userEmail);
        if (!$user) return;

        $csvData = $this->readCsvFile($filename);
        if (!$csvData) return;

        $processedData = $this->processCsvData($csvData, $user);
        if (!$processedData) return;

        $duplicates = $this->findDuplicates($processedData, $user);

        if ($this->option('test')) {
            $this->displayTestResults($processedData, $duplicates);
            return;
        }

        $uniqueData = array_filter($processedData, fn($item) => !in_array($item, $duplicates));
        $this->insertTransactions($uniqueData);

        $this->info("Import terminé. " . count($uniqueData) . " transactions ajoutées.");
        $this->info(count($duplicates) . " doublons ignorés.");
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
        } catch (\Exception $e) {
            $this->error("Erreur de lecture du CSV: " . $e->getMessage());
            return null;
        }
    }

    private function processCsvData(array $csvData, User $user): ?array
    {
        return array_filter(array_map(function ($row) use ($user) {
            try {
                $amount = !empty($row['Debit'])
                    ? (float)str_replace(',', '.', ltrim($row['Debit'], '-')) * -1
                    : (float)str_replace(',', '.', $row['Credit']);

                return [
                    'user_id' => $user->id,
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
            } catch (\Exception $e) {
                $this->warn("Erreur de traitement d'une ligne: " . $e->getMessage());
                return null;
            }
        }, $csvData));
    }

    private function findDuplicates(array $data, User $user): array
    {
        $existing = Transaction::query()->where('user_id', $user->getAttribute('id'))
            ->get()
            ->map(function ($transaction) {
                // Convertit le montant en centimes pour la comparaison
                $transaction->amount = (int)round($transaction->amount * 100);
                return $transaction;
            })
            ->keyBy(function ($transaction) {
                return sprintf("%s|%s|%s",
                    $transaction->operation_date->format('Y-m-d'),
                    $transaction->amount,
                    trim($transaction->description)
                );
            });

        $duplicates = [];
        foreach ($data as $item) {
            $key = sprintf("%s|%s|%s",
                $item['operation_date'],
                $item['amount'],
                trim($item['description'])
            );

            if ($existing->has($key)) {
                $duplicates[] = $item;
            }
        }

        return $duplicates;
    }

    private function displayTestResults(array $data, array $duplicates): void
    {
        $this->line('=== TEST DE DETECTION DE DOUBLONS ===');
        $this->line(sprintf(
            "Transactions analysées: %d / Doublons détectés: %d",
            count($data),
            count($duplicates)
        ));

        if (!empty($duplicates)) {
            $this->table(
                ['Date Opération', 'Montant', 'Description'],
                array_map(function ($d) {
                    $montant = $d['amount'] / 100;
                    return [
                        $d['operation_date'],
                        ($montant < 0 ? '-' : '') . number_format(abs($montant), 2),
                        substr($d['description'], 0, 50)
                    ];
                }, $duplicates)
            );
        }
    }

    private function insertTransactions(array $data): void
    {
        if (empty($data)) {
            $this->info("Aucune nouvelle transaction à importer.");
            return;
        }

        $chunks = array_chunk($data, 500);
        $progressBar = $this->output->createProgressBar(count($chunks));

        foreach ($chunks as $chunk) {
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

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
    protected $signature = 'import:transactions {filename} {email}';
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

        $uniqueData = $this->filterDuplicates($processedData, $user);

        $this->insertTransactions($uniqueData, $user);

        $this->info("Import terminé. " . count($uniqueData) . " transactions ajoutées.");
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
            } catch (\Exception $e) {
                $this->warn("Erreur de traitement d'une ligne: " . $e->getMessage());
                return null;
            }
        }, $csvData));
    }

    private function filterDuplicates(array $data, User $user): array
    {
        $existingKeys = Transaction::query()->where('user_id', $user->getAttribute('id'))
            ->get()
            ->mapWithKeys(fn($t) => [
                implode('|', [
                    $t->operation_date,
                    $t->value_date,
                    $t->amount,
                    $t->description
                ]) => true
            ])
            ->toArray();

        return array_filter($data, fn($item) => !isset($existingKeys[
            implode('|', [
                $item['operation_date'],
                $item['value_date'],
                $item['amount'],
                $item['description']
            ])
            ]));
    }

    private function insertTransactions(array $data, User $user): void
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvdsService;
use App\Models\EvdsRate;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\TelegramService;

class EvdsComparison extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:evds-comparison';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receives EVDS data, compares it and sends it to Telegram';

    /**
     * Execute the console command.
     */
    public function handle(EvdsService $evds, TelegramService $telegram)
    {
        $this->info('EVDS rates are being taken...');
            $series = 'TP.DK.USD.A-TP.DK.EUR.A-TP.DK.GBP.A';
            $startDate = now()->startOfMonth()->format('d-m-Y');
            $endDate = now()->format('d-m-Y');

        $data = $evds->getExchangeRates($series, $startDate, $endDate);

        if (isset($data['success']) && $data['success'] === false) {
            $this->error("Could not get rate from API.");
            $this->line("Durum: {$data['status']}");
            $this->line("Cevap: {$data['body']}");
            return;
        }

        if (!isset($data['items']) || empty($data['items'])) {
            $this->error('"items" was not found in the API response.');
            return;
        }

        $latestItem = end($data['items']);
        $latestDate = $latestItem['Tarih'];

        $cachedDate = Cache::get('evds_last_date');

        if ($cachedDate === $latestDate) {
            $this->info("Data is current. Last updated: {$latestDate}");
            return;
        }

        $this->info("New data found ({$latestDate}). Updated...");

        foreach ($data['items'] as $item) {
            $date = Carbon::createFromFormat('d-m-Y', $item['Tarih'])->format('Y-m-d');

            $this->saveRate($date, 'USD', $item['TP_DK_USD_A'] ?? null);
            $this->saveRate($date, 'EUR', $item['TP_DK_EUR_A'] ?? null);
            $this->saveRate($date, 'GBP', $item['TP_DK_GBP_A'] ?? null);
        }

        $latestDate = Carbon::createFromFormat('d-m-Y', $latestItem['Tarih'])->format('Y-m-d');


        Cache::put('evds_last_date', $latestDate, now()->addMinutes(5));

        $this->info("Data Updated. Last Date: {$latestDate}");

        // **Karşılaştırma fonksiyonunu çağırıyoruz**
        $comparisonMessage = $this->compareLatestRates($latestDate);

        if ($comparisonMessage) {
            $telegram->sendMessage($comparisonMessage);
            $this->info("Telegram message sent.");
        } else {
            $this->info("No changes detected.");
        }
    }

    private function saveRate($date, $series, $value)
    {
        if ($value === null) return;

        EvdsRate::updateOrCreate(
            ['date' => $date, 'series' => $series],
            ['value' => $value]
        );
    }

    /**
     * KUR KARŞILAŞTIRMA FONKSİYONU
     **/
    private function compareLatestRates($latestDate)
    {
        $currentRates = EvdsRate::where('date', $latestDate)->get();

        $previousDate = EvdsRate::where('date', '<', $latestDate)
            ->orderBy('date', 'desc')
            ->value('date');

        if (!$previousDate) {
            return null;
        }

        $previousRates = EvdsRate::where('date', $previousDate)->get();

        $message = "*Kur Değişim Raporu*\n";
        $message .= "Tarih: {$latestDate}\n";
        $message .= "Önceki Tarih: {$previousDate}\n\n";

        $hasChange = false;

        foreach ($currentRates as $current) {
            $prev = $previousRates->firstWhere('series', $current->series);

            if (!$prev) continue;

            $diff = $current->value - $prev->value;

            if (abs($diff) > 0.001) {
                $hasChange = true;
                $arrow = $diff > 0 ? '' : '';
                $message .= "{$arrow} *{$current->series}* değişti: " .
                    number_format($prev->value, 4) . " → " .
                    number_format($current->value, 4) . " (Fark: " .
                    number_format($diff, 4) . ")\n";
            }
        }

        return $hasChange ? $message : null;
    }
}

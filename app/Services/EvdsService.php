<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EvdsService
{


    public function __construct()
    {
        //EvdsService::getExchangeRates()
    }

    /**
     * EVDS üzerinden döviz kuru verilerini getirmeyi sağlayan api servisi bilgileri
     */
    public function getExchangeRates()
    {
        $date = now()->format('d-m-Y');
        $url ="https://evds2.tcmb.gov.tr/service/evds/series=TP.DK.USD.A-TP.DK.EUR.A-TP.DK.CHF.A-TP.DK.GBP.A-TP.DK.JPY.A&startDate=01-10-2025&endDate={$date}&type=json"; 

        $response = Http::withHeaders([
            'key' =>//evds api key yazılacak., 
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }
        
        return [
            'success' => false,
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}

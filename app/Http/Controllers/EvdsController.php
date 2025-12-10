<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvdsRate;
use Carbon\Carbon;

class EvdsController extends Controller
{
    public function index(Request $request)
    {   

        $selectedSeries = $request->get('series', 'USD');
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        $rates = EvdsRate::where('series', $selectedSeries)->whereBetween('date', [
                    $startDate->format('d-m-Y'),
                    $endDate->format('d-m-Y')
                ])
                ->orderBy('date', 'asc')
                ->get();

        $dates = $rates->map(function ($item) {
            return Carbon::createFromFormat('d-m-Y', $item->date)->translatedFormat('j F');
        })->toArray();

        $values = $rates->pluck('value')->toArray();

        $allSeries = EvdsRate::select('series')->distinct()->pluck('series')->toArray();

        $lastUpdate = EvdsRate::latest('updated_at')->first()?->updated_at;
        $lastUpdateFormatted = $lastUpdate ? Carbon::parse($lastUpdate)->format('d-m-Y H:i') : 'Veri bulunamadı';

        return view('evds.index', compact('dates', 'values', 'selectedSeries', 'allSeries', 'lastUpdateFormatted'));
    }

}

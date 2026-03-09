<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Flight;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $last7 = Carbon::now()->subDays(7);

        $totalFlights   = Flight::count();
        $flightsToday   = Flight::whereDate('flight_date', $today)->count();
        $flightsLast7   = Flight::where('flight_date', '>=', $last7)->count();

        // Vuelos por día (últimos 7 días) para el gráfico
        $flightsByDay = [];
        $chartLabels  = [];
        $chartData    = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i);
            $count = Flight::whereDate('flight_date', $d)->count();
            $chartLabels[] = $d->format('d/m');
            $chartData[]   = $count;
        }

        // Últimos vuelos (10)
        $flights = Flight::with(['airline', 'aircraft', 'creator'])
            ->orderBy('flight_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $pendingCount = \App\Models\Flight::where('status', 'pending')->count();

        return view('livewire.dashboard', [
            'totalFlights' => $totalFlights,
            'flightsToday' => $flightsToday,
            'flightsLast7' => $flightsLast7,
            'pendingCount'  => $pendingCount,
            'chartLabels'  => $chartLabels,
            'chartData'    => $chartData,
            'flights'      => $flights,
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}

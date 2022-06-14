<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Arr;

class HomeController extends Controller
{

    protected $from = null;
    protected $to   = null;
    protected $maxLeg  = 5;
    protected $flights = [];
    protected $responses = [];

    /**
     * Execute the search request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get request
        $from = $request->input('from');
        $to   = $request->input('to');
        $max  = $request->input('max');
        // Set max multi-leg number
        if ( $max ) {
            $max = intval($max);
            if ( $max > 0 ) {
                $this->maxLeg = $max;
            }
        }
        // Get flight routes
        if ( $from && $to ) {
            $this->from = $from;
            $this->to = $to;
            $this->getFlightRoutes();
        }

        // dd($this->responses);
        return response()->json($this->responses);
    }

    /**
     * Get the flight routes.
     *
     * @return void
     */
    protected function getFlightRoutes()
    {
        // Initialization datas.
        $airportsPath = storage_path() . '/json/airports.json';
        $flightsPath  = storage_path() . '/json/flights.json';
        $airportsJson = file_get_contents($airportsPath);
        $flightsJson  = file_get_contents($flightsPath);
        $airportsAll = json_decode($airportsJson, true);
        $flightsAll  = json_decode($flightsJson, true);

        // Get keyed airports and flights
        // $airports = Arr::keyBy($airportsAll, 'iata');
        $flightsByFrom = Arr::keyByMultiple($flightsAll, 'from');
        $flightsByTo   = Arr::keyByMultiple($flightsAll, 'to');
        $this->flights = $flightsByFrom;

        // Return empty when no from or to flights
        if ( ! isset($flightsByFrom[$this->from], $flightsByTo[$this->to]) ) {
            return;
        }

        // Get flight path
        $array = ['iatas' => [], 'flights' => []];
        $this->getMultiLegFlights($array, $this->from);
    }

    // Get multi-leg flights
    protected function getMultiLegFlights($array, $from)
    {
        $iatas = $array['iatas'] ?? [];
        $flightsFrom = $this->flights[$from] ?? [];
        foreach ( $flightsFrom as $_flight ) {
            // Get flight info
            $_from  = $_flight['from'];
            $_to    = $_flight['to'];
            if ( in_array($_to, $iatas) ) {
                // Filter iata in route
                continue;
            }
            // Add flight info
            $_array = $array;
            $_array['iatas'][] = $_from;
            $_array['iatas'][] = $_to;
            $_array['flights'][] = $_flight;
            // Transit
            if ( $_to == $this->to ) {
                // Return arrived
                $this->responses[] = $_array['flights'];
            } elseif ( count($_array['flights']) >= $this->maxLeg ) {
                // Too many transfers
                continue;
            } else {
                // Get multi-leg flights
                $this->getMultiLegFlights($_array, $_to);
            }
        }
    }

}

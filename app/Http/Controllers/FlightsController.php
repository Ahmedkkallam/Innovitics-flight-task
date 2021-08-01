<?php

namespace App\Http\Controllers;

use App\Flight;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class FlightsController extends Controller
{
    public function AddFlight(Request $request){
        $validation = Validator::make($request->all() ,[
            'originCity'   =>  'required|string',
            'destinationCity'     =>  'required|string',
            'price'     =>  'required|integer',
            'takeOffTime'     =>  'required|date|before:landingTime',
            'landingTime'     =>  'required|date|after:takeOffTime',
        ]);

        if($validation->fails()) {
            return $validation->errors();
        } else {
           return  Flight::create($validation->validated());
        }

    }

    public function SearchFlight(Request $request){
        $validation = Validator::make($request->all() ,[
            'originCity'   =>  'required|string',
            'destinationCity'     =>  'required|string',
            'type' => ['required','integer',Rule::in(['0', '1']),
            ],
        ]);

        if($validation->fails()) {
            return $validation->errors();
        } else {
            $flights = Flight::all();
            switch ($validation->validated()['type']){
                case 0: // cheapest
                    $cheapestDirectFlight = $flights
                        ->where('originCity',$validation->validated()['originCity'])
                        ->where('destinationCity',$validation->validated()['destinationCity'])
                        ->sortBy("price")
                        ->first();
                    $result['result']['total price']= $cheapestDirectFlight->price;
                    $result['result']['schedule']=[$cheapestDirectFlight];

                    $indirectFlights = $flights
                        ->where('originCity',$validation->validated()['originCity'])
                        ->where('destinationCity','!=',$validation->validated()['destinationCity']);

                    foreach ($indirectFlights as $possiableFlight){
                        $cheapestTransit = $flights
                            ->where('originCity',$possiableFlight->destinationCity)
                            ->where('destinationCity',$validation->validated()['destinationCity'])
                            ->sortBy("price")
                            ->first();
                        if($cheapestTransit->count()>0){
                            $schedule = [];
                            array_push($schedule,$possiableFlight);
                            array_push($schedule,$cheapestTransit);
                            $total = $possiableFlight->price+$cheapestTransit->price;
                            if($total < $result['result']['total price']){
                                $result['result']['total price']= $total;
                                $result['result']['schedule'] = $schedule;
                            }
                        }
                    }
                    return $result;
                    break;
                case 1: // fastest
                    $fastestDirectFlight = $flights
                        ->where('originCity',$validation->validated()['originCity'])
                        ->where('destinationCity',$validation->validated()['destinationCity'])
                        ->sortBy("landingTime")
                        ->first();
                    $result['result']['total price']= $fastestDirectFlight->price;
                    $result['result']['schedule']=[$fastestDirectFlight];

                    $indirectFlights = $flights
                        ->where('originCity',$validation->validated()['originCity'])
                        ->where('destinationCity','!=',$validation->validated()['destinationCity']);

                    foreach ($indirectFlights as $possiableFlight){
                        $fastestTransit = $flights
                            ->where('originCity',$possiableFlight->destinationCity)
                            ->where('destinationCity',$validation->validated()['destinationCity'])
                            ->sortBy("landingTime")
                            ->first();
                        if($fastestTransit->count()>0){
                            $schedule = [];
                            array_push($schedule,$possiableFlight);
                            array_push($schedule,$fastestTransit);
                            $total = $possiableFlight->price+$fastestTransit->price;
                            if($fastestTransit->landingTime<$fastestDirectFlight->landingTime){
                                $result['result']['total price']= $total;
                                $result['result']['schedule'] = $schedule;
                            }
                        }
                    }
                    return $result;
                    break;
            }
        }

    }
}

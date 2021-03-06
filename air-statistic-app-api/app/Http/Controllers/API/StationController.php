<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Measurement;
use App\Models\Stand;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StationController extends BaseController
{
    /**
     * show all stations
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $stations = DB::table('stations')->paginate(15);

        if($stations->isEmpty())
            return $this->sendError('Stations not found', 404);

        return $this->sendResponse($stations, 'Stations retrieved successfully.');
    }

    /**
     * show all station coordinates
     * @return \Illuminate\Http\Response
     */
    public function getCords()
    {
        $stations = DB::table('stations')
            ->select('station_code', 'station_name', 'wgs84_n', 'wgs84_e')
            ->where("wgs84_n",">=","-90")
            ->where("wgs84_n","<=", "90")
            ->where("wgs84_e",">=","-90")
            ->where("wgs84_e","<=", "90")
            ->get();

        if($stations->isEmpty())
            return $this->sendError('Cords not found', 404);

        return $this->sendResponse($stations, 'Cords retrieved successfully.');
    }


    /**
     * show one station
     * @param $station_code
     * @return \Illuminate\Http\Response
     */
    public function getStation($station_code)
    {
        if(strtolower($station_code) != 'getcords') {
            $station = DB::table('stations')->where('station_code', '=', $station_code)->get();

            if ($station->isEmpty())
                return $this->sendError('Station not found', 404);

            return $this->sendResponse($station, 'Station retrieved successfully.');
        } else return $this->getCords();
    }

    /**
     * get all stands for station
     * @param $station_code
     * @return \Illuminate\Http\Response
     */
    public function getAllStands($station_code)
    {
        $stands = DB::table('stations')
            ->join('stands', 'stations.station_code', '=', 'stands.station_code')
            ->join('measurements', 'stands.stand_code', '=', 'measurements.stand_code')
            ->select('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
            ->selectRaw("array_to_string((array_agg(measurements.measurement_value))[1:15], ', ') measurement_values")
            ->selectRaw("array_to_string((array_agg(DISTINCT measurements.measurement_date))[1:15], ', ') measurement_dates")
            ->where('stations.station_code', '=', $station_code)
            ->groupBy('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
            ->get();
//        $stands = DB::table()

/*
        $stands = DB::table('stations')
            ->where('stations.station_code', '=', $station_code)
            ->join('stands', 'stations.station_code', '=', 'stands.station_code')
            ->join('measurements', 'stands.stand_code', '=', 'measurements.stand_code')
            ->select('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
            ->toSql();*/

            if ($stands->isEmpty()) {
                return $this->sendError('Data not found', 404);
            }


        return $this->sendResponse($stands, 'Data retrieved successfully.');
    }

    /**
     * get all stands for station
     * @param $stand_code
     * @return \Illuminate\Http\Response
     */
    public function getStand($stand_code)
    {
        $stand = DB::table('stands')
            ->join('measurements', 'stands.stand_code', '=', 'measurements.stand_code')
            ->select('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
            ->selectRaw("array_to_string((array_agg(measurements.measurement_value)), ', ') measurement_values")
            ->selectRaw("array_to_string((array_agg(DISTINCT measurements.measurement_date)), ', ') measurement_dates")
            ->where('stands.stand_code', '=', $stand_code)
            ->groupBy('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
            ->get();
//        $stands = DB::table()

        /*
                $stands = DB::table('stations')
                    ->where('stations.station_code', '=', $station_code)
                    ->join('stands', 'stations.station_code', '=', 'stands.station_code')
                    ->join('measurements', 'stands.stand_code', '=', 'measurements.stand_code')
                    ->select('stands.stand_code', 'stands.indicator_code', 'stands.indicator')
                    ->toSql();*/

        if ($stand->isEmpty()) {
            return $this->sendError('Data not found', 404);
        }


        return $this->sendResponse($stand, 'Data retrieved successfully.');
    }

    public function getAvailableVoivodeship()
    {
        $v = DB::table('stations')
            ->distinct()
            ->select('voivodeship')
            ->get();

        if($v->isEmpty())
            return $this->sendError('Station not found', 404);

        return $this->sendResponse($v, 'Station retrieved successfully.');
    }

    /**
     * show stations - advanced query
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getStationsAdv(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'voivodeship' => 'required|string',
            'location' => 'nullable|string',
            'adress' => 'nullable|string',
            'indicator' => 'nullable|string',
            'measurement_type' => 'nullable|string',
            'start_date' => 'nullable|date',
            'close_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $results = DB::table('stations')
            ->join('stands', 'stations.station_code', '=', 'stands.station_code')
            ->select('stands.stand_code', 'stations.station_code', 'stations.station_name', 'stands.indicator')
            ->where('stations.voivodeship', '=', $request->voivodeship);

        if($request->filled('location'))
        {
            $results = $results->where('stations.location', '=', $request->location);
        }

        if($request->filled('adress'))
        {
            $results = $results->where('stations.adress', '=', $request->adress);
        }

        if($request->filled('indicator'))
        {
            $results = $results->where('stands.indicator', '=', $request->indicator);
        }

        if($request->filled('measurement_type'))
        {
            $results = $results->where('stands.measurement_type', '=', $request->measurement_type);
        }

        if($request->filled('start_date'))
        {
            $results = $results->where('stations.start_date', '=', $request->start_date);
        }

        if($request->filled('close_date'))
        {
            $results = $results->where('stations.close_date', '=', $request->close_date);
        }

        $results = $results->paginate(10);

        if($results->isEmpty())
            return $this->sendError('Station not found', 404);

        return $this->sendResponse($results, 'Station retrieved successfully.');
    }

    /**
     * show stations - advanced query
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getStationsAdv2(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'station_type' => 'nullable|string',
            'location_type' => 'nullable|string',
            'voivodeship' => 'nullable|string',
            'location' => 'nullable|string',
            'station_code' => 'nullable|string',
            'indicator' => 'nullable|string',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $results = DB::table('stations')
            ->join('stands', 'stations.station_code', '=', 'stands.station_code')
            ->select('stands.stand_code', 'stations.station_code', 'stations.station_name', 'stands.indicator');

        if($request->filled('station_type'))
        {
            $results = $results->where('stations.station_type', '=', $request->station_type);
        }

        if($request->filled('location_type'))
        {
            $results = $results->where('stations.location_type', '=', $request->location_type);
        }

        if($request->filled('voivodeship'))
        {
            $results = $results->where('stations.voivodeship', '=', $request->voivodeship);
        }

        if($request->filled('location'))
        {
            $results = $results->where('stations.location', '=', $request->location);
        }

        if ($request->filled('indicator')) {
            $results = $results->where('stations.indicator', '=', $request->indicator);
        }

        if($request->filled('station_code'))
        {
            $results = $results->where('stands.station_code', '=', $request->station_code);
        }

        $results = $results->paginate(10);

        if($results->isEmpty())
            return $this->sendError('Station not found', 404);

        return $this->sendResponse($results, 'Station retrieved successfully.');
    }

}

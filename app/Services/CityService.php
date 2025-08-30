<?php

namespace App\Services;

use App\Models\City;

class CityService
{

    public function index()
    {
        return City::with('country')->get();
    }

    public function store(array $data)
    {
        return City::create($data);
    }

    public function show(string $id)
    {
        return City::with('country')->findOrFail($id);
    }


    public function update(array $data, string $id)
    {
        $city = $this->show($id);
        $city->update($data);
        return $city;
    }


    public function destroy(string $id)
    {
        $city = $this->show($id);
        $city->delete();
        return $city;
    }

    public function getCitiesByCountry($countryId)
    {

        return City::where('country_id', operator: $countryId)->with('country')->get();
    }

    
}

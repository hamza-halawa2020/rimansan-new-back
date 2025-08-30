<?php

namespace App\Services;

use App\Models\Country;

class CountryService
{
    public function index()
    {
        return Country::all();
    }

    public function store(array $data)
    {
        return Country::create($data);
    }

    public function show(string $id)
    {
        return Country::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $country = $this->show($id);
        $country->update($data);
        return $country;
    }

    public function destroy(string $id)
    {
        $country = $this->show($id);
        $country->delete();
        return $country;
    }
}

<?php

namespace App\Services;

use App\Models\ContactUs;

class ContactService
{
    public function index()
    {
        return ContactUs::paginate(10);
    }

    public function store(array $data)
    {
        return ContactUs::create($data);
    }

    public function show(string $id)
    {
        return ContactUs::findOrFail($id);
    }

    public function destroy(string $id)
    {
        $contact = $this->show($id);
        $contact->delete();
        return $contact;
    }
}

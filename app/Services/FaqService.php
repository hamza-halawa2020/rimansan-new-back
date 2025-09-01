<?php

namespace App\Services;

use App\Models\Faq;

class FaqService
{

    public function index()
    {
        return  Faq::with('admin')->get();
    }

    public function store(array $data)
    {
        return Faq::create($data);
    }

    public function show(string $id)
    {
        return Faq::with('admin')->findOrFail($id);
    }

    public function update(array $data, string $id)
    {

        $faq = $this->show($id);
        $faq->update($data);
        return $faq;
    }
    public function destroy(string $id)
    {
        $faq = $this->show($id);
        $faq->delete();
        return $faq;
    }
}

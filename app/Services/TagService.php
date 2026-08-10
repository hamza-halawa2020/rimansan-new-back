<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function index()
    {
        return Tag::all();
    }

    public function store(array $data)
    {
        return Tag::create($data);
    }

    public function show(string $id)
    {
        return Tag::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $tag = $this->show($id);
        $tag->update($data);
        return $tag;
    }

    public function destroy(string $id)
    {
        $tag = $this->show($id);
        $tag->delete();
        return $tag;
    }
}

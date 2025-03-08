<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TranslationController extends Controller
{
    private $langs = ['ar', 'en'];
    private $basePath = 'public/i18n/';

    public function index()
    {
        $translations = [];
        foreach ($this->langs as $lang) {
            $filePath = $this->basePath . "{$lang}.json";
            if (Storage::exists($filePath)) {
                $translations[$lang] = json_decode(Storage::get($filePath), true);
            } else {
                $translations[$lang] = [];
            }
        }
        return response()->json($translations);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'lang' => 'required|in:ar,en',
            'translations' => 'required|array',
        ]);

        $filePath = $this->basePath . "{$data['lang']}.json";
        Storage::put($filePath, json_encode($data['translations'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['message' => 'Translation updated successfully']);
    }

    public function download($lang)
    {
        if (!in_array($lang, $this->langs)) {
            return response()->json(['error' => 'Invalid language'], 400);
        }

        $filePath = $this->basePath . "{$lang}.json";
        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::download($filePath, "{$lang}.json", [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*', // Ensure CORS for download
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationController extends Controller
{


    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
    }



    private $langs = ['ar', 'en'];
    private $basePath = 'i18n/'; // Relative to public/ directory

    // public function index()
    // {
    //     $translations = [];
    //     foreach ($this->langs as $lang) {
    //         $filePath = public_path($this->basePath . "{$lang}.json");
    //         if (file_exists($filePath)) {
    //             $translations[$lang] = json_decode(file_get_contents($filePath), true);
    //         } else {
    //             $translations[$lang] = [];
    //         }
    //     }
    //     return response()->json($translations)
    //         ->header('Access-Control-Allow-Origin', '*'); // Add CORS header
    // }

    public function index()
{
    $translations = [];
    $version = []; // لتخزين إصدار كل لغة
    foreach ($this->langs as $lang) {
        $filePath = public_path($this->basePath . "{$lang}.json");
        if (file_exists($filePath)) {
            $translations[$lang] = json_decode(file_get_contents($filePath), true);
            // استخدام تاريخ التعديل الأخير كإصدار
            $version[$lang] = filemtime($filePath); // Unix timestamp لآخر تعديل
        } else {
            $translations[$lang] = [];
            $version[$lang] = '0'; // إصدار افتراضي لو الملف مش موجود
        }
    }
    return response()->json([
        'translations' => $translations,
        'version' => $version
    ])->header('Access-Control-Allow-Origin', '*');
}

    public function update(Request $request)
    {
        $data = $request->validate([
            'lang' => 'required|in:ar,en',
            'translations' => 'required|array',
        ]);

        $filePath = public_path($this->basePath . "{$data['lang']}.json");

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Write the JSON file
        file_put_contents($filePath, json_encode($data['translations'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['message' => 'Translation updated successfully'])
            ->header('Access-Control-Allow-Origin', '*'); // Add CORS header
    }

    public function download($lang)
    {
        if (!in_array($lang, $this->langs)) {
            return response()->json(['error' => 'Invalid language'], 400);
        }

        $filePath = public_path($this->basePath . "{$lang}.json");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, "{$lang}.json", [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*', // Ensure CORS for download
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    use ApiResponse;

    private TranslationService $translationService;

    function __construct(TranslationService $translationService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->translationService = $translationService;
    }

    public function index()
    {
        return $this->success($this->translationService->index())
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'lang' => 'required|in:ar,en',
            'translations' => 'required|array',
        ]);

        $this->translationService->update($data['lang'], $data['translations']);

        return $this->success(null, 'Translation updated successfully')
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function download($lang)
    {
        if (!$this->translationService->isSupported($lang)) {
            return $this->error('Invalid language', 400);
        }

        $filePath = $this->translationService->path($lang);
        if (!file_exists($filePath)) {
            return $this->error('File not found', 404);
        }

        return response()->download($filePath, "{$lang}.json", [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}

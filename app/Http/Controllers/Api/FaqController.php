<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Exception;
use Illuminate\Support\Facades\Gate;

class FaqController extends Controller
{

    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }

    public function index()
    {
        try {
            $Faqs = Faq::paginate(10);
            return FaqResource::collection($Faqs);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreFaqRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {
                $Faq = Faq::create($validatedData);

                return response()->json(['data' => new FaqResource($Faq)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store Faq.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Faq = Faq::findOrFail($id);
            return new FaqResource($Faq);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateFaqRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (Gate::allows("is-admin")) {
                $Faq = Faq::findOrFail($id);
                $Faq->update($validatedData);
                return response()->json(['data' => new FaqResource($Faq)], 200);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Faq = Faq::findOrFail($id);
                $Faq->delete();
                return response()->json(['data' => 'Faq deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete Faq.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}

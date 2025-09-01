<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Services\FaqService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class FaqController extends Controller
{

    use ApiResponse;
    private $userId;
    private FaqService $faqService;

    function __construct(FaqService $faqService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->faqService = $faqService;
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Faqs = $this->faqService->index();
            return $this->success(FaqResource::collection($Faqs));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreFaqRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {
                $Faq = $this->faqService->store($validatedData);
                return $this->success(new FaqResource($Faq));
            } else {
                return $this->error('not allow to Store Faq.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Faq = $this->faqService->show($id);
            return $this->success(new FaqResource($Faq));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateFaqRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (Gate::allows("is-admin")) {
                $faq = $this->faqService->update($validatedData, $id);
                return response()->json(['data' => new FaqResource($faq)], 200);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $faq = $this->faqService->destroy($id);
                return $this->success(['data' => 'Faq deleted successfully']);
            } else {
                return $this->error('not allow to delete Faq.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}

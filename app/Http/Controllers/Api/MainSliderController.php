<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveMainSliderRequest;
use App\Http\Requests\StoreMainSliderRequest;
use App\Http\Requests\UpdateMainSliderRequest;
use App\Http\Resources\MainSliderResource;
use App\Models\MainSlider;
use Exception;
use Illuminate\Support\Facades\Gate;

class MainSliderController extends Controller
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
            $MainSliders = MainSlider::where('status', 'active')->paginate(10);
            return MainSliderResource::collection($MainSliders);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function all()
    {
        try {
            $addSideBarBanner = MainSlider::paginate(10);
            return MainSliderResource::collection($addSideBarBanner);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function store(StoreMainSliderRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/main-sliders/';
                    $image->move(public_path($folderPath), $filename);
                }
                $validatedData['image'] = $filename ?? 'default.png';
                $MainSlider = MainSlider::create($validatedData);
                return response()->json(['data' => new MainSliderResource($MainSlider)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store MainSlider.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $MainSlider = MainSlider::findOrFail($id);
            return new MainSliderResource($MainSlider);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function active(ActiveMainSliderRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $MainSlider = MainSlider::findOrFail($id);
                $MainSlider->update($validatedData);
                return response()->json(['data' => new MainSliderResource($MainSlider)], 200);
            } else {
                return response()->json(['message' => 'not allow to active MainSlider.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateMainSliderRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (Gate::allows("is-admin")) {
                $MainSlider = MainSlider::findOrFail($id);


                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/main-sliders/';
                    if ($MainSlider->image && $MainSlider->image !== 'images/main-sliders/default.png' && file_exists(public_path($MainSlider->image))) {
                        unlink(public_path($MainSlider->image));
                    }
                    $image->move(public_path($folderPath), $filename);
                    $validatedData['image'] =  $filename;
                }

                $MainSlider->update($validatedData);
                return response()->json(['data' => new MainSliderResource($MainSlider)], 200);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $MainSlider = MainSlider::findOrFail($id);
                $MainSlider->delete();
                return response()->json(['data' => 'MainSlider deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete MainSlider.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddSideBarBannerRequest;
use App\Http\Requests\UpdateAddSideBarBannerRequest;
use App\Http\Resources\AddSideBarBannerResource;
use App\Models\AddSideBarBanner;
use Exception;
use Illuminate\Support\Facades\Gate;

class AddSideBarBannerController extends Controller
{
    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
    }

    public function index()
    {
        try {
            $addSideBarBanner = AddSideBarBanner::where('status', 'active')->paginate(10);
            return AddSideBarBannerResource::collection($addSideBarBanner);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            $addSideBarBanner = AddSideBarBanner::paginate(10);
            return AddSideBarBannerResource::collection($addSideBarBanner);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function store(StoreAddSideBarBannerRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/side-bar/';
                    $image->move(public_path($folderPath), $filename);

                }

                $validatedData['image'] = $filename ?? 'default.png';

                $addSideBarBanner = AddSideBarBanner::create($validatedData);
                return response()->json(['data' => new AddSideBarBannerResource($addSideBarBanner)], 200);
            } else {
                return response()->json(['message' => 'not allow to StoreAddSideBarBanner.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $addSideBarBanner = AddSideBarBanner::findOrFail($id);
            return new AddSideBarBannerResource($addSideBarBanner);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateAddSideBarBannerRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (!Gate::allows("is-admin")) {
                return response()->json(['message' => 'Not allowed to updateAddSideBarBanner.'], 403);
            }

            $addSideBarBanner = AddSideBarBanner::findOrFail($id);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $folderPath = 'images/side-bar/';

                if ($addSideBarBanner->image && $addSideBarBanner->image !== 'images/side-bar/default.png' && file_exists(public_path($addSideBarBanner->image))) {
                    unlink(public_path($addSideBarBanner->image));
                }

                $image->move(public_path($folderPath), $filename);
                $validatedData['image'] = $filename;
            }

            $addSideBarBanner->update($validatedData);
            return response()->json(['data' => new AddSideBarBannerResource($addSideBarBanner)], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $addSideBarBanner = AddSideBarBanner::findOrFail($id);

                if ($addSideBarBanner->image && $addSideBarBanner->image !== 'images/side-bar/default.png' && file_exists(public_path($addSideBarBanner->image))) {
                    unlink(public_path($addSideBarBanner->image));
                }

                $addSideBarBanner->delete();
                return response()->json(['data' => 'addSideBarBanner deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to deleteAddSideBarBanner.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
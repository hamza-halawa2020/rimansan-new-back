<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Http\Resources\SocialLinkResource;
use App\Models\SocialLink;
use Exception;
use Illuminate\Support\Facades\Gate;

class SocialLinkController extends Controller
{

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
    }

    public function index()
    {
        try {
            $SocialLinks = SocialLink::paginate(10);
            return SocialLinkResource::collection($SocialLinks);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreSocialLinkRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {

                if ($request->hasFile('icon')) {
                    $image = $request->file('icon');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/socials/';
                    $image->move(public_path($folderPath), $filename);

                }

                $validatedData['icon'] = $filename ?? 'default.png';

                $SocialLink = SocialLink::create($validatedData);
                return response()->json(['data' => new SocialLinkResource($SocialLink)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store SocialLink.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $SocialLink = SocialLink::findOrFail($id);
            return new SocialLinkResource($SocialLink);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateSocialLinkRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (!Gate::allows("is-admin")) {
                return response()->json(['message' => 'Not allowed to update SocialLink.'], 403);
            }

            $socialLink = SocialLink::findOrFail($id);
            if ($request->hasFile('icon')) {
                $image = $request->file('icon');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $folderPath = 'images/socials/';

                if ($socialLink->icon && $socialLink->icon !== 'images/social/default.png' && file_exists(public_path($socialLink->icon))) {
                    unlink(public_path($socialLink->icon));
                }

                $image->move(public_path($folderPath), $filename);
                $validatedData['icon'] = $folderPath . $filename;
            }

            $socialLink->update($validatedData);
            return response()->json(['data' => new SocialLinkResource($socialLink)], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $socialLink = SocialLink::findOrFail($id);

                if ($socialLink->icon && $socialLink->icon !== 'images/socials/default.png' && file_exists(public_path($socialLink->icon))) {
                    unlink(public_path($socialLink->icon));
                }

                $socialLink->delete();
                return response()->json(['data' => 'SocialLink deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete SocialLink.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use Exception;
use Illuminate\Support\Facades\Gate;

class CertificationController extends Controller
{
    private $userId;
    function __construct()
    {
        $this->middleware("auth:sanctum")->except('index', 'show', 'showBySerialNumber', 'downloadFile');
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Certifications = Certification::all();
            return CertificationResource::collection($Certifications);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreCertificationRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();

                if ($request->hasFile('file')) {
                    $image = $request->file('file');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/Certifications/';
                    $image->move(public_path($folderPath), $filename);
                }
                $validatedData['file'] = $filename ?? 'default.png';
                $Certification = Certification::create($validatedData);
                return response()->json(['data' => new CertificationResource($Certification),], 201);
            }
            return response()->json(['message' => 'Not allowed to store Certifications.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to add Certification. Please try again later.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Certification = Certification::findOrFail($id);
            return new CertificationResource($Certification);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateCertificationRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Certification = Certification::find($id);

                // Check if the Certification exists
                if (!$Certification) {
                    return response()->json(['message' => 'Certification not found.'], 404);
                }

                $validatedData = $request->validated();

                if ($request->hasFile('file')) {
                    $image = $request->file('file');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/Certifications/';

                    // Delete the old image if it exists
                    if ($Certification->file && $Certification->file !== 'default.png' && file_exists(public_path($folderPath . $Certification->file))) {
                        unlink(public_path($folderPath . $Certification->file));
                    }

                    $image->move(public_path($folderPath), $filename);
                    $validatedData['file'] = $filename;
                }

                $Certification->update($validatedData);

                return response()->json(new CertificationResource($Certification), 200);
            }

            return response()->json(['message' => 'Not allowed to update Certifications.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the Certification.'], 500);
        }
    }



    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Certification = Certification::findOrFail($id);
                $Certification->delete();
                return response()->json(['data' => 'Certification deleted successfully'], 200);
            }
            return response()->json(['message' => 'Not allowed to update Certifications.'], 403);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function showBySerialNumber(string $serialNumber)
    {
        try {
            $certificate = Certification::where('serial_number', $serialNumber)->first();

            if (!$certificate) {
                return response()->json(['error' => 'Certificate not found'], 404);
            }

            return new CertificationResource($certificate);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function downloadFile(string $id)
    {
        try {
            $certificate = Certification::findOrFail($id);

            $filePath = public_path('images/Certifications/' . $certificate->file);

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            return response()->download($filePath, $certificate->file);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

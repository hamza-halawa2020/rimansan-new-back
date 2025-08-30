<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Services\CertificationService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class CertificationController extends Controller
{
    use ApiResponse;
    private $userId;
    private CertificationService $certificationService;
    function __construct(CertificationService $certificationService)
    {
        $this->middleware("auth:sanctum")->except('index', 'show', 'showBySerialNumber', 'downloadFile');
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->certificationService = $certificationService;
    }

    public function index()
    {
        try {
            $Certifications = $this->certificationService->index();
            return $this->success(CertificationResource::collection($Certifications));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StoreCertificationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $certifiaction = $this->certificationService->store($validatedData);
                return $this->success(new CertificationResource($certifiaction), 'certifiaction created successfully', 201);
            } else {
                return $this->errro('not allow to Store certifiaction.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $certifiaction = $this->certificationService->show($id);
            return $this->success(new CertificationResource($certifiaction));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function update(UpdateCertificationRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (!Gate::allows("is-admin")) {
                return $this->error('Not allowed to update Certification.', 403);
            }

            $Certification = $this->certificationService->update($validatedData, $id);
            return $this->success(new CertificationResource($Certification), 'Certification updated successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }




    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->certificationService->destroy($id);
                return $this->success(null, 'Certification deleted successfully', 204);
            } else {
                return $this->error('not allow to delete Certification.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function showBySerialNumber(string $serialNumber)
    {
        try {
            $Certification = $this->certificationService->showBySerialNumber($serialNumber);
            if (!$Certification) {
                return $this->error('Certificate not found', 404);
            }
            return $this->success(new CertificationResource($Certification), 'Certification fetched successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function downloadFile(string $id)
    {
        try {

            $file = $this->certificationService->downloadFile($id);

            if (!file_exists($file['path'])) {
                return $this->error('Certificate not found', 404);
            }
            return response()->download($file['path'], $file['filename']);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}

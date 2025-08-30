<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileService
{
    public function upload(UploadedFile $file, string $path = 'uploads'): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $extension;

        $folderPath = public_path($path);
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $file->move($folderPath, $filename);

        return $path . '/' . $filename;
    }

    public function delete(string $path): bool
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}

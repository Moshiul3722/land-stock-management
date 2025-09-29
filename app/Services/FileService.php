<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class FileService
{
    public function store($module, $file, $disk = 'public', $path = '')
    {
        $path = Storage::disk($disk)->put($path, $file);

        $userId = Auth::check() ? Auth::user()->id : 0;

        $fileRecord = File::create([
            'file_name' => $file->getClientOriginalName(),
            'module_name' => $module,
            'path' => $path,
            'disk' => $disk,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        return $fileRecord;
    }


    public function retrieve($fileId)
    {
        $fileRecord = File::find($fileId);
        if (!$fileRecord) {
            return response()->json(['error' => 'File record not found'], 404);
        }

        $disk = $fileRecord->disk;
        $filePath = $fileRecord->path;

        if (Storage::disk($disk)->exists($filePath)) {
            $fileContents = Storage::disk($disk)->get($filePath);
            return [
                'file_name' => $fileRecord->file_name,
                'file_contents' => base64_encode($fileContents),
                'path' => $filePath,
                'file_id' => $fileId
            ];
        }
        return response()->json(['error' => 'File not found in storage'], 404);
    }

    public function delete($fileId)
    {
        $fileRecord = File::find($fileId);
        if (!$fileRecord) {
            return response()->json(['error' => 'File record not found'], 404);
        }
        try {
            $fileRecord->delete();
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}

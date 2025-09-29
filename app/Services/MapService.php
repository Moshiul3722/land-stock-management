<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class MapService
{
    public function load()
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
}

<?php

namespace App\Http\Controllers\Api\FileHandler;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileHandlerController extends BaseController
{
    public function uploadFileToLocal(Request $request)
    {
        $guessExtension = $request->file('file')->guessExtension();
        $fileNameWithExt = $request->filename . '#' . Carbon::now()->format('dmyhi') . '.' . $guessExtension;
        $directory = $request->directory;
        $request->file('file')->storeAs('public/files/' . $directory, $fileNameWithExt, 'local');
        $pathURL = Storage::disk('local')->url('public/files/' . $directory . '/' . $fileNameWithExt);

        $result = [
            "pathURL" => $pathURL,
            "size" => $request->file("file")->getSize(),
            "extention" => $guessExtension,
            "fileNameWithExt" => $fileNameWithExt,
            "directory" => $directory,
            "filename" => $request->filename,
        ];

        return $this->successResponse("Image berhasil di simpan", $result);
    }
}

<?php

namespace App\Http\Controllers\Api\FileHandler;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function uploadFileToS3(Request $request)
    {
        if (File::exists($request->file)) {
            $guessExtension = $request->file('file')->guessExtension();
            $fileNameWithExt = $request->filename . '#' . Carbon::now()->format('dmyhi') . '.' . $guessExtension;
            Storage::disk('s3')->put("original/" . $guessExtension . "/" . $fileNameWithExt, File::get($request->file));
            $pathURL = Storage::disk('s3')->url("original/" . $guessExtension . "/" . $fileNameWithExt);
            return $this->successResponse("Image berhasil di simpan", $pathURL);
        }
    }

    public function uploadFileResize(Request $request)
    {
        $guessExtension = $request->file('file')->guessExtension();
        $fileNameWithExt = $request->filename . '.' . $guessExtension;
        $directory = $request->directory;
        $request->file('file')->storeAs('public/files/' . $directory, $fileNameWithExt, 'local');
        $imageDetails = resizeImageAll($directory, $fileNameWithExt, $request->filename);
        $resultImageDetails = array();

        foreach ($imageDetails as $imageTypes) {
            foreach ($imageTypes as $value) {
                $imagePath = storage_path('app/public/files' . $value["image_url"]);
                $path = uploadToS3Bucket($value["extention"] . '/' . $value["type"] . '/' . $directory . '/' . $request->filename . '#' . Carbon::now()->format('dmyhi') . '.' . $value["extention"], $imagePath);

                $dataImage = [
                    "extention" => $value["extention"],
                    "type" => $value["type"],
                    "image_url" => $path,
                    "name" => $value["name"]
                ];

                $resultImageDetails = [$dataImage, ...$resultImageDetails];

                unlink($imagePath);
            }
        }
        return $this->successResponse("Image berhasil di simpan", $resultImageDetails);
    }

    public function moveToS3(Request $request)
    {
        $filename = $request->filename;
        $fileNameWithExt = $request->fileNameWithExt;
        $directory = $request->directory;
        $imageDetails = resizeImageAll($directory, $fileNameWithExt, $filename);

        $pathUrls = [];

        foreach ($imageDetails as $imageTypes) {
            foreach ($imageTypes as $value) {
                $imagePath = storage_path('app/public/files' . $value["image_url"]);
                $path = uploadToS3Bucket($value["extention"] . '/' . $value["type"] . '/' . $directory . '/' . $request->filename . '#' . Carbon::now()->format('dmyhis') . '.' . $value["extention"], $imagePath);
                $pathUrls = [...$pathUrls, $path];
                unlink($imagePath);
            }
        }
        return $this->successResponse("Image berhasil di simpan", ["source" => $imageDetails, "pathUrls" => $pathUrls]);
    }
}

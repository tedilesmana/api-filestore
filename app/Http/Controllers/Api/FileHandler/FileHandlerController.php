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
    public function deleteFileInS3(Request $request)
    {
        try {
            $images = '[
                {
                    "extention": "png",
                    "type": "thumbnail",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/png/thumbnail/tes33/logo-paramadina-white%230607230227.png",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "webp",
                    "type": "thumbnail",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/webp/thumbnail/tes33/logo-paramadina-white%230607230227.webp",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "png",
                    "type": "small",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/png/small/tes33/logo-paramadina-white%230607230227.png",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "webp",
                    "type": "small",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/webp/small/tes33/logo-paramadina-white%230607230227.webp",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "png",
                    "type": "medium",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/png/medium/tes33/logo-paramadina-white%230607230227.png",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "webp",
                    "type": "medium",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/webp/medium/tes33/logo-paramadina-white%230607230227.webp",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "png",
                    "type": "large",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/png/large/tes33/logo-paramadina-white%230607230227.png",
                    "name": "logo-paramadina-white"
                },
                {
                    "extention": "webp",
                    "type": "large",
                    "image_url": "https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/webp/large/tes33/logo-paramadina-white%230607230227.webp",
                    "name": "logo-paramadina-white"
                }
            ]';
            $arrImage = json_decode($images);
            $result = [];
            foreach ($arrImage as $key => $value) {
                $fullPath = $value->image_url;
                $lengthBaseUrlS3 = strlen('https://sip-data-storage.s3.ap-southeast-1.amazonaws.com/');
                $cutPath = substr($fullPath, $lengthBaseUrlS3);
                $path = str_replace('%23', '#', $cutPath);
                $response = Storage::disk('s3')->delete($path);
                $itemResult = [
                    "image_url" => $fullPath,
                    "status" => $response
                ];

                $result = [...$result, $itemResult];
            }

            return $this->successResponse("Image berhasil di simpan", $result);
        } catch (\Throwable $th) {
            return $this->badResponse("Image berhasil di simpan", $th);
        }
    }

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

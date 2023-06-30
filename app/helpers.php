<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;
use WebPConvert\WebPConvert;

function generalDateFormat($date)
{
    return Carbon::parse($date)->format('d M Y');
}

function generateCode($last_code, $initial_code)
{
    if (!$last_code) {
        return $initial_code . '-00000000001';
    }

    $explode_seller_code = explode('-', $last_code);

    if (count($explode_seller_code) > 1) {
        $code = $explode_seller_code[2];
        $string = preg_replace("/[^0-9\.]/", '', $code);
        return $initial_code . '-' . sprintf('%011d', $string + 1);
    } else {
        return $initial_code . '-00000000001';
    }
}

function decodePhoneNumber($phone)
{
    $phone_number = explode('#', $phone);

    return $phone_number[0];
}

function setQueryList($request, $columns, $id = 'id')
{
    //filter
    $key_filter = '';
    $list_column = [];
    $value_filter = [];
    if ($request->filter) {
        $list_filter = explode("|", $request->filter);

        for ($i = 0; $i < count($list_filter); $i++) {
            $item_keyword = explode("=", $list_filter[$i]);
            $key_keyword = $item_keyword[0];
            $list_column = [...$list_column, $key_keyword];
            $value_keyword = $item_keyword[1];
            $keyword = '%' . $value_keyword . '%';
            if ($i == 0) {
                $key_filter = $key_filter . "`$key_keyword` like ?";
                $value_filter = [...$value_filter, $keyword];
            } else {
                $key_filter = $key_filter . " and `$key_keyword` like ?";
                $value_filter = [...$value_filter, $keyword];
            }
        };
    }

    $fromDate = $request->query('from');
    $toDate = $request->query('to');

    $isSearchDateRange = !empty($fromDate) &&
        !is_null($fromDate) && $fromDate && !empty($toDate) &&
        !is_null($toDate) && $toDate;

    $fromDateTime = $fromDate . " 00:00:00";
    $toDateTime = $toDate . " 23:59:59";

    $list_key = "";

    if ($request->filter && $request->search) {
        $list_key = $isSearchDateRange ? " (" . $key_filter . " and " . "date_created >= ?" . " and " . "date_created <= ?" . " ) " : " (" . $key_filter . " ) ";
    }

    if ($request->filter && !$request->search) {
        $list_key = $isSearchDateRange ? " (" . $key_filter . " and " . "date_created >= ?" . " and " . "date_created <= ?" . " ) " : " (" . $key_filter . " ) ";
    }

    if (!$request->filter && $request->search) {
        $list_key = $isSearchDateRange ? " (" . "date_created >= ?" . " and " . "date_created <= ?" . " ) " : "";
    }

    if (!$request->filter && !$request->search) {
        $list_key = $isSearchDateRange ? " (" . " date_created >= ?" . " and " . "date_created <= ?" . " ) " : "";
    }

    $list_val = $isSearchDateRange ? [...$value_filter, $fromDateTime, $toDateTime] : [...$value_filter];

    $queryKey = strlen($list_key) > 0 ? $list_key : " (`$id` like ? ) ";
    $queryVal = count($list_val) > 0 ? $list_val : ["%%"];

    //searching
    $key_searching = '';
    $value_searching = [];

    if ($request->search) {
        $keyword = '%' . $request->search . '%';
        for ($i = 0; $i < count($columns); $i++) {
            $column = $columns[$i];
            if ($i == 0) {
                $key_searching = $key_searching . "`$column` like ?";
                $value_searching = [...$value_searching, $keyword];
            } else {
                $key_searching = $key_searching . " or `$column` like ?";
                $value_searching = [...$value_searching, $keyword];
            }
        }
    }

    $querySearchKey = strlen($key_searching) > 0 ? " (" . $key_searching  . " ) " : " (`$id` like ? ) ";
    $querySearchVal = count($value_searching) > 0 ? $value_searching : ["%%"];

    return [
        "queryKey" => $queryKey,
        "queryVal" => $queryVal,
        "querySearchKey" => $querySearchKey,
        "querySearchVal" => $querySearchVal,
        "listColumn" => $list_column
    ];
}

function setPagination($data)
{
    $pagination = (object) [
        "current_page" => $data->toArray()["current_page"],
        "first_page_url" => $data->toArray()["first_page_url"],
        "from" => $data->toArray()["from"],
        "last_page" => $data->toArray()["last_page"],
        "last_page_url" => $data->toArray()["last_page_url"],
        "links" => $data->toArray()["links"],
        "next_page_url" => $data->toArray()["next_page_url"],
        "path" => $data->toArray()["path"],
        "per_page" => $data->toArray()["per_page"],
        "prev_page_url" => $data->toArray()["prev_page_url"],
        "to" => $data->toArray()["to"],
        "total" => $data->toArray()["total"]
    ];

    return $pagination;
}

function uploadImage($fileImage, $image, $directory, $type, $filename)
{
    $directoryFile = $directory . '/' . Carbon::now()->format('Y-M-d');
    $file = $fileImage;
    $fileSize = $file->getSize();
    $extention = $file->extension();
    $fileName = $filename;
    $imageNameWithExtention = $fileName . '.' . $extention;

    if ($extention == 'svg') {
        $image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'svg',
            "type" => $type,
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }

    if ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'png') {
        $image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'svg',
            "type" => $type,
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }

    if ($extention == 'webp') {
        $image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'webp',
            "type" => $type,
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }
}

function uploadVideo($video)
{
    $video->validate([
        'video' => 'required|file|mimetypes:video/mp4',
    ]);

    $videoName = time() . '.' . $video->extension();

    $video->move(public_path('videos'),  $videoName);

    return url('') . '/' . $videoName;
}

function uploadFile($file)
{
    $file->validate([
        'file' => 'required|csv,txt,xlx,xls,pdf|max:2048',
    ]);

    $fileName = time() . '.' . $file->extension();

    $file->move(public_path('files'), $fileName);

    return url('') . '/' . $fileName;
}

function deleteImage($image_url)
{
    return File::delete(storage_path('app/public/image' . $image_url));
}

function resizeImageAll($directory, $imageNameWithExtension, $fileName)
{
    $optimizerChain = (new OptimizerChain)
        ->addOptimizer(new Jpegoptim([
            '-m85',
            '--strip-all',
            '--all-progressive',
        ]))
        ->addOptimizer(new Pngquant([
            '--force',
        ]))
        ->addOptimizer(new Optipng([
            '-i0',
            '-o2',
            '-quiet',
        ]))
        ->addOptimizer(new Svgo([
            '--disable=cleanupIDs',
        ]))
        ->addOptimizer(new Gifsicle([
            '-b',
            '-O3'
        ]))
        ->addOptimizer(new Cwebp([
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 90',
        ]));

    $multiSizeImage = new \Guizoxxv\LaravelMultiSizeImage\MultiSizeImage($optimizerChain);
    $pathFile = storage_path('app/public/files/' . $directory . '/' . $imageNameWithExtension);
    dd($pathFile);
    $images = $multiSizeImage->processImage($pathFile);
    $imageDetails = array();

    foreach ($images as $image) {
        $imageNameWithExtention = substr($image, 33);
        $output = '';
        $type = '';

        if (str_contains($imageNameWithExtention, 'lg')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@lg' . '.webp');
            $type .= 'large';
            convertToWebp($image, $output);
        } else if (str_contains($imageNameWithExtention, 'md')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@md' . '.webp');
            $type .= 'medium';
            convertToWebp($image, $output);
        } else if (str_contains($imageNameWithExtention, 'sm')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@sm' . '.webp');
            $type .= 'small';
            convertToWebp($image, $output);
        } else {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@tb' . '.webp');
            $type .= 'thumbnail';
            convertToWebp($image, $output);
        }

        $dataImage = [
            "size" => Storage::size(substr($image, 21)),
            "extention" => File::extension(substr($image, 21)),
            "type" => $type,
            "image_url" => substr($image, 33),
            "name" => $fileName
        ];

        $dataWebp = [
            "size" => Storage::size(substr($output, 21)),
            "extention" => File::extension(substr($output, 21)),
            "type" => $type,
            "image_url" => substr($output, 33),
            "name" => $fileName
        ];

        $dataImages = [
            "webp" => $dataWebp,
            "image" => $dataImage
        ];

        $imageDetails = [$dataImages, ...$imageDetails];
    }

    return $imageDetails;
}

function resizeImageOriginal($directory, $imageNameWithExtension, $fileName)
{
    $optimizerChain = (new OptimizerChain)
        ->addOptimizer(new Jpegoptim([
            '-m85',
            '--strip-all',
            '--all-progressive',
        ]))
        ->addOptimizer(new Pngquant([
            '--force',
        ]))
        ->addOptimizer(new Optipng([
            '-i0',
            '-o2',
            '-quiet',
        ]))
        ->addOptimizer(new Svgo([
            '--disable=cleanupIDs',
        ]))
        ->addOptimizer(new Gifsicle([
            '-b',
            '-O3'
        ]))
        ->addOptimizer(new Cwebp([
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 90',
        ]));

    $multiSizeImage = new \Guizoxxv\LaravelMultiSizeImage\MultiSizeImage($optimizerChain);
    $pathFile = storage_path('app/public/files/' . $directory . '/' . $imageNameWithExtension);
    $images = $multiSizeImage->processImage($pathFile);
    $imageDetails = array();

    foreach ($images as $image) {
        $imageNameWithExtention = substr($image, 33);
        $type = '';

        if (str_contains($imageNameWithExtention, 'lg')) {
            $type .= 'large';
        } else if (str_contains($imageNameWithExtention, 'md')) {
            $type .= 'medium';
        } else if (str_contains($imageNameWithExtention, 'sm')) {
            $type .= 'small';
        } else {
            $type .= 'thumbnail';
        }

        $dataImage = [
            "size" => Storage::size(substr($image, 21)),
            "extention" => File::extension(substr($image, 21)),
            "type" => $type,
            "image_url" => substr($image, 33),
        ];

        $dataImage = [
            "image" => $dataImage
        ];

        $imageDetails = [$dataImage, ...$imageDetails];
    }

    return $imageDetails;
}

function resizeImageToWebp($directory, $imageNameWithExtension, $fileName)
{
    $optimizerChain = (new OptimizerChain)
        ->addOptimizer(new Jpegoptim([
            '-m85',
            '--strip-all',
            '--all-progressive',
        ]))
        ->addOptimizer(new Pngquant([
            '--force',
        ]))
        ->addOptimizer(new Optipng([
            '-i0',
            '-o2',
            '-quiet',
        ]))
        ->addOptimizer(new Svgo([
            '--disable=cleanupIDs',
        ]))
        ->addOptimizer(new Gifsicle([
            '-b',
            '-O3'
        ]))
        ->addOptimizer(new Cwebp([
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 90',
        ]));

    $multiSizeImage = new \Guizoxxv\LaravelMultiSizeImage\MultiSizeImage($optimizerChain);
    $pathFile = storage_path('app/public/files/' . $directory . '/' . $imageNameWithExtension);
    $images = $multiSizeImage->processImage($pathFile);
    $imageDetails = array();

    foreach ($images as $image) {
        $imageNameWithExtention = substr($image, 33);
        $output = '';
        $type = '';

        if (str_contains($imageNameWithExtention, 'lg')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@lg' . '.webp');
            $type .= 'large';
            convertToWebp($image, $output);
        } else if (str_contains($imageNameWithExtention, 'md')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@md' . '.webp');
            $type .= 'medium';
            convertToWebp($image, $output);
        } else if (str_contains($imageNameWithExtention, 'sm')) {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@sm' . '.webp');
            $type .= 'small';
            convertToWebp($image, $output);
        } else {
            $output .= storage_path('app/public/files/' . $directory . '/' . $fileName . time() . '@tb' . '.webp');
            $type .= 'thumbnail';
            convertToWebp($image, $output);
        }

        $dataImage = [
            "size" => Storage::size(substr($image, 21)),
            "extention" => File::extension(substr($image, 21)),
            "type" => $type,
            "image_url" => substr($image, 33),
        ];

        $dataWebp = [
            "size" => Storage::size(substr($output, 21)),
            "extention" => File::extension(substr($output, 21)),
            "type" => $type,
            "image_url" => substr($output, 33),
        ];

        $dataImage = [
            "webp" => $dataWebp
        ];

        $imageDetails = [$dataImage, ...$imageDetails];
    }

    return $imageDetails;
}

function uploadToGoogleStorage($image_path, $filename)
{
    $disk = Storage::disk('gcs');
    $fileSource = fopen($image_path, 'r');
    $result = $disk->write($filename, $fileSource);
    return $result;
}

function uploadToS3Bucket($file_path, $file)
{
    if (File::exists($file)) {
        $fileSource = fopen($file, 'r+');
        Storage::disk('s3')->put($file_path, $fileSource);
        $pathURL = Storage::disk('s3')->url($file_path);
        return $pathURL;
    }
}

function convertToWebp($input, $output)
{
    $options = [

        // failure handling
        'fail'                 => 'original',   // ('original' | 404' | 'throw' | 'report')
        'fail-when-fail-fails' => 'throw',      // ('original' | 404' | 'throw' | 'report')

        // options influencing the decision process of what to be served
        'reconvert' => false,         // if true, existing (cached) image will be discarded
        'serve-original' => false,    // if true, the original image will be served rather than the converted
        'show-report' => false,       // if true, a report will be output rather than the raw image

        // warning handling
        'suppress-warnings' => true,            // if you set to false, make sure that warnings are not echoed out!

        // options when serving an image (be it the webp or the original, if the original is smaller than the webp)
        'serve-image' => [
            'headers' => [
                'cache-control' => true,
                'content-length' => true,
                'content-type' => true,
                'expires' => false,
                'last-modified' => true,
                'vary-accept' => false
            ],
            'cache-control-header' => 'public, max-age=31536000',
        ],

        // redirect tweak
        'redirect-to-self-instead-of-serving' => false,  // if true, a redirect will be issues rather than serving

        'convert' => [
            'quality' => 'auto',
        ]
    ];

    return WebPConvert::convert($input, $output, $options);
}

function uploadImageForSellerIdentity($request, $directory)
{
    $directoryFile = $directory . '/' . Carbon::now()->format('Y-M-d');


    $file = $request->file('image');
    $fileSize = $file->getSize();
    $extention = $file->extension();
    $fileName = time();
    $imageNameWithExtention = $fileName . '.' . $extention;

    if ($extention == 'svg') {
        $request->image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'svg',
            "type" => 'identity_card_image',
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }

    if ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'png') {
        $request->image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'svg',
            "type" => 'general',
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }

    if ($extention == 'webp') {
        $request->image->move(storage_path('app/public/image/' . $directoryFile), $imageNameWithExtention);

        $data = [
            "size" => $fileSize,
            "extention" => 'webp',
            "type" => 'general',
            "image_url" => '/' . $directoryFile . '/' . $imageNameWithExtention,
        ];

        $dataImage = [
            "image" => $data
        ];

        $imagePath = storage_path('app/public/image/' .  $directoryFile . '/' . $imageNameWithExtention);
        uploadToGoogleStorage($imagePath, $directoryFile . '/' . $imageNameWithExtention);

        return [$dataImage];
    }
}

function sendNotificationUsingTopic()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
            "notification": {
                "body": "subject",
                "title": "title"
            },
            "priority": "high",
            "data": {
                "click_action": "FLUTTER_NOTIFICATION_CLICK",
                "id": "1",
                "status": "done",
                "sound": "default",
                "screen": "yourTopicName"
            },
            "to": "/topics/customerWithDriver"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: key=AAAASdzIVK4:APA91bH5F9g2cAhJWdvx1rD2KVWv1vUIllgFLE7uHUz0OXNUh_zTEG4TZm4takgj3GIH8TsLAiZQXcJKRaUJwIKZ6b4MV2TO_6hDcr76doT4mh02yc5Lvc6izCPQAdHI9C8_8oo4jMCZ'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function sendNotification($receiver, $notification, $data)
{
    $SERVER_API_KEY = "AAAASdzIVK4:APA91bH5F9g2cAhJWdvx1rD2KVWv1vUIllgFLE7uHUz0OXNUh_zTEG4TZm4takgj3GIH8TsLAiZQXcJKRaUJwIKZ6b4MV2TO_6hDcr76doT4mh02yc5Lvc6izCPQAdHI9C8_8oo4jMCZ";

    $notificationBody = [
        "to"            => $receiver,
        "priority"      => "high",
        "data"          => $data,
        "notification"  => [
            "title" => $notification->title,
            "body"  => $notification->body,
        ]
    ];

    $json_data = json_encode($notificationBody);

    $headers = [
        'Authorization: key=' . $SERVER_API_KEY,
        'Content-Type: application/json',
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    $response = curl_exec($ch);

    curl_close($ch);

    Log::debug(json_encode($response));
}

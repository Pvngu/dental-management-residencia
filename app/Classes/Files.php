<?php

namespace App\Classes;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

class Files
{
    /**
     * Get the URL of a file stored in S3.
     *
     * @param string $path
     * @return string
     */

    public static function getFileS3Url($path , $expires = 5)
    {
        $awsEndpoint = config('filesystems.disks.s3.endpoint');
        $awsBucket = config('filesystems.disks.s3.bucket');

        $file_url = $awsEndpoint . '/' . $awsBucket . '/' . ltrim($path);
        $file =  Storage::disk('s3')->temporaryUrl($file_url, now()->addMinutes(5));
        
        return $file;
    }

    /**
     * @param $image
     * @param $dir
     * @param null $width
     * @param int $height
     * @param $crop
     * @return string
     * @throws \Exception
     */

    public static function upload($image, $dir, $width = null, $height = 800, $crop = false)
    {
        config(['filesystems.default' => 's3']);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $image;
        $folder = $dir;

        if (!$uploadedFile->isValid()) {
            throw new \Exception('File was not uploaded correctly');
        }   

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());
        $newPath = $folder . '/' . $newName;

        $imageStream = Image::read($uploadedFile->getRealPath());

        if (!empty($crop)) {
            // Crop image logic remains complex, focusing on the main upload path first.
            // This part might need adjustments if cropping is used with S3.
        }

        if (($width || $height)) {
            $imageStream->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Get the image content as a string
        $imageContent = (string) $imageStream->encode();

        // Upload to S3
        Storage::disk('s3')->put($newPath, $imageContent, 'private');

        // Return the path without the base URL
        return $newPath;
    }

    public static function generateNewFileName($currentFileName)
    {
        $ext = strtolower(File::extension($currentFileName));
        $newName = md5(microtime());

        if ($ext === '') {
            return $newName;
        }

        return $newName . '.' . $ext;
    }

    public static function uploadLocalOrS3($uploadedFile, $dir)
    {
        if (!$uploadedFile->isValid()) {
            throw new \Exception('File was not uploaded correctly');
        }

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());
        $path = $dir . '/' . $newName;

        if (config('filesystems.default') === 's3') {
            Storage::disk('s3')->putFileAs($dir, $uploadedFile, $newName, 'private');
        } else {
            // Local storage
            $uploadedFile->storeAs($dir, $newName);
        }

        return $path;
    }

    public static function deleteFile($image, $folder)
    {
        $dir = trim($folder, '/');
        $path = $dir . '/' . $image;

        if (!File::exists(public_path($path))) {
            Storage::delete($path);
        }

        return true;
    }

    public static function deleteDirectory($folder)
    {
        $dir = trim($folder);
        Storage::deleteDirectory($dir);
        return true;
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AvatarService
{
    public function processAvatar(
        UploadedFile $file,
        int $userId
    ): string {
        // create path for avatar
        $directory = "avatars/{$userId}";
        $filename = Str::uuid() . '.' . $file->extension();
        $avatarPath = "{$directory}/{$filename}";

        // initialize ImageManager with GD driver
        $manager = new ImageManager(new Driver());

        // process the image with Intervention Image
        $image = $manager->read($file->path());

        // resize (max 600px)
        $max = 600;
        $image->resize($max, $max, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // create temp directory if it doesn't exist
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // save & compress to target ~0.5MB
        $tempPath = $tempDir . '/' . Str::random(40);
        $quality = 80;

        $image->save($tempPath, $quality);
        while (filesize($tempPath) > 500000 && $quality > 20) {
            $quality -= 5;
            $image->save($tempPath, $quality);
        }

        // store avatar to storage
        Storage::disk('public')->put($avatarPath, file_get_contents($tempPath));

        // clean up temp file
        unlink($tempPath);

        // return avatar path
        return $avatarPath;
    }
}

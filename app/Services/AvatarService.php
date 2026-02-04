<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Exceptions\ProfileException;
use App\Models\User;

class AvatarService
{
    public const MIN_IMAGE_DIMENSION = 100;
    public const MAX_IMAGE_DIMENSION = 4096;
    private const MAX_PROCESSED_IMAGE_DIMENSION = 400;
    private const TARGET_FILE_SIZE = 500000;
    private const INITIAL_QUALITY = 85;
    private const MIN_QUALITY = 30;

    /**
     * Process and store user avatar/image
     */
    private function processAndStoreUserAvatar(
        UploadedFile $file,
        int $userId
    ): string {
        try {
            // create path for avatar
            $directory = "avatars/{$userId}";
            $filename = Str::uuid() . '.' . $file->extension();
            $avatarPath = "{$directory}/{$filename}";

            // initialize ImageManager with GD driver
            $manager = new ImageManager(new Driver());

            // process the image with Intervention Image
            $image = $manager->read($file->path());

            // resize
            $image->scaleDown(self::MAX_PROCESSED_IMAGE_DIMENSION, self::MAX_PROCESSED_IMAGE_DIMENSION);

            // create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');

            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // generate temp file path
            $tempPath = $tempDir . '/' . Str::random(40);

            // compress & save
            $quality = self::INITIAL_QUALITY;

            $image->save($tempPath, $quality);

            while (filesize($tempPath) > self::TARGET_FILE_SIZE && $quality > self::MIN_QUALITY) {
                $quality -= 5;
                $image->save($tempPath, $quality);
            }

            // store avatar to storage
            Storage::disk('public')->put($avatarPath, file_get_contents($tempPath));

            // clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // return avatar path
            return $avatarPath;
        } catch (\Throwable $e) {
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            throw ProfileException::avatarProcessFailed($userId, $e);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(
        User $user,
        UploadedFile $avatar
    ): string {
        try {
            if ($user->avatar) {
                $this->deleteImagePath($user);
            }

            return $this->processAndStoreUserAvatar($avatar, $user->id);
        } catch (\Throwable $e) {
            throw ProfileException::avatarUploadFailed($user->id, $e);
        }
    }

    /**
     * Delete user avatar/image path
     */
    public function deleteImagePath(User $user): void
    {
        try {
            Storage::disk('public')->delete($user->avatar);
        } catch (\Throwable $e) {
            throw ProfileException::avatarDeleteFailed($user->id, $e);
        }
    }

    /**
     * Delete user avatar/image directory
     */
    public function deleteImageDirectory(User $user): void
    {
        try {
            Storage::disk('public')->deleteDirectory("avatars/{$user->id}");
        } catch (\Throwable $e) {
            throw ProfileException::avatarDeleteFailed($user->id, $e);
        }
    }
}

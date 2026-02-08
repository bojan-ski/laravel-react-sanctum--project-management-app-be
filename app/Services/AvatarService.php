<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Exceptions\AvatarException;
use App\Models\Avatar;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AvatarService
{
    public const MIN_IMAGE_DIMENSION = 100;
    public const MAX_IMAGE_DIMENSION = 4096;
    private const MAX_PROCESSED_IMAGE_DIMENSION = 400;
    private const TARGET_FILE_SIZE = 500000;
    private const INITIAL_QUALITY = 85;
    private const MIN_QUALITY = 30;

    /**
     * Delete user avatar/image path
     */
    private function deleteAvatarPath(Avatar $avatar): void
    {
        try {
            DB::transaction(function () use ($avatar) {
                Storage::disk('public')->delete($avatar->avatar_path);

                $avatar->delete();
            });
        } catch (\Throwable $e) {
            throw AvatarException::avatarDeleteFailed($avatar->id, $e);
        }
    }

    /**
     * Process and store user avatar/image
     */
    private function processAndStoreUserAvatar(
        UploadedFile $file,
        string $directory,
        string $filename,
    ): string {
        try {
            // create path for avatar
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

            throw AvatarException::avatarProcessFailed($file->getClientOriginalName(), $e);
        }
    }

    /**
     * Process and store user avatar/image
     */
    private function processAndSaveUserAvatar(
        UploadedFile $file,
        int $userId
    ): void {
        try {
            $directory = "avatars/{$userId}";
            $filename = Str::uuid() . '.' . $file->extension();

            $path = $this->processAndStoreUserAvatar(
                $file,
                $directory,
                $filename
            );

            Avatar::create([
                'user_id' => $userId,
                'filename' => $filename,
                'avatar_path' => $path
            ]);
        } catch (\Throwable $e) {
            throw AvatarException::avatarProcessFailed($file->getClientOriginalName(), $e);
        }
    }

    /**
     * Upload user avatar
     */
    public function processUserAvatar(
        User $user,
        UploadedFile $avatar
    ): void {
        try {
            if ($user->avatar) {
                $this->deleteAvatarPath($user->avatar);
            }

            $this->processAndSaveUserAvatar($avatar, $user->id);
        } catch (\Throwable $e) {
            throw AvatarException::avatarUploadFailed($user->id, $e);
        }
    }

    /**
     * Delete user avatar/image directory
     */
    public function deleteAvatarDirectory(User $user): void
    {
        $avatar = $user->avatar;

        try {
            $this->deleteAvatarPath($avatar);

            Storage::disk('public')->deleteDirectory("avatars/{$user->id}");
        } catch (\Throwable $e) {
            throw AvatarException::avatarDeleteFailed($avatar->id, $e);
        }
    }
}

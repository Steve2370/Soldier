<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Factory;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class RemoveUserAvatar
{
    public function __construct(private Factory $files, private LoggerInterface $logger) {}

    public function remove(?string $path): void
    {
        // Only locally uploaded avatars, never arbitrary paths or remote URLs.
        if (! $path || ! preg_match('#^avatars/[a-zA-Z0-9_-]+\.(jpe?g|png|gif|webp)$#i', $path)
            || User::withTrashed()->where('avatar', $path)->exists()) {
            return;
        }

        try {
            if (! $this->files->disk('public')->delete($path)) {
                $this->logger->warning('Deleted account avatar cleanup failed');
            }
        } catch (Throwable $error) {
            // The account deletion is already committed; do not report a false 500.
            $this->logger->warning('Deleted account avatar cleanup failed', ['exception_type' => $error::class]);
        }
    }
}

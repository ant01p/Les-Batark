<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageHandler
{
    public function __construct(
        private readonly string $uploadsDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, string $subDir): string
    {
        $targetDir = $this->uploadsDirectory . '/' . $subDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename)->lower();
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($targetDir, $newFilename);

        return $newFilename;
    }

    public function remove(string $filename, string $subDir): void
    {
        $path = $this->uploadsDirectory . '/' . $subDir . '/' . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}

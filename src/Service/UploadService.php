<?php

namespace Backend2Plus\UploadBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UploadService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $publicUploadsFilesystem,
        private FilesystemOperator $privateUploadsFilesystem,
        private int $maxWidth = 800,
        private int $maxHeight = 600,
        private int $quality = 85,
    ) {}

    public function moveFile($uploadedFile, bool $filePathClean): array
    {
        $fileName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        
        // Proveravamo da li je fajl slika i da li je private upload
        $isImage = $this->isImageFile($uploadedFile);
        
        if ($isImage && $filePathClean) {
            // Resizeujemo sliku samo za private upload
            $resizedImagePath = $this->resizeImage($uploadedFile);
            $stream = fopen($resizedImagePath, 'r');
            $fileSize = filesize($resizedImagePath);
        } else {
            $stream = fopen($uploadedFile->getPathname(), 'r');
        }

        if ($filePathClean) {
            $this->privateUploadsFilesystem->writeStream($fileName, $stream);
        } else {
            $this->publicUploadsFilesystem->writeStream($fileName, $stream);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        
        // Brišemo privremeni fajl ako je kreiran
        if ($isImage && isset($resizedImagePath)) {
            unlink($resizedImagePath);
        }

        return [$fileName, $fileSize];
    }
    
    private function isImageFile($uploadedFile): bool
    {
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $uploadedFile->getMimeType();
        
        return in_array($mimeType, $allowedMimeTypes);
    }
    
    private function resizeImage($uploadedFile): string
    {
        $manager = new ImageManager(new Driver());
        
        // Učitavamo sliku
        $image = $manager->read($uploadedFile->getPathname());
        
        // Resizeujemo sliku - koristimo konfiguraciju
        $image->scaleDown(width: $this->maxWidth, height: $this->maxHeight);
        
        // Kreiramo privremeni fajl
        $tempPath = sys_get_temp_dir() . '/' . uniqid('resized_') . '.jpg';
        
        // Sačuvamo sa konfigurisanim kvalitetom da smanjimo veličinu
        $image->toJpeg($this->quality)->save($tempPath);
        
        return $tempPath;
    }

    public function createMediaObjectForEntities($result, $entityConnect, $method, $repository, $mediaObjectClass): JsonResponse
    {
        foreach ($result as $item) {
            if (isset($item['mediaObjectId'], $item['entityId'])) {
                $mediaObject = $this->entityManager->find($mediaObjectClass, $item['mediaObjectId']);
                $entityObject = $repository->find($item['entityId']);
                $entityConnectCreate = new $entityConnect();
                $entityConnectCreate->setMediaObject($mediaObject);
                $entityConnectCreate->$method($entityObject);
                $this->entityManager->persist($entityConnectCreate);
            }
        }

        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}

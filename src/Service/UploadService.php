<?php

namespace Backend2Plus\UploadBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $sourcePath = $uploadedFile->getPathname();
        $mimeType = $uploadedFile->getMimeType();
        
        // Kreiranje privremenog fajla
        $tempPath = sys_get_temp_dir() . '/' . uniqid('resized_') . '.jpg';
        
        // Učitavanje originalne slike
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image format');
        }
        
        if (!$sourceImage) {
            throw new \Exception('Failed to load image');
        }
        
        // Dohvatanje dimenzija originalne slike
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        // Izračunavanje novih dimenzija
        $ratio = min($this->maxWidth / $originalWidth, $this->maxHeight / $originalHeight);
        
        if ($ratio >= 1) {
            // Slika je manja od maksimuma, ne treba resize
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        } else {
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
        }
        
        // Kreiranje nove slike sa novim dimenzijama
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Omogućavanje transparencije za PNG
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        // Resizeovanje slike
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Sačuvavanje resizeovane slike
        imagejpeg($newImage, $tempPath, $this->quality);
        
        // Oslobađanje memorije
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
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

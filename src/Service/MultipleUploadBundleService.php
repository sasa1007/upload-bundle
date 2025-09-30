<?php

namespace Backend2Plus\UploadBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Backward compatibility wrapper for UploadService
 * This class provides the same interface as the old MultipleUploadBundleService
 */
class MultipleUploadBundleService
{
    private UploadService $uploadService;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $publicUploadsFilesystem,
        private FilesystemOperator $privateUploadsFilesystem,
    ) {
        $this->uploadService = new UploadService(
            $entityManager,
            $publicUploadsFilesystem,
            $privateUploadsFilesystem
        );
    }

    public function moveFile($uploadedFile, bool $filePathClean): array
    {
        return $this->uploadService->moveFile($uploadedFile, $filePathClean);
    }

    public function createMediaObjectForEntities($result, $entityConnect, $method, $repository): JsonResponse
    {
        // Use the MediaObject entity class directly
        $mediaObjectClass = \App\Bundles\MediaObject\Entity\MediaObject::class;
        return $this->uploadService->createMediaObjectForEntities(
            $result, 
            $entityConnect, 
            $method, 
            $repository, 
            $mediaObjectClass
        );
    }
}

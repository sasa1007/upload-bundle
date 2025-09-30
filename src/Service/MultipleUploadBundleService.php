<?php

namespace Backend2Plus\UploadBundle\Service;

use App\Bundles\MediaCenter\Repository\MediaObjectRepository;
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
    private MediaObjectRepository $mediaObjectRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $publicUploadsFilesystem,
        private FilesystemOperator $privateUploadsFilesystem,
        MediaObjectRepository $mediaObjectRepository,
    ) {
        $this->mediaObjectRepository = $mediaObjectRepository;
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
        // Use the MediaObject entity class from the repository
        $mediaObjectClass = $this->mediaObjectRepository->getClassName();
        return $this->uploadService->createMediaObjectForEntities(
            $result, 
            $entityConnect, 
            $method, 
            $repository, 
            $mediaObjectClass
        );
    }
}

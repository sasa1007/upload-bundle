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
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $publicUploadsFilesystem,
        private FilesystemOperator $privateUploadsFilesystem,
        private MediaObjectRepository $mediaObjectRepository,
    ) {}

    public function moveFile($uploadedFile, bool $filePathClean): array
    {
        $fileName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $stream = fopen($uploadedFile->getPathname(), 'r');

        if ($filePathClean) {
            $this->privateUploadsFilesystem->writeStream($fileName, $stream);
        } else {
            $this->publicUploadsFilesystem->writeStream($fileName, $stream);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return [$fileName, $fileSize];
    }

    public function createMediaObjectForEntities($result, $entityConnect, $method, $repository): JsonResponse
    {
        foreach ($result as $item) {
            if (isset($item['mediaObjectId'], $item['entityId'])) {
                $mediaObject = $this->mediaObjectRepository->find($item['mediaObjectId']);
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

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

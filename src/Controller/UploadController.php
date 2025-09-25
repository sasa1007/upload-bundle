<?php

namespace Backend2Plus\UploadBundle\Controller;

use Backend2Plus\UploadBundle\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UploadController
{
    public function __construct()
    {
        // Constructor needed for Symfony service container
    }

    #[Route('/bundle/upload/images', methods: ['POST'])]
    public function uploadImages(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UploadService $uploadService,
    ): JsonResponse {
        $files = $request->files->get('files');
        $singleFile = $request->files->get('file');
        $mediaObjectId = $request->request->get('mediaObjectId');

        if ($files && is_array($files)) {
            $uploadedFiles = $this->handleMultipleFiles($files, $entityManager, $serializer, $uploadService, $request);
            return new JsonResponse(['files' => $uploadedFiles]);
        } elseif ($singleFile) {
            $uploadedFiles = $this->handleSingleFile($singleFile, $mediaObjectId, $entityManager, $serializer, $uploadService, $request);
            return new JsonResponse(['files' => $uploadedFiles]);
        }

        throw new BadRequestHttpException('No files provided');
    }

    private function handleMultipleFiles(array $files, EntityManagerInterface $entityManager, SerializerInterface $serializer, UploadService $uploadService, Request $request): array
    {
        $uploadedFiles = [];
        $mediaObjects = [];
        
        // Uzimamo filePathClean iz request-a - ako nije prosleđen, podrazumeva se false
        $filePathCleanParam = $request->request->get('filePathClean', 'false');
        $filePathClean = filter_var($filePathCleanParam, FILTER_VALIDATE_BOOLEAN);
        
        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            [$fileName, $fileSize] = $uploadService->moveFile($file, $filePathClean);
            
            // Koristimo MediaObject entitet iz glavnog projekta
            $mediaObject = new \App\Bundles\MediaObject\Entity\MediaObject();
            $mediaObject->setFilePath($fileName);
            $mediaObject->setFileSize($fileSize);
            
            $entityManager->persist($mediaObject);
            $mediaObjects[] = $mediaObject;
        }
        
        // Flush jednom na kraju za sve fajlove
        $entityManager->flush();
        
        // Sada kada su svi MediaObject-i sačuvani, možemo da ih serializujemo
        foreach ($mediaObjects as $mediaObject) {
            $uploadedFiles[] = $serializer->normalize($mediaObject, 'jsonld', ['groups' => ['mediaObject:read']]);
        }

        return $uploadedFiles;
    }

    private function handleSingleFile($singleFile, $mediaObjectId, EntityManagerInterface $entityManager, SerializerInterface $serializer, UploadService $uploadService, Request $request): array
    {
        // Uzimamo filePathClean iz request-a - ako nije prosleđen, podrazumeva se false
        $filePathCleanParam = $request->request->get('filePathClean', 'false');
        $filePathClean = filter_var($filePathCleanParam, FILTER_VALIDATE_BOOLEAN);
        
        if ($mediaObjectId) {
            [$fileName, $fileSize] = $uploadService->moveFile($singleFile, $filePathClean);
            
            $mediaObject = $entityManager->getRepository(\App\Bundles\MediaObject\Entity\MediaObject::class)->find($mediaObjectId);
            if (!$mediaObject) {
                throw new BadRequestHttpException('MediaObject with provided ID not found');
            }

            $mediaObject->setFilePath($fileName);
            $mediaObject->setFileSize($fileSize);
            
            $entityManager->persist($mediaObject);
            $entityManager->flush();

            return [$serializer->normalize($mediaObject, 'jsonld', ['groups' => ['mediaObject:read']])];
        }

        // Samo uploadujemo fajl, ne kreiramo MediaObject
        [$fileName, $fileSize] = $uploadService->moveFile($singleFile, $filePathClean);
        
        return [[
            'filePath' => $fileName,
            'fileSize' => $fileSize
        ]];
    }

    #[Route('/bundle/delete/aws/image/{filePath}', methods: ['GET'])]
    public function postRemove(
        $filePath,
        FilesystemOperator $privateUploadsFilesystem,
        FilesystemOperator $publicUploadsFilesystem,
    ): JsonResponse {
        if ($privateUploadsFilesystem->fileExists($filePath)) {
            $privateUploadsFilesystem->delete($filePath);
        } elseif ($publicUploadsFilesystem->fileExists($filePath)) {
            $publicUploadsFilesystem->delete($filePath);
        }

        return new JsonResponse(['success' => true]);
    }
}

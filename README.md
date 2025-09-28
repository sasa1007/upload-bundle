# Upload Bundle

[![GitHub release](https://img.shields.io/github/release/sasa1007/upload-bundle.svg)](https://github.com/sasa1007/upload-bundle/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Symfony bundle for upload functionality with Flysystem support. Bundle uses your existing MediaObject entity from your project.

## Installation

### 1. Add to composer.json

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/sasa1007/upload-bundle.git"
        }
    ],
    "require": {
        "backend2-plus/upload-bundle": "^1.0"
    }
}

### 2. Install bundle

```bash
composer require backend2-plus/upload-bundle:^1.0
```

### 3. Register bundle in config/bundles.php

```php
<?php

return [
    // ... other bundles
    Backend2Plus\UploadBundle\UploadBundle::class => ['all' => true],
];
```

### 4. Configure Flysystem

Bundle uses OneupFlysystemBundle. Add to config/packages/oneup_flysystem.yaml:

```yaml
oneup_flysystem:
    adapters:
        public_uploads:
            local:
                directory: '%kernel.project_dir%/public/upload'
        private_uploads:
            local:
                directory: '%kernel.project_dir%/privatePictures'
    filesystems:
        public_uploads:
            adapter: public_uploads
        private_uploads:
            adapter: private_uploads
```

### 5. Include routing

Add to config/routes.yaml:

```yaml
upload_bundle:
    resource: '@UploadBundle/config/routing.yaml'
```

### 6. Configure MediaObject

Bundle expects you to have MediaObject entity in your project. Controller receives MediaObject class and repository as parameters:

```yaml
# config/services.yaml
Backend2Plus\UploadBundle\Controller\UploadController:
    arguments:
        $mediaObjectRepository: '@App\Bundles\MediaCenter\Repository\MediaObjectRepository'
        $mediaObjectClass: 'App\Bundles\MediaObject\Entity\MediaObject'
```

## Usage

### Upload images

**POST** `/bundle/upload/images`

Parameters:
- `files` (array): Multiple files
- `file` (UploadedFile): Single file
- `mediaObjectId` (int, optional): ID of existing MediaObject
- `filePathClean` (bool, optional): Whether to save in private directory (default: false)

### Delete images

**GET** `/bundle/delete/aws/image/{filePath}`

## API

Bundle uses your existing MediaObject entity with ApiPlatform support.

### MediaObject

Bundle expects your MediaObject entity to have the following methods:
- `setFilePath($filePath)`: Set file path
- `setFileSize($fileSize)`: Set file size
- `getFilePath()`: Get file path
- `getFileSize()`: Get file size

## Services

### UploadService

```php
use Backend2Plus\UploadBundle\Service\UploadService;

// Upload file
[$fileName, $fileSize] = $uploadService->moveFile($uploadedFile, $filePathClean);

// Create connections between MediaObject and other entities
$uploadService->createMediaObjectForEntities($result, $entityConnect, $method, $repository, $mediaObjectRepository);
```

## Dependencies

- Symfony 6.0+
- Doctrine ORM 2.15+
- League Flysystem 3.0+
- OneupFlysystemBundle 5.0+
- ApiPlatform 3.0+

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you have any questions or need help, please open an issue on [GitHub](https://github.com/sasa1007/upload-bundle/issues).

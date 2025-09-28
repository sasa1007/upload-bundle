# Upload Bundle Integration Instructions

## Step 1: Add bundle to composer.json

In your main project, add to `composer.json`:

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
```

Then run:
```bash
composer require backend2-plus/upload-bundle:^1.0
```

## Step 2: Register bundle

In `config/bundles.php` add:
```php
<?php

return [
    // ... existing bundles
    Backend2Plus\UploadBundle\UploadBundle::class => ['all' => true],
];
```

## Step 3: Configure Flysystem

In `config/packages/oneup_flysystem.yaml` add:
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

## Step 4: Include routing

In `config/routes.yaml` add:
```yaml
upload_bundle:
    resource: '@UploadBundle/config/routing.yaml'
```

## Step 5: Configure MediaObject

Bundle uses your existing MediaObject entity. Add configuration in `config/services.yaml`:

```yaml
Backend2Plus\UploadBundle\Controller\UploadController:
    arguments:
        $mediaObjectRepository: '@App\Bundles\MediaCenter\Repository\MediaObjectRepository'
        $mediaObjectClass: 'App\Bundles\MediaObject\Entity\MediaObject'
```

## Step 6: Replace existing files

Now you can delete existing files:
- `src/Bundles/Upload/MultipleUploadBundleService.php`
- `src/Bundles/Upload/MultipleUploadController.php`

And replace them with bundle services:
- `Backend2Plus\UploadBundle\Service\UploadService`
- `Backend2Plus\UploadBundle\Controller\UploadController`

## Step 7: Update existing controllers

In existing controllers where `MultipleUploadBundleService` is used, replace with:

```php
// Old code
use App\Bundles\Upload\MultipleUploadBundleService;

// New code
use Backend2Plus\UploadBundle\Service\UploadService;
```

## Step 8: Testing

Test if everything works:
1. Upload images via `/bundle/upload/images`
2. Delete images via `/bundle/delete/aws/image/{filePath}`
3. API Platform endpoint for MediaObject

## Notes

- Bundle uses the same routes as the original code
- All existing calls will work without changes
- MediaObject entity is fully compatible with existing code
- Bundle supports both public and private upload directories

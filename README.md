# UploadBundle

Symfony bundle for upload functionality with Flysystem support.

## Installation

### 1. Install bundle via Composer

```bash
composer require backend2-plus/upload-bundle
```

### 2. Migrate existing code

If you have existing code that uses `App\Bundles\Upload\MultipleUploadBundleService`, you need to change the import in controllers:

**Old:**
```php
use App\Bundles\Upload\MultipleUploadBundleService;
```

**New:**
```php
use Backend2Plus\UploadBundle\Service\App\Bundles\Upload\MultipleUploadBundleService;
```

### 3. Remove old files

After migration, you can delete old files:
- `src/Controller/UploadController.php`
- `src/Bundles/Upload/` directory
- `config/packages/upload.yaml` (old)

## Usage

### Upload Controller

Bundle provides API endpoint for upload:

```
POST /api/upload
```

### Services

- `Backend2Plus\UploadBundle\Service\UploadService` - main upload service
- `Backend2Plus\UploadBundle\Service\App\Bundles\Upload\MultipleUploadBundleService` - service for multiple upload

### Flysystem Configuration

Bundle uses OneupFlysystemBundle for filesystem configuration. You need to configure:

```yaml
# config/packages/oneup_flysystem.yaml
oneup_flysystem:
    adapters:
        public_uploads:
            local:
                directory: '%kernel.project_dir%/public/upload'
        private_uploads:
            local:
                directory: '%kernel.project_dir%/privatePictures'
    filesystems:
        public_uploads_filesystem:
            adapter: public_uploads
        private_uploads_filesystem:
            adapter: private_uploads
```

For support contact: sasam09@gmail.com
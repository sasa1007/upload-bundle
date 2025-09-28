<?php

namespace Backend2Plus\UploadBundle;

use Backend2Plus\UploadBundle\DependencyInjection\UploadBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UploadBundle extends Bundle
{
    public function getContainerExtension(): ?UploadBundleExtension
    {
        return new UploadBundleExtension();
    }
}

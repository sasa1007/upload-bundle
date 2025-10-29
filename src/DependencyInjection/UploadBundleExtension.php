<?php

namespace Backend2Plus\UploadBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Backend2Plus\UploadBundle\DependencyInjection\Configuration;

class UploadBundleExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('upload_bundle.image_resize.private.max_width', $config['image_resize']['private']['max_width']);
        $container->setParameter('upload_bundle.image_resize.private.max_height', $config['image_resize']['private']['max_height']);
        $container->setParameter('upload_bundle.image_resize.public.max_width', $config['image_resize']['public']['max_width']);
        $container->setParameter('upload_bundle.image_resize.public.max_height', $config['image_resize']['public']['max_height']);
        $container->setParameter('upload_bundle.image_resize.quality', $config['image_resize']['quality']);
        
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }
    
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('upload_bundle', [
            'image_resize' => [
                'private' => [
                    'max_width' => 1200,
                    'max_height' => 1200
                ],
                'public' => [
                    'max_width' => 2560,
                    'max_height' => 1440
                ],
                'quality' => 85
            ]
        ]);
    }
}

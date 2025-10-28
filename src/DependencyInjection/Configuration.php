<?php

namespace Backend2Plus\UploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('upload_bundle');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('image_resize')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_width')
                            ->defaultValue(1200)
                            ->info('Maximum width for resized images')
                        ->end()
                        ->integerNode('max_height')
                            ->defaultValue(1200)
                            ->info('Maximum height for resized images')
                        ->end()
                        ->integerNode('quality')
                            ->defaultValue(85)
                            ->min(1)
                            ->max(100)
                            ->info('JPEG quality (1-100)')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Finder\Finder;

return static function (ContainerConfigurator $container): void {
    $srcDir = \dirname(__DIR__, 2) . '/src';
    $types = [];
    $mappings = [];

    if (is_dir($srcDir)) {
        $typeFinder = new Finder();
        $typeFinder->files()->in($srcDir)->path('Infrastructure/Persistence/Type')->name('*Type.php');

        foreach ($typeFinder as $file) {
            $relativePath = $file->getRelativePathname();
            $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (class_exists($class) && defined("$class::NAME")) {
                $types[$class::NAME] = $class;
            }
        }

        $mappingFinder = new Finder();
        $mappingFinder->directories()->in($srcDir)->path('Infrastructure/Persistence')->name('Mapping');

        foreach ($mappingFinder as $dir) {
            $relativePath = $dir->getRelativePathname();
            $module = explode('/', $relativePath)[0];

            $mappings[$module] = [
                'type' => 'xml',
                'is_bundle' => false,
                'dir' => '%kernel.project_dir%/src/' . $relativePath,
                'prefix' => 'App\\' . $module . '\Domain\Entity',
                'alias' => $module,
            ];
        }
    }

    // Attribute-mapped entities (e.g. gesdinet RefreshToken in Infrastructure/Security)
    $mappings['AuthSecurity'] = [
        'type' => 'attribute',
        'is_bundle' => false,
        'dir' => '%kernel.project_dir%/src/Auth/Infrastructure/Security',
        'prefix' => 'App\Auth\Infrastructure\Security',
        'alias' => 'AuthSecurity',
    ];

    $container->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
            'profiling_collect_backtrace' => '%kernel.debug%',
            'types' => $types,
            'options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
            ],
        ],
        'orm' => [
            'validate_xml_mapping' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
            'auto_mapping' => false,
            'mappings' => $mappings,
        ],
    ]);

    //  TEST CONFIG (when@test)
    if ($container->env() === 'test') {
        $container->extension('doctrine', [
            'dbal' => [
                'dbname_suffix' => '_test%env(default::TEST_TOKEN)%',
            ],
        ]);
    }

    //  PROD CONFIG (when@prod)
    if ($container->env() === 'prod') {
        $container->extension('doctrine', [
            'orm' => [
                'query_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.system_cache_pool',
                ],
                'result_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.result_cache_pool',
                ],
            ],
        ]);

        $container->extension('framework', [
            'cache' => [
                'pools' => [
                    'doctrine.result_cache_pool' => [
                        'adapter' => 'cache.app',
                    ],
                    'doctrine.system_cache_pool' => [
                        'adapter' => 'cache.system',
                    ],
                ],
            ],
        ]);
    }
};

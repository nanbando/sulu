<?php

namespace Nanbando\Plugin\Sulu\DependencyInjection;

use Nanbando\Plugin\Sulu\Composer\SuluDetector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Integrates sulu presets into nanbando.
 */
class NanbandoSuluExtension extends Extension implements PrependExtensionInterface
{
    private static $mandatoryParameters = [
        'database_user',
        'database_password',
        'database_name',
    ];

    private static $optionalParameters = [
        'jackrabbit_uri' => 'http://localhost:8080/server/',
        'phpcr_workspace' => 'default',
    ];

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        foreach (self::$optionalParameters as $parameter => $value) {
            if (!$container->hasParameter($parameter)) {
                $container->setParameter($parameter, $value);
            }
        }

        $missingParameter = array_diff(self::$mandatoryParameters, array_keys($container->getParameterBag()->all()));
        if (0 < count($missingParameter)) {
            throw new \Exception(
                sprintf('Following Parameter are not available: ["%s"]', implode('", "', $missingParameter))
            );
        }

        $detector = SuluDetector::create($container);
        if ($application = $detector->detect()) {
            $container->prependExtensionConfig('nanbando', ['application' => $application]);

            if ('jackrabbit' == $application['options']['phpcr_backend']
                && $container->hasParameter('phpcr_backend')
                && array_key_exists('url', $phpcrBackend = $container->getParameter('phpcr_backend'))
            ) {
                $container->setParameter(
                    'jackrabbit_uri',
                    $phpcrBackend['url']
                );
            }
        }

        $container->prependExtensionConfig(
            'nanbando',
            [
                'presets' => [
                    [
                        'application' => 'sulu',
                        'version' => '*',
                        'backup' => [
                            'database' => [
                                'plugin' => 'mysql',
                                'process' => ['database'],
                                'parameter' => [
                                    'username' => '%database_user%',
                                    'password' => '%database_password%',
                                    'database' => '%database_name%',
                                ],
                            ],
                        ],
                    ],
                    [
                        'application' => 'sulu',
                        'version' => '*',
                        'options' => [
                            'edition' => 'minimal',
                        ],
                        'backup' => [
                            'uploads' => [
                                'plugin' => 'directory',
                                'process' => ['files'],
                                'parameter' => [
                                    'directory' => 'var/uploads',
                                ],
                            ],
                        ],
                    ],
                    [
                        'application' => 'sulu',
                        'version' => '*',
                        'options' => [
                            'edition' => 'standard',
                        ],
                        'backup' => [
                            'uploads' => [
                                'plugin' => 'directory',
                                'process' => ['files'],
                                'parameter' => [
                                    'directory' => 'uploads',
                                ],
                            ],
                        ],
                    ],
                    [
                        'application' => 'sulu',
                        'version' => '*',
                        'options' => [
                            'phpcr_backend' => 'jackrabbit',
                        ],
                        'backup' => [
                            'cmf' => [
                                'plugin' => 'jackrabbit',
                                'process' => ['database'],
                                'parameter' => [
                                    'jackrabbit_uri' => '%jackrabbit_uri%',
                                    'workspace' => '%phpcr_workspace%',
                                    'path' => '/cmf',
                                ],
                            ],
                            'versions' => [
                                'plugin' => 'jackrabbit',
                                'parameter' => [
                                    'jackrabbit_uri' => '%jackrabbit_uri%',
                                    'workspace' => '%phpcr_workspace%',
                                    'path' => '/jcr:versions',
                                ],
                            ],
                        ],
                    ],
                    [
                        'application' => 'sulu',
                        'version' => '^1.3',
                        'options' => [
                            'phpcr_backend' => 'jackrabbit',
                        ],
                        'backup' => [
                            'cmf_live' => [
                                'plugin' => 'jackrabbit',
                                'process' => ['database'],
                                'parameter' => [
                                    'jackrabbit_uri' => '%jackrabbit_uri%',
                                    'workspace' => '%phpcr_workspace%_live',
                                    'path' => '/cmf',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}

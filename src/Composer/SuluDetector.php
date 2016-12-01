<?php

namespace Nanbando\Plugin\Sulu\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Locker;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Detects version and options for sulu applications.
 */
class SuluDetector
{
    public static function create(ContainerBuilder $container)
    {
        return new self($container);
    }

    const SULU_PACKAGE_NAME = 'sulu/sulu';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var Locker
     */
    protected $locker;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @param ContainerBuilder $container
     */
    private function __construct(ContainerBuilder $container)
    {
        $this->container = $container;

        $this->composer = Factory::create(new NullIO(), getcwd() . DIRECTORY_SEPARATOR . 'composer.json');
        $this->locker = $this->composer->getLocker();
    }

    /**
     * Detect sulu, version and options.
     *
     * @return array
     */
    public function detect()
    {
        if (!($package = $this->getPackage())) {
            return;
        }

        return [
            'name' => 'sulu',
            'version' => $package['version'],
            'options' => array_filter(
                [
                    'edition' => $this->detectEdition(),
                    'phpcr_backend' => $this->detectPhpcrBackend(),
                ]
            ),
        ];
    }

    /**
     * Returns composer package definition (if exists).
     *
     * @return array
     */
    private function getPackage()
    {
        try {
            $lockData = $this->locker->getLockData();
        } catch (\LogicException $exception) {
            return;
        }

        foreach ($lockData['packages'] as $package) {
            if ($package['name'] === self::SULU_PACKAGE_NAME) {
                return $package;
            }
        }
    }

    /**
     * Detect edition for actual sulu-application.
     *
     * @return string
     */
    private function detectEdition()
    {
        $extra = $this->composer->getPackage()->getExtra();
        if ($extra && array_key_exists('symfony-var-dir', $extra) && $extra['symfony-var-dir'] === 'var') {
            return 'minimal';
        }

        return 'standard';
    }

    /**
     * Detect phpcr-backend for actual sulu-application.
     *
     * @return string
     */
    private function detectPhpcrBackend()
    {
        if (!$this->container->hasParameter('phpcr_backend')) {
            return;
        }

        $parameter = $this->container->getParameter('phpcr_backend');
        if (!array_key_exists('type', $parameter)) {
            return;
        }

        return $parameter['type'];
    }
}

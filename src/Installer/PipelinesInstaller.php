<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\TestingSuite\Composer\Installer;

use Composer\IO\IOInterface;
use Mediact\Composer\FileInstaller;
use Mediact\FileMapping\UnixFileMapping;
use Mediact\TestingSuite\Composer\Factory\ProcessFactoryInterface;

class PipelinesInstaller implements InstallerInterface
{
    /** @var FileInstaller */
    private $fileInstaller;

    /** @var IOInterface */
    private $io;

    /** @var ProcessFactoryInterface */
    private $processFactory;

    /** @var string */
    private $destination;

    /** @var string */
    private $pattern = 'bitbucket.org';

    /** @var string */
    private $filename = 'bitbucket-pipelines.yml';

    /**
     * Constructor.
     *
     * @param FileInstaller           $fileInstaller
     * @param IOInterface             $io
     * @param ProcessFactoryInterface $processFactory
     * @param string|null             $destination
     * @param string|null             $pattern
     * @param string|null             $filename
     */
    public function __construct(
        FileInstaller $fileInstaller,
        IOInterface $io,
        ProcessFactoryInterface $processFactory,
        string $destination = null,
        string $pattern = null,
        string $filename = null
    ) {
        $this->fileInstaller  = $fileInstaller;
        $this->io             = $io;
        $this->processFactory = $processFactory;
        $this->destination    = $destination ?? getcwd();
        $this->pattern        = $pattern ?? $this->pattern;
        $this->filename       = $filename ?? $this->filename;
    }

    /**
     * Install.
     *
     * @return void
     */
    public function install()
    {
        if (file_exists($this->destination . '/' . $this->filename)
            || !$this->isBitbucket()
        ) {
            return;
        }

        $mapping = new UnixFileMapping(
            __DIR__ . '/../../templates/files/pipelines',
            $this->destination,
            $this->filename
        );

        $this->fileInstaller->installFile($mapping);
        $this->io->write(
            sprintf(
                '<info>Installed:</info> %s',
                $mapping->getRelativeDestination()
            )
        );
    }

    /**
     * Check whether the project is on Bitbucket.
     *
     * @return bool
     */
    private function isBitbucket(): bool
    {
        $process = $this->processFactory->create('git remote -v');
        $process->run();

        return strpos($process->getOutput(), $this->pattern) !== false;
    }
}

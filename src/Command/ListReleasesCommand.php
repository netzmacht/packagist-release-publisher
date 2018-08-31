<?php

/**
 * Packagist release publisher.
 *
 * @package    packagist-release-publisher
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus
 * @license    LGPL-3.0-or-later https://github.com/netzmacht/tapatalk-client-api/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace App\Command;

use App\Packagist\PackageReleases;
use App\Packagist\Release;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PublishReleaseNoteCommand
 */
final class ListReleasesCommand extends Command
{
    /**
     * Package releases.
     *
     * @var PackageReleases
     */
    private $packageReleases;

    /**
     * File system.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * File storing since date.
     *
     * @var string
     */
    private $lastRunFile;


    /**
     * PublishReleaseNoteCommand constructor.
     *
     * @param PackageReleases $packageReleases Package releases.
     * @param Filesystem      $filesystem      The file system.
     * @param string          $lastRunFile     Last run file.
     */
    public function __construct(PackageReleases $packageReleases, Filesystem $filesystem, string $lastRunFile)
    {
        parent::__construct('list-releases');

        $this->packageReleases = $packageReleases;
        $this->filesystem      = $filesystem;
        $this->lastRunFile     = $lastRunFile;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            'since',
            's',
            InputOption::VALUE_REQUIRED,
            'Optional time reference, parseable by \DateTimeImmutable'
        );

        $this->addOption(
            'ignore-last-run',
            'i',
            InputOption::VALUE_NONE,
            'If true the last run is ignored.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $since = $this->getSince($input);
        $count = 0;

        $output->writeln(sprintf('Check packages released since %s:', $since->format($since::ATOM)));

        foreach ($this->packageReleases->since($since) as $release) {
            $output->writeln(sprintf(' - "%s" released', $release), OutputInterface::VERBOSITY_VERBOSE);
        }

        $output->writeln(sprintf('%s releases found.', $count), OutputInterface::VERBOSITY_NORMAL);
    }

    /**
     * Get the sinc date.
     *
     * @param InputInterface $input The console input.
     *
     * @return \DateTimeInterface
     */
    private function getSince(InputInterface $input): \DateTimeInterface
    {
        $lastRun = null;

        if (!$input->getOption('ignore-last-run') && $this->filesystem->exists($this->lastRunFile)) {
            $content = file_get_contents($this->lastRunFile);
            $lastRun = new \DateTimeImmutable($content);
        }

        if ($input->getOption('since')) {
            $since = new \DateTimeImmutable($input->getOption('since'));

            if ($since > $lastRun) {
                return $since;
            }

            return $lastRun;
        }

        if ($lastRun) {
            return $lastRun;
        }

        $dateTime = new \DateTimeImmutable();
        $dateTime = $dateTime->setTime(0, 0);

        return $dateTime;
    }
}

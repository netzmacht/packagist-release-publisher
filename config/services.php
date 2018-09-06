<?php

/**
 * Release notifier.
 *
 * @package    release-notifier
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus
 * @license    LGPL-3.0-or-later https://github.com/netzmacht/release-notifier/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

use App\Console\Command\CheckCommand;
use App\Console\Command\CheckPackageCommand;
use App\Console\Command\CreateConfigCommand;
use App\Console\Command\PublishCommand;
use App\History\History;
use App\Package\PackagistReleases;
use App\Package\Releases;
use App\Publisher\DelegatingPublisherFactory;
use App\Publisher\PublisherFactory;
use App\Publisher\Tapatalk\TapatalkPublisherFactory;
use App\Rss\Http\GuzzleClientAdapter;
use GuzzleHttp\Client as HttpClient;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Feed\Reader\Http\ClientInterface as FeedReaderHttpClient;

return [
    'invokables' => [
        Filesystem::class               => Filesystem::class,
        TapatalkPublisherFactory::class => TapatalkPublisherFactory::class,
    ],
    'factories'  => [
        /* Application config */
        'config'                   => function () {
            return new ArrayObject(require __DIR__ . '/config.php');
        },

        /* Console application */
        Application::class         => function (ContainerInterface $container): Application {
            $config      = $container->get('config');
            $application = new Application($config['application']['name'], $config['application']['version']);

            foreach ($config['application']['commands'] as $command) {
                $application->add($container->get($command));
            }

            return $application;
        },

        /* Console commands */
        CreateConfigCommand::class => function (ContainerInterface $container): CreateConfigCommand {
            return new CreateConfigCommand(
                $container->get(Filesystem::class),
                $container->get('config')['paths']['boilerplate']
            );
        },

        CheckCommand::class => function (ContainerInterface $container): CheckCommand {
            return new CheckCommand(
                $container->get(Releases::class),
                $container->get(History::class)
            );
        },

        CheckPackageCommand::class => function (ContainerInterface $container): CheckPackageCommand {
            return new CheckPackageCommand(
                $container->get(Releases::class)
            );
        },

        PublishCommand::class       => function (ContainerInterface $container): PublishCommand {
            return new PublishCommand(
                $container->get(PublisherFactory::class),
                $container->get(Releases::class),
                $container->get(History::class)
            );
        },

        /* Publisher factory */
        PublisherFactory::class     => function (ContainerInterface $container): PublisherFactory {
            $factories = [];
            $config    = $container->get('config');

            foreach ($config['publishers']['factories'] as $serviceName) {
                $factories[] = $container->get($serviceName);
            }

            return new DelegatingPublisherFactory($factories);
        },

        /* Rss feed http client */
        FeedReaderHttpClient::class => function (ContainerInterface $container): FeedReaderHttpClient {
            $config = $container->get('config');

            return new GuzzleClientAdapter(
                new HttpClient(['base_uri' => $config['packagist']['url']])
            );
        },

        /* Package releases */
        Releases::class             => function (ContainerInterface $container): Releases {
            return new PackagistReleases($container->get(FeedReaderHttpClient::class));
        },

        /* Last run information */
        History::class              => function (ContainerInterface $container): History {
            return new History(
                $container->get(Filesystem::class),
                $container->get('config')['paths']['last_run']
            );
        }
    ],
];

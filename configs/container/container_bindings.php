<?php

declare(strict_types = 1);

use App\Auth;
use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Csrf;
use App\DataObjects\SessionConfig;
use App\Enum\AppEnvironment;
use App\Enum\SameSite;
use App\RequestValidators\RequestValidatorFactory;
use App\Services\UserProviderService;
use App\Session;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use DoctrineExtensions\Query\Mysql\Cast;
use DoctrineExtensions\Query\Mysql\Date;
use DoctrineExtensions\Query\Mysql\DateFormat;
use DoctrineExtensions\Query\Mysql\Month;
use DoctrineExtensions\Query\Mysql\MonthName;
use DoctrineExtensions\Query\Mysql\Year;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;

use function DI\create;

return [
    App::class                                      => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router         = require CONFIG_PATH . '/routes/web.php';

        $app = AppFactory::create();

        $router($app);

        $addMiddlewares($app);

        return $app;
    },
    Config::class                                   => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),

    EntityManager::class                    => function (Config $config) {

        $configuration = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode'),
        );
        $configuration->addCustomDatetimeFunction('DATE', Date::class);
        $configuration->addCustomDatetimeFunction('YEAR', Year::class);
        $configuration->addCustomDatetimeFunction('CAST', Cast::class);
        $configuration->addCustomDatetimeFunction('MONTH', Month::class);
        $configuration->addCustomDatetimeFunction('MONTHNAME', MonthName::class);
        $configuration->addCustomDatetimeFunction('DATE_FORMAT', DateFormat::class);

        return new EntityManager(new Connection($config->get('doctrine.connection'), new Driver()),
        $configuration
    );},
    
    Twig::class                                     => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'         => STORAGE_PATH . '/cache/templates',
            'auto_reload'   => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));
        $twig->addExtension(new DebugExtension());

        return $twig;
    },
    /**
     * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
     */
    'webpack_encore.packages'                       => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    ),
    'webpack_encore.tag_renderer'                   => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
        $container->get('webpack_encore.packages')
    ),
    ResponseFactoryInterface::class                          => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class                    => fn (ContainerInterface $container) => $container->get(
        Auth::class
    ),
    UserProviderServiceInterface::class     => fn (ContainerInterface $container) => $container->get(
        UserProviderService::class
    ),
    SessionInterface::class                 => fn (Config $config) => new Session(
        new SessionConfig(
            $config->get('session.name', ''),
            $config->get('session.flash_name', 'flash'),
            $config->get('session.secure', true),
            $config->get('session.httponly', true),
            SameSite::from($config->get('session.samesite', 'lax'))
        )
    ),
    RequestValidatorFactoryInterface::class => fn (ContainerInterface $container) => $container->get(
        RequestValidatorFactory::class
    ),
    'csrf'                                  => fn (ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard(
        $responseFactory,
        failureHandler: $csrf->failureHandler(),
        persistentTokenMode: true
    ),
    Clockwork::class => function (EntityManager $entityManager) {
        $clockwork = new Clockwork();

        $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($entityManager));

        return $clockwork;
    }
];
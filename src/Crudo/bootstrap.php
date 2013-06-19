<?php

define("ROOT_PATH", realpath(__DIR__ . "/../../"));

require_once __DIR__ . "/../../vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use FranMoreno\Silex\Provider\PagerfantaServiceProvider;
use Crudo\Config;

$app = new Silex\Application();

$app['debug'] = true;
$app['baseUrl'] = Config\Config::getInstance()->getBaseUrl();

$app['entityManager'] = $app->share(function() {
            return EntityManager::create(Config\Config::getInstance()->getDbParams(), Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/Entity'), true, null, null, false));
        });

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT_PATH . '/views',
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'foo' => array('pattern' => '^/foo'),
        'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true,
            'form' => array(
                'login_path' => '/',
                "default_target_path" => '/dashboard',
                'check_path' => '/logincheck',
                'username_parameter' => 'login[_username]',
                'password_parameter' => 'login[_password]',
            ),
            'logout' => array('logout_path' => '/logout'),
            'users' => $app->share(function() use ($app) {
                        return new Crudo\Security\UserProvider($app['entityManager']);
                    }),
        ),
    ),
    'security.access_rules' => array(
        array('^/dashboard', 'ROLE_USER'),
        array('^/user', 'ROLE_ADMIN'),
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER')
    ),
));

$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'en')
);

$app->register(new Silex\Provider\ValidatorServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new PagerfantaServiceProvider());

$app->boot();
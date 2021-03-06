<?php

namespace Crudo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class Dashboard implements ControllerProviderInterface {

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app) {
                    return $app['twig']->render('dashboard/dashboard.twig');
                })->bind("dashboard");

        return $controllers;
    }

}

?>

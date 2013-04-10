<?php

namespace Crudo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class Login implements ControllerProviderInterface {

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $app->get('/', function(Request $request) use ($app) {
                    $form = $app['form.factory']->create(new \Crudo\Form\Login());

                    return $app['twig']->render('login/login.twig', array(
                                'error' => $app['security.last_error']($request),
                                'last_username' => $app['session']->get('_security.last_username'),
                                'form' => $form->createView()
                            ));
                });

        return $controllers;
    }

}
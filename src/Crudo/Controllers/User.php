<?php

namespace Crudo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\User as SecurityCoreUser;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\TwitterBootstrapView;
use Crudo\Config;

class User implements ControllerProviderInterface
{

    public function connect(Application $app)
    {

        $controllers = $app['controllers_factory'];

        // ------ List ------
        $controllers->get('list', function (Request $request, Application $app) {
                    $page = $request->query->get('page', 1);

                    $keyword = $request->query->get('keyword', false);

                    if ($keyword != false) {
                        $query = $app['entityManager']->createQuery("SELECT u FROM Crudo\Entity\User u WHERE u.username LIKE '%$keyword%'");
                    } else {
                        $query = $app['entityManager']->createQuery("SELECT u FROM Crudo\Entity\User u");
                    }

                    $user = $query->getResult();

                    $adapter = new ArrayAdapter($user);
                    $pagerfanta = new Pagerfanta($adapter);

                    $pagerfanta->setMaxPerPage(Config\Config::getInstance()->getPaginationPerPage());
                    $pagerfanta->setCurrentPage($page);

                    $routeGenerator = function($page) use($keyword) {
                                $route = '?page=' . $page;

                                if ($keyword != false) {
                                    $route .= '&keyword=' . $keyword;
                                }

                                return $route;
                            };

                    $view = new TwitterBootstrapView();
                    $pagination = $view->render($pagerfanta, $routeGenerator, array(
                        'proximity' => 3,
                            ));

                    return $app['twig']->render('user/list.twig', array('keyword' => $keyword, 'pagination' => $pagination, 'my_pager' => $pagerfanta));
                })->bind("user.list");


        // ------ Add ------
        $controllers->match('add', function (Request $request) use ($app) {

                    $form = $app['form.factory']->create(new \Crudo\Form\CreateUser($app));

                    if ('POST' == $request->getMethod()) {
                        $form->bind($request);

                        if ($form->isValid()) {

                            $data = $form->getData();

                            $entity = new \Crudo\Entity\User();

                            $encpassword = User::encodePassword($data['username'], $data['password_repeated'], $app);

                            $entity->setName($app->escape($data['name']));
                            $entity->setSurname($app->escape($data['surname']));
                            $entity->setUsername($app->escape($data['username']));
                            $entity->setPassword($app->escape($encpassword));
                            $entity->setEmail($app->escape($data['email']));
                            $entity->setRoles($app->escape($data['roles']));

                            $app['entityManager']->persist($entity);
                            $app['entityManager']->flush();

                            $app["session"]->setFlash("success", "The user has been added.");

                            return $app->redirect($app["url_generator"]->generate('user.list'));
                        }
                    }

                    return $app['twig']->render('user/add.twig', array('form' => $form->createView()));
                })->bind("user.add");

        // ------ Edit ------
        $controllers->match('edit/{id}', function ($id) use ($app) {

                    $user = $app['entityManager']->getRepository('Crudo\Entity\User')->findBy(array('id' => $id));

                    if (count($user) == 0) {
                        $app["session"]->setFlash("error", "Sorry, I could not find the user to edit.");
                        return $app->redirect($app["url_generator"]->generate('user.list'));
                    }

                    $formEdit = $app['form.factory']->create(new \Crudo\Form\EditUser($app, $id));
                    $formEdit->setData($user[0]->getArray());

                    $formPassword = $app['form.factory']->create(new \Crudo\Form\ChangePassword());

                    $startTab = '';

                    if ("POST" === $app['request']->getMethod()) {
                        if ($app['request']->get('ChangePassword') !== null) {

                            $startTab = "password";

                            $formPassword->bindRequest($app["request"]);

                            if ($formPassword->isValid()) {

                                $data = $formPassword->getData();

                                $encpassword = User::encodePassword($user[0]->getUsername(), $data['password_repeated'], $app);

                                $user[0]->setPassword($app->escape($encpassword));

                                $app['entityManager']->merge($user[0]);
                                $app['entityManager']->flush();

                                $app["session"]->setFlash("success", "The password has been updated.");

                                return $app->redirect($app["url_generator"]->generate('user.list'));
                            }
                        } else {

                            $formEdit->bindRequest($app["request"]);

                            if ($formEdit->isValid()) {

                                $data = $formEdit->getData();

                                $user[0]->setName($app->escape($data['name']));
                                $user[0]->setSurname($app->escape($data['surname']));
                                $user[0]->setUsername($app->escape($data['username']));
                                $user[0]->setEmail($app->escape($data['email']));
                                $user[0]->setRoles($app->escape($data['roles']));

                                $app['entityManager']->merge($user[0]);
                                $app['entityManager']->flush();

                                $app["session"]->setFlash("success", "The user has been updated.");

                                return $app->redirect($app["url_generator"]->generate('user.list'));
                            }
                        }
                    }

                    return $app['twig']->render('user/edit.twig', array('id' => $id, 'form' => $formEdit->createView(), 'changepassword' => $formPassword->createView(), 'startTab' => $startTab));
                })->bind("user.edit");

        // ------ Delete
        $controllers->match('delete/{id}', function ($id) use ($app) {

                    $check = $app['entityManager']->getRepository('Crudo\Entity\User')->findAll();

                    if (count($check) == 1) {
                        $app["session"]->setFlash("error", "Sorry, I can't remove the last user of the world.");
                        return $app->redirect($app["url_generator"]->generate('user.list'));
                    }

                    $user = $app['entityManager']->getRepository('Crudo\Entity\User')->findBy(array('id' => $id));

                    if (count($user) == 0) {
                        $app["session"]->setFlash("error", "Sorry, I could not find the user to delete.");
                        return $app->redirect($app["url_generator"]->generate('user.list'));
                    }

                    $app['entityManager']->remove($user[0]);
                    $app['entityManager']->flush();

                    $app["session"]->setFlash("success", "The user has been deleted.");

                    return $app->redirect($app["url_generator"]->generate('user.list'));
                })->bind("user.delete");



        return $controllers;
    }

    static function encodePassword($username, $nonEncodedPassword, $app)
    {
        $user = new SecurityCoreUser($username, $nonEncodedPassword);

        $encoder = $app['security.encoder_factory']->getEncoder($user);

        $encodedPassword = $encoder->encodePassword($nonEncodedPassword, $user->getSalt());

        return $encodedPassword;
    }

}

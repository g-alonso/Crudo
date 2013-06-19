<?php

namespace Crudo\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

class CreateUser extends AbstractType {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $app = $this->app;

        $builder->add('name', "text", array("constraints" => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)))));
        $builder->add('surname', "text", array("constraints" => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)))));
        $builder->add('username', "text", array(
            "constraints" => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)), new Assert\Callback(array(
                    "methods" => array(function ($username, ExecutionContext $context) use ($app) {
                            $user = $app['entityManager']->getRepository('Crudo\Entity\User')->findBy(array('username' => $username));
                            if (count($user) > 0) {
                                $context->addViolation("Username taken");
                            }
                        }))))
                )
        );
        $builder->add('password_repeated', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'options' => array('required' => true),
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
        ));
        $builder->add('email', "email", array("constraints" => array(new Assert\NotBlank(), new Assert\Email())));
        $builder->add('roles', 'choice', array(
            'choices' => array('ROLE_ADMIN' => 'ROLE_ADMIN', 'ROLE_USER' => 'ROLE_USER'),
            'expanded' => false,
        ));
    }

    public function getName() {
        return "CreateUser";
    }

}

?>

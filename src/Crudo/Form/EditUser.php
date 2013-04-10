<?php

namespace Crudo\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

class EditUser extends AbstractType {

    private $app;
    private $id;

    public function __construct($app, $id) {
        $this->app = $app;
        $this->id = $id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $app = $this->app;
        $id = $this->id;

        $builder->add('name', "text", array("constraints" => array(new Assert\NotBlank(), new Assert\MinLength(3))));
        $builder->add('surname', "text", array("constraints" => array(new Assert\NotBlank(), new Assert\MinLength(3))));
        $builder->add('username', "text", array("constraints" => array(
                new Assert\NotBlank(),
                new Assert\MinLength(5),
                new Assert\Callback(array(
                    "methods" => array(
                        function ($username, ExecutionContext $context) use ($app, $id) {

                            $user = $app['entityManager']->getRepository('Crudo\Entity\User')->findBy(array('username' => $username));

                            if (count($user) > 0) {
                                if ($user[0]->getId() != $id) {
                                    $context->addViolation("Username taken");
                                }
                            }
                        }))))
                )
        );
        $builder->add('email', "email", array("constraints" => array(new Assert\NotBlank(), new Assert\Email())));
        $builder->add('roles', 'choice', array(
            'choices' => array('ROLE_ADMIN' => 'ROLE_ADMIN', 'ROLE_USER' => 'ROLE_USER'),
            'expanded' => false,
        ));
    }

    public function getName() {
        return "EditUser";
    }

}

?>

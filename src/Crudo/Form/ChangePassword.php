<?php

namespace Crudo\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangePassword extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('password_repeated', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'options' => array('required' => true),
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
        ));
    }

    public function getName() {
        return "ChangePassword";
    }

}

?>

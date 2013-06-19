<?php

namespace Crudo\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class Login extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('_username', "text", array("attr" => array("class" => "span4")));
        $builder->add('_password', "password", array("attr" => array("class" => "span4")));
    }

    public function getName() {
        return "login";
    }

}
<?php

namespace Crudo\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface {

    private $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function loadUserByUsername($username) {
        $user = $this->em->getRepository('Crudo\Entity\User')->findBy(array('username' => $username));

        if (count($user) == 0) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        
        return new User($user[0]->getUsername(), $user[0]->getPassword(), explode(',', $user[0]->getRoles()), true, true, true, true);
    }

    public function refreshUser(UserInterface $user) {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }

}

?>

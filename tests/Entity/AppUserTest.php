<?php

namespace App\test\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppUserTest extends KernelTestCase{
    
    public function getEntity() {
        return (new User())
        ->setEmail('user@test.fr')
        ->setPassword('12345678')
        ->setLastname('Michel')
        ->setFirstname('Marchand')
        ;
    }

    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);

        $messages = [];

        foreach ($errors as $error){
            $messages[] = $error->getPropertyPath().' => '.$error->getMessage();
        }

        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidUser()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }



    public function testConstraintsEmailUser()
    {
        $this->assertHasErrors($this->getEntity()->setEmail('test'), 1);
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
        $this->assertHasErrors($this->getEntity()->setEmail('toto@gmail.com'), 1);
    }



    public function testConstraintsFullName()
    {
        $this->assertHasErrors($this->getEntity()->setFirstname(''), 1);
        $this->assertHasErrors($this->getEntity()->setLastname(''), 1);
    }
}
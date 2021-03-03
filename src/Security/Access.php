<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;

class Access {

    private $security;
    private $userRepository;
    public function __construct(Security $security, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    public function accessDashboard(int $id)
    {
        $user = $this->userRepository->findOneBy(['id' =>$id]);
        if($this->security->getUser() == $user){
            return true;
        }
        return false;
    }
}
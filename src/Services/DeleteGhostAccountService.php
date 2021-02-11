<?php

namespace App\Services;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteGhostAccountService {

    private $userRepository;

    private $manager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $manager)
    {
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    public function DeleteGhostAccount()
    {
        $AccountGhots = $this->userRepository->loadUserByComfirmationAccount(false);

        foreach ($AccountGhots as $ghots){
            $this->manager->remove($ghots);
        }
        $this->manager->flush();
    }
}
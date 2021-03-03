<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Persistence\ObjectManager;
use App\Services\CreateProjectMinimaHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    private $createProjectHelper;

    public function __construct(CreateProjectMinimaHelper $createProjectHelper)
    {
        $this->createProjectHelper = $createProjectHelper;
    }

    public function load(ObjectManager $manager)
    {
        $this->createProjectHelper->createProjectMinima();
        UserFactory::new()->createMany(25);
        
        $manager->flush();
    }
}

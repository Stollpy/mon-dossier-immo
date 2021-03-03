<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Proxy;
use App\Repository\UserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\RepositoryProxy;
use App\Services\IndividualDataService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static User|Proxy find($criteria)
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy first(string $sortedField = 'id')
 * @method static User|Proxy last(string $sortedField = 'id')
 * @method static User|Proxy random(array $attributes = [])
 * @method static User|Proxy randomOrCreate(array $attributes = [])
 * @method static User[]|Proxy[] all()
 * @method static User[]|Proxy[] findBy(array $attributes)
 * @method static User[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create($attributes = [])
 */
final class UserFactory extends ModelFactory
{
    private $encoder;
    private $dataService;

    public function __construct(UserPasswordEncoderInterface $encoder, IndividualDataService $dataService)
    {
        $this->encoder = $encoder;
        $this->dataService = $dataService;
        parent::__construct();

        // TODO inject services if required (https://github.com/zenstruck/foundry#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'password' => '12345678',
            'createdAt' => self::faker()->dateTimeBetween('-3 years', 'now', 'Europe/Paris'),
            'accountConfirmation' => true

        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            ->afterInstantiate(function(User $user) {
                $plainPassword = $user->getPassword();
                $hashedPassword = $this->encoder->encodePassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $individual = $this->dataService->createIndividualFixtures($user, 'individual');
                $this->dataService->CreateIndividualData($individual);
            })
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}

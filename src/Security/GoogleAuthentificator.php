<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GoogleAuthentificator  extends SocialAuthenticator 
{
     /**
     * @var ClientRegistry
     */
    private $clientRegistry;

     /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var UserRepository
     */
    private $userRepository;

     /**
     * @var RouterInterface
     */
    private $router;


    /**
     * GoogleAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $manager
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $manager, RouterInterface $router, UserRepository $userRepository)
    {
        $this->clientRegistry = $clientRegistry;
        $this->manager = $manager;
        $this->router = $router;
        $this->userRepository = $userRepository;        
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        // return $request->attributes->get('_route') === 'connect_google_check';
        return $request->getPathInfo() == '/connect/google/check' && $request->isMethod('GET');

    }

    /**
     * @param Request $request
     */
    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

     /**
     * @param UserProviderInterface $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleCLient()->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        $user = $this->userRepository->loadUserByEmail($email);
        if(!$user)
        {
            
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($googleUser->getFirstname());
            $user->setLastname($googleUser->getLastname());
            $user->setAccountConfirmation(true);
            $this->manager->persist($user);
            $this->manager->flush();
        }

        return $user;
    }

    /**
     * @return \KnpU\OAuth2ClientBundle\Client\OAuth2Client
     */
    public function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            'security.login'
        );
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        // return new Response($message, Response::HTTP_FORBIDDEN);
    }

     /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // return null;
    }
    
}
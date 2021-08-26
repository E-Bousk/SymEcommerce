<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
       $this->encoder= $encoder;
    }
    
    public function supports(Request $request)
    {
        //voir vidéo 14.12
        // dd($request);
        return $request->attributes->get('_route') === 'security_login' 
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        //voir vidéo 14.13
        // dd($request);
        
        // retourn un tableaux de données extraites de la requetes pour afficher « $utils->getLastUsername() » dans le dump du fichier securityController.php
        // voir les commentaires de la page https://learn.web-develop.me/courses/symfony-5-le-guide-complet-debutants-et-intermediaires/528868-la-securite-authentification-1-heure-et-40-minutes/1529289-obtenir-la-raison-de-l-echec-de-l-authentification-authenticationutils
        // $request->attributes->set(Security::LAST_USERNAME, $request->request->get('login')['email']);

        return $request->request->get('login');
        // return $request->get('login'); // paramètre 'login' est un array avec 3 infos


    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // dd($credentials, $userProvider->loadUserByUsername($credentials['email']));
        try {
            return $userProvider->loadUserByUsername($credentials['email']);
        } catch(UsernameNotFoundException $e) {
            throw new AuthenticationException("Cette adresse email est inconnue");
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // dd($credentials, $user);
        $isValid= $this->encoder->isPasswordValid($user, $credentials['password']);
        if (!$isValid) {
            throw new AuthenticationException("Les informations de connexions ne correspondent pas");
        }
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // dd('échec', $exception);

         // retourn un tableaux de données extraites de la requetes pour afficher « $utils->getLastUsername() » dans le dump du fichier securityController.php
        // voir les commentaires de la page https://learn.web-develop.me/courses/symfony-5-le-guide-complet-debutants-et-intermediaires/528868-la-securite-authentification-1-heure-et-40-minutes/1529148-modifier-les-messages-d-erreur
        // $login = $request->request->get('login');
        // $request->attributes->set(Security::LAST_USERNAME, $login['email']);
        $request->attributes->set(Security::LAST_USERNAME, $request->get('login')['email']);

        $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // dd('succès');
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Voir Video 15.2
        return new RedirectResponse('/login');
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
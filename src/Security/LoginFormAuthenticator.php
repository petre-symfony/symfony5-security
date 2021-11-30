<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator {
	private UserRepository $repository;
	private RouterInterface $router;

	public function __construct(UserRepository $repository, RouterInterface $router){

		$this->repository = $repository;
		$this->router = $router;
	}

	public function authenticate(Request $request): PassportInterface {
		$email = $request->request->get('email');
		$password = $request->request->get('password');

		return new Passport(
			new UserBadge($email, function($userIdentifier) {
				$user = $this->repository->findOneBy(['email' => $userIdentifier]);

				if(!$user) {
					throw new UserNotFoundException();
				}

				return $user;
			}),
			new PasswordCredentials($password),
			[
				new CsrfTokenBadge(
					'authenticate',
					$request->request->get('_csrf_token')
				),
				(new RememberMeBadge())->enable()
			]
		);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		return new RedirectResponse(
			$this->router->generate('app_homepage')
		);
	}

	protected function getLoginUrl(Request $request): string {
		return $this->router->generate('app_login');
	}

}

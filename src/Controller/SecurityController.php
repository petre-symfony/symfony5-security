<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class SecurityController extends BaseController {
	/**
	 * @Route("/login", name="app_login")
	 */
	public function login(AuthenticationUtils $authentication): Response {
		return $this->render('security/login.html.twig', [
			'error' => $authentication->getLastAuthenticationError(),
			'last_username' => $authentication->getLastUsername()
		]);
	}

	/**
	 * @Route("/logout", name="app_logout")
	 */
	public function logout() {
		throw new \Exception('logout() should never be reached');
	}

	/**
	 * @Route("/authenticate/2fa/enable", name="app_2fa_enable")
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 */
	public function enable2fa(TotpAuthenticatorInterface $totpAuthenticator, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if(!$user->isTotpAuthenticationEnabled()){
			$user->setTotpSecret($totpAuthenticator->generateSecret());

			$entityManager->flush();
		}

		dd($totpAuthenticator->getQRContent($user));
	}
}

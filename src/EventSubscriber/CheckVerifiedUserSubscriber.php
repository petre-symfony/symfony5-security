<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface {
	public function onCheckPassport(CheckPassportEvent $event) {
		$passport = $event->getPassport();

		if(!$passport instanceof UserPassportInterface) {
			throw new \Exception('Unexpected passport type');
		}

		$user = $passport->getUser();

		if (!$user instanceof User) {
			throw new \Exception('Unexpected user type');
		}

		if(!$user->getIsVerified()){
			throw new AccountNotVerifiedAuthenticationException(
				'Please verify your account before logging in'
			);
		}
	}

	public static function getSubscribedEvents() {
		return [
			CheckPassportEvent::class => ['onCheckPassport', -10 ]
		];
	}

}
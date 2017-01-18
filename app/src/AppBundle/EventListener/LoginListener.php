<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $encoderFactory;
    private $om;

    public function __construct(EncoderFactoryInterface $encoderFactory, ObjectManager $om)
    {
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $token = $event->getAuthenticationToken();

        // Migrate the user to the new hashing algorithm if is using the legacy one
        if ($user->hasLegacyPassword()) {
            // Credentials can be retrieved thanks to the false value of
            // the erase_credentials parameter in security.yml
            $plainPassword = $token->getCredentials();

            $user->setOldPassword(null);
            $encoder = $this->encoderFactory->getEncoder($user);

            $user->setPassword(
                $encoder->encodePassword($plainPassword, $user->getSalt())
            );

            $this->om->persist($user);
            $this->om->flush();
        }

        // We don't need any more credentials
        $token->eraseCredentials();
    }
}
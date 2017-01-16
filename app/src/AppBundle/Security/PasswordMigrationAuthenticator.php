<?php
// src/AppBundle/Security/TimeAuthenticator.php
namespace AppBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class PasswordMigrationAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory, ObjectManager $om)
    {
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        $password = $user->hasLegacyPassword() ? $user->getOldPassword() : $user->getPassword();
        $passwordValid = $encoder->isPasswordValid($password, $token->getCredentials(), $user->getSalt());

        if ($user->hasLegacyPassword()) {
            $this->reencodeUserPassword($user, $token);
        }

        if ($passwordValid) {
            return new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                $providerKey,
                $user->getRoles()
            );
        }

        // CAUTION: this message will be returned to the client
        // (so don't put any un-trusted messages / error strings here)
        throw new CustomUserMessageAuthenticationException('Invalid username or password');
    }

    protected function reencodeUserPassword(UserInterface $user, TokenInterface $token)
    {
        $user->setOldPassword(null);

        // Use the new encoder
        $encoder = $this->encoderFactory->getEncoder($user);

        $user->setPassword($encoder->encodePassword($token->getCredentials(), $user->getSalt()));

        $this->om->persist($user);
        $this->om->flush();
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}

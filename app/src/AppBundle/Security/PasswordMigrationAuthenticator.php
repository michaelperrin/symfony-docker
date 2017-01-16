<?php
// src/AppBundle/Security/TimeAuthenticator.php
namespace AppBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class PasswordMigrationAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoder;
    private $oldEncoder;
    private $om;

    public function __construct(UserPasswordEncoderInterface $encoder, PasswordEncoderInterface $oldEncoder, ObjectManager $om)
    {
        $this->encoder = $encoder;
        $this->oldEncoder = $oldEncoder;
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

        $passwordValid = $this->isPasswordValid($user, $token);

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

    protected function isPasswordValid(UserInterface $user, TokenInterface $token)
    {
        $passwordValid = false;

        if ($user->getOldPassword()) {
            $plainPassword = $token->getCredentials();

            $passwordValid = $this->oldEncoder->isPasswordValid($user->getOldPassword(), $plainPassword, $user->getSalt());

            if ($passwordValid) {
                // Password is valid. Encode the password with the new encoder, and remove old password.
                $this->reencodeUserPassword($user, $token);
            }
        } else {
            $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials(), $user->getSalt());
        }

        return $passwordValid;
    }

    protected function reencodeUserPassword(UserInterface $user, TokenInterface $token)
    {
        $user
            ->setPassword($this->encoder->encodePassword($user, $token->getCredentials()))
            ->setOldPassword(null)
        ;

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

<?php

namespace LaravelDoctrine\Passport\Bridge;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Auth\Authenticatable;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Contracts\OAuthUserRepository;
use LaravelDoctrine\Passport\Passport;
use RuntimeException;
use Illuminate\Contracts\Hashing\Hasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param EntityManager $entityManager
     */
    public function __construct(Hasher $hasher, EntityManager $entityManager)
    {
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        if (is_null($entityClass = Passport::$userEntity)) {
            throw new RuntimeException('Unable to determine user entity from configuration.');
        }

        /** @var OAuthUserRepository $repository */
        $repository = $this->entityManager->getRepository($entityClass);

        /** @var OAuthUser|Authenticatable $user */
        $user = $repository->findForPassport($username);

        if (! $user) {
            return null;
        }

        if (! $user->validateForPassportPasswordGrant($password)) {
            return null;
        }

        return new User($user->getAuthIdentifier());
    }
}

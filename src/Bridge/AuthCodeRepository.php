<?php

namespace LaravelDoctrine\Passport\Bridge;

use Illuminate\Database\Connection;
use LaravelDoctrine\Passport\Repositories\AuthCodeRepository as DoctrineAuthCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use FormatsScopesForStorage;

    /**
     * The database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * @var DoctrineAuthCodeRepository
     */
    private $authCodeRepository;

    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Database\Connewtion $database
     * @param DoctrineAuthCodeRepository $authCodeRepository
     */
    public function __construct(Connection $database, DoctrineAuthCodeRepository $authCodeRepository)
    {
        $this->database = $database;
        $this->authCodeRepository = $authCodeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $this->authCodeRepository->create(
            $authCodeEntity->getIdentifier(),
            $authCodeEntity->getUserIdentifier(),
            $authCodeEntity->getClient()->getIdentifier(),
            $this->formatScopesForStorage($authCodeEntity->getScopes()),
            $authCodeEntity->getExpiryDateTime()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $this->authCodeRepository->revoke($codeId);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return $this->authCodeRepository->isTokenRevoked($codeId);
    }
}

<?php

namespace LaravelDoctrine\Passport\Bridge;

use DateTime;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use LaravelDoctrine\Passport\Events\AccessTokenCreated;
use LaravelDoctrine\Passport\Repositories\TokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use FormatsScopesForStorage;

    /**
     * The database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    private $events;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Database\Connection $database
     * @param Dispatcher $events
     * @param TokenRepository $tokenRepository
     */
    public function __construct(Connection $database, Dispatcher $events, TokenRepository $tokenRepository)
    {
        $this->events = $events;
        $this->database = $database;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessToken($userIdentifier, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->tokenRepository->create(
            $id = $accessTokenEntity->getIdentifier(),
            $userId = $accessTokenEntity->getUserIdentifier(),
            $clientId = $accessTokenEntity->getClient()->getIdentifier(),
            $this->formatScopesForStorage($accessTokenEntity->getScopes()),
            $accessTokenEntity->getExpiryDateTime()
        );

        $this->events->fire(new AccessTokenCreated($id, $userId, $clientId));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        $this->tokenRepository->revoke($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return $this->tokenRepository->isTokenRevoked($tokenId);
    }
}

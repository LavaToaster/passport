<?php

namespace LaravelDoctrine\Passport\Bridge;

use Illuminate\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use LaravelDoctrine\Passport\Events\RefreshTokenCreated;
use LaravelDoctrine\Passport\Repositories\RefreshTokenRepository as DoctrineRefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
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
     * @var DoctrineRefreshTokenRepository
     */
    private $refreshTokenRepository;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Database\Connection  $database
     * @return void
     */
    public function __construct(Connection $database, Dispatcher $events, DoctrineRefreshTokenRepository $refreshTokenRepository)
    {
        $this->events = $events;
        $this->database = $database;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->refreshTokenRepository->create(
            $id = $refreshTokenEntity->getIdentifier(),
            $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            $refreshTokenEntity->getExpiryDateTime()
        );

        $this->events->fire(new RefreshTokenCreated($id, $accessTokenId));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->refreshTokenRepository->revoke($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return $this->refreshTokenRepository->isTokenRevoked($tokenId);
    }
}

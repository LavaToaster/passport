<?php

namespace LaravelDoctrine\Passport\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\Token;

trait HasApiToken
{
    /**
     * @ORM\OneToMany(targetEntity="LaravelDoctrine\Passport\Entities\Client", mappedBy="user")
     *
     * @var Client[]|ArrayCollection
     */
    protected $clients;

    /**
     * @ORM\OneToMany(targetEntity="LaravelDoctrine\Passport\Entities\Token", mappedBy="user")
     *
     * @var Token[]|ArrayCollection
     */
    protected $tokens;

    /**
     * @var Token
     */
    protected $accessToken;

    /**
     * Boots the trait by instantiating the relationships.
     *
     * @return void
     */
    protected function bootHasApiToken()
    {
        $this->clients = new ArrayCollection();
        $this->tokens = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|Client[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param Client[] $clients
     */
    public function setClients(array $clients)
    {
        $this->clients = new ArrayCollection($clients);
    }

    /**
     * @return Token[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param Token[] $tokens
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = new ArrayCollection($tokens);
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return Token|null
     */
    public function getToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token for the user.
     *
     * @param  \LaravelDoctrine\Passport\Entities\Token $token
     * @return $this
     */
    public function withAccessToken(Token $token)
    {
        $this->accessToken = $token;

        return $this;
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param  string $scope
     * @return bool
     */
    public function tokenCan($scope)
    {
        return $this->accessToken ? $this->accessToken->can($scope) : false;
    }

    /**
     * Check if the user owns the given client
     *
     * @param Client|int $client
     * @return bool
     */
    public function ownsClient($client)
    {
        $clientId = $client instanceof Client ? $client->getId() : (int) $client;

        foreach ($this->getClients() as $client) {
            if ($clientId === $client->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates the given password against the user
     *
     * @param string $password
     * @return bool
     */
    public function validateForPassportPasswordGrant($password) {
        // TODO: Throw an error?
        return false;
    }
}

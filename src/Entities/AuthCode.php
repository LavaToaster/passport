<?php

namespace LaravelDoctrine\Passport\Entities;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Traits\Expires;
use LaravelDoctrine\Passport\Traits\Revocable;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth_auth_codes")
 */
class AuthCode
{
    use Revocable, Expires;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LaravelDoctrine\Passport\Contracts\OAuthUser", inversedBy="clients")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var OAuthUser
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="tokens")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     *
     * @var Client
     */
    protected $client;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @var array
     */
    protected $scopes;

    /**
     * @param string $id
     * @param OAuthUser $user
     * @param Client $client
     * @param array $scopes
     * @param \DateTime $expiry
     */
    public function __construct($id, OAuthUser $user, Client $client, array $scopes, \DateTime $expiry)
    {
        $this->setId($id);
        $this->setUser($user);
        $this->setClient($client);
        $this->setScopes($scopes);
        $this->setExpiresAt($expiry);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return OAuthUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param OAuthUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }
}

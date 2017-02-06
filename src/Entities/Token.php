<?php

namespace LaravelDoctrine\Passport\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Traits\Expires;
use LaravelDoctrine\Passport\Traits\Revocable;
use LaravelDoctrine\Passport\Traits\Serializable;
use LaravelDoctrine\Passport\Traits\Timestamps;

/**
 * @ORM\Entity(repositoryClass="LaravelDoctrine\Passport\Repositories\TokenRepository")
 * @ORM\Table(name="oauth_access_tokens")
 * @ORM\HasLifecycleCallbacks()
 */
class Token implements Jsonable, JsonSerializable
{
    use Timestamps, Revocable, Expires, Serializable;

    protected $attributes = [
        'id',
        'name',
        'scopes',
        'client',
    ];

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
     * @var Client|ArrayCollection
     */
    protected $client;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $name;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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

    /**
     * Determine if the token has a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function can($scope)
    {
        return in_array('*', $this->scopes) ||
            array_key_exists($scope, array_flip($this->scopes));
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function cant($scope)
    {
        return ! $this->can($scope);
    }

    /**
     * Revoke the token instance.
     *
     * @return void
     */
    public function revoke()
    {
        $this->revoked = true;

        // Persist and save straight away
        /** @var EntityManager $em */
        $em = resolve('em');
        $em->persist($this);
        $em->flush($this);
    }

    /**
     * Determine if the token is a transient JWT token.
     *
     * @return bool
     */
    public function transient()
    {
        return false;
    }
}

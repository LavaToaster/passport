<?php

namespace LaravelDoctrine\Passport\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Traits\Revocable;
use LaravelDoctrine\Passport\Traits\Serializable;
use LaravelDoctrine\Passport\Traits\Timestamps;

/**
 * @ORM\Entity(repositoryClass="LaravelDoctrine\Passport\Repositories\ClientRepository")
 * @ORM\Table(name="oauth_clients")
 * @ORM\HasLifecycleCallbacks()
 */
class Client implements Jsonable, JsonSerializable
{
    use Timestamps, Revocable, Serializable;

    protected $attributes = [
        'id',
        'name',
        'redirect',
        'secret',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
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
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    protected $secret;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    protected $redirect;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $personalAccessClient;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $passwordClient;

    /**
     * @var AuthCode[]|ArrayCollection
     */
    protected $authCodes;

    /**
     * @ORM\OneToMany(targetEntity="Token", mappedBy="client")
     *
     * @var Token[]|ArrayCollection
     */
    protected $tokens;

    public function __construct()
    {
        $this->authCodes = new ArrayCollection();
        $this->tokens = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
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
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->getRedirect();
    }

    /**
     * @param string $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return bool
     */
    public function isPersonalAccessClient()
    {
        return $this->personalAccessClient;
    }

    /**
     * @param bool $personalAccessClient
     */
    public function setPersonalAccessClient($personalAccessClient)
    {
        $this->personalAccessClient = $personalAccessClient;
    }

    /**
     * @return bool
     */
    public function isPasswordClient()
    {
        return $this->passwordClient;
    }

    /**
     * @param bool $passwordClient
     */
    public function setPasswordClient($passwordClient)
    {
        $this->passwordClient = $passwordClient;
    }

    /**
     * @return AuthCode[]
     */
    public function getAuthCodes()
    {
        return $this->authCodes;
    }

    /**
     * @param AuthCode[] $authCodes
     */
    public function setAuthCodes(array $authCodes)
    {
        $this->authCodes = new ArrayCollection($authCodes);
    }

    /**
     * @return mixed
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param mixed $tokens
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Get all of the authentication codes for the client.
     *
     * @return AuthCode[]|ArrayCollection
     */
    public function authCodes()
    {
        return $this->authCodes;
    }

    /**
     * Determine if the client is a "first party" client.
     *
     * @return bool
     */
    public function isFirstParty()
    {
        return $this->isPersonalAccessClient() || $this->isPasswordClient();
    }

    protected function getHiddenAttributes()
    {
        return [
            'secret'
        ];
    }
}

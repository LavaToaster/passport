<?php

namespace LaravelDoctrine\Passport\Entities;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Passport\Traits\DoctrineEloquent;
use LaravelDoctrine\Passport\Traits\Expires;
use LaravelDoctrine\Passport\Traits\Revocable;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth_refresh_tokens")
 */
class RefreshToken
{
    use Revocable, Expires;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Token")
     * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id")
     *
     * @var Token
     */
    protected $accessToken;

    public function __construct($id, Token $token, \DateTime $expiresAt)
    {
        $this->setId($id);
        $this->setAccessToken($token);
        $this->setExpiresAt($expiresAt);
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
     * @return Token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param Token $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
}

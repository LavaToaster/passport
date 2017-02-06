<?php

namespace LaravelDoctrine\Passport\Traits;

trait Revocable
{
    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $revoked = false;

    /**
     * @return bool
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * @param bool $revoked
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;
    }

    public function revoke()
    {
        $this->setRevoked(true);
    }
}

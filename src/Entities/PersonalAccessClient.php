<?php

namespace LaravelDoctrine\Passport\Entities;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Passport\Traits\DoctrineEloquent;
use LaravelDoctrine\Passport\Traits\Timestamps;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth_personal_access_clients")
 * @ORM\HasLifecycleCallbacks()
 */
class PersonalAccessClient
{
    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Client")
     *
     * @var Client
     */
    protected $client;

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
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}

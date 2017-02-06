<?php

namespace LaravelDoctrine\Passport\Repositories;

use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Collection;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\PersonalAccessClient;
use LaravelDoctrine\Passport\Passport;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository extends EntityRepository
{
    /**
     * Get an active client by the given ID.
     *
     * @param  int  $id
     * @return Client|null
     */
    public function findActive($id)
    {
        /** @var Client $client */
        $client = $this->find($id);

        return $client && ! $client->isRevoked() ? $client : null;
    }

    /**
     * Get the client instances for the given user ID.
     *
     * @param  OAuthUser|mixed  $user
     * @return Client[]|Collection
     */
    public function forUser($user)
    {
        $user = $this->getEntity(OAuthUser::class, $user, true, Passport::$userEntity);

        return collect($this->findBy(['user' => $user->getId()], ['name' => 'desc']));
    }

    /**
     * Get the active client instances for the given user ID.
     *
     * @param  mixed  $user
     * @return \Illuminate\Support\Collection
     */
    public function activeForUser($user)
    {
        $theStuff = $this->forUser($user)->reject(function (Client $client) {
            return $client->isRevoked();
        })->values();

        return $theStuff;
    }

    /**
     * Get the personal access token client for the application.
     *
     * @return Client
     */
    public function personalAccessClient()
    {
        if (Passport::$personalAccessClient) {
            return $this->find(Passport::$personalAccessClient);
        } else {
            /** @var PersonalAccessClient $pac */
            $pac = $this->_em
                ->createQueryBuilder()
                ->select('client')
                ->from('\LaravelDoctrine\Passport\Entities\PersonalAccessClient', 'client')
                ->orderBy('client.id', 'desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            return $pac->getClient();
        }
    }

    /**
     * Store a new client.
     *
     * @param  OAuthUser|mixed  $user
     * @param  string  $name
     * @param  string  $redirect
     * @param  bool  $personalAccess
     * @param  bool  $password
     * @return Client
     */
    public function create($user, $name, $redirect, $personalAccess = false, $password = false)
    {
        $user = $this->getEntity(OAuthUser::class, $user, true, Passport::$userEntity);

        $client = new Client();
        $client->setUser($user);
        $client->setName($name);
        $client->setSecret(str_random(40));
        $client->setRedirect($redirect);
        $client->setPersonalAccessClient($personalAccess);
        $client->setPasswordClient($password);
        $client->setRevoked(false);

        $this->save($client);

        return $client;
    }

    /**
     * Store a new personal access token client.
     *
     * @param  OAuthUser|mixed  $user
     * @param  string  $name
     * @param  string  $redirect
     * @return Client
     */
    public function createPersonalAccessClient($user, $name, $redirect)
    {
        $user = $this->getEntity(OAuthUser::class, $user, true, Passport::$userEntity);

        return $this->create($user, $name, $redirect, true);
    }

    /**
     * Store a new password grant client.
     *
     * @param  OAuthUser|mixed  $user
     * @param  string  $name
     * @param  string  $redirect
     * @return Client
     */
    public function createPasswordGrantClient($user, $name, $redirect)
    {
        $user = $this->getEntity(OAuthUser::class, $user, true, Passport::$userEntity);

        return $this->create($user, $name, $redirect, false, true);
    }

    /**
     * Update the given client.
     *
     * @param  Client  $client
     * @param  string  $name
     * @param  string  $redirect
     * @return Client
     */
    public function update(Client $client, $name, $redirect)
    {
        $client->setName($name);
        $client->setRedirect($redirect);

        $this->save($client);

        return $client;
    }

    /**
     * Regenerate the client secret.
     *
     * @param  Client  $client
     * @return Client
     */
    public function regenerateSecret(Client $client)
    {
        $client->setSecret(str_random(40));

        $this->save($client);

        return $client;
    }

    /**
     * Determine if the given client is revoked.
     *
     * @param  int  $id
     * @return bool
     */
    public function revoked($id)
    {
        return $this->createQueryBuilder('client')
            ->where('client.id = :clientId')
            ->andWhere('client.revoked = true')
            ->setParameter('clientId', $id)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    /**
     * Delete the given client.
     *
     * @param  Client  $client
     * @return void
     */
    public function delete(Client $client)
    {
        collect($client->getTokens())->each->revoke();

        $client->revoke();

        $this->save($client);
    }

    protected function save(Client $client)
    {
        $this->_em->persist($client);
        $this->_em->flush($client);
    }

    protected function getEntity($class, $value, $nullable, $entity = null)
    {
        if ($nullable && $value === null) {
            return null;
        }

        return $value instanceof $class ? $value : $this->_em->getReference($entity ?: $class, $value);
    }
}

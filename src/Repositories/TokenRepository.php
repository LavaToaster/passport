<?php

namespace LaravelDoctrine\Passport\Repositories;

use Doctrine\ORM\EntityRepository;
use Illuminate\Container\Container;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\Token;
use LaravelDoctrine\Passport\Passport;
use LaravelDoctrine\Passport\PersonalAccessTokenFactory;

class TokenRepository extends EntityRepository
{
    /**
     * Store the given token instance.
     *
     * @param  Token  $token
     * @return void
     */
    public function save(Token $token)
    {
        $this->_em->persist($token);
        $this->_em->flush($token);
    }

    /**
     * Find a valid token for the given user and client.
     *
     * @param  OAuthUser  $user
     * @param  Client  $client
     * @return Token|null
     */
    public function findValidToken($user, $client)
    {
        $query = $this->createQueryBuilder('token')
            ->where('token.client = :clientId')
            ->andWhere('token.user = :userId')
            ->andWhere('token.revoked = false')
            ->andWhere('token.expiresAt > :now')
            ->setParameter('userId', $user->getId())
            ->setParameter('clientId', $client->getId())
            ->setParameter('now', new \DateTime())
            ->orderBy('token.expiresAt', 'desc');

        return array_first($query->getQuery()->getResult());
    }

    public function create($id, $user, $client, $scopes, $expiry)
    {
        $user = $user instanceof OAuthUser ? $user : $this->_em->getReference(Passport::$userEntity, $user);
        $client = $client instanceof Client ? $client : $this->_em->getReference(Client::class, $client);

        $token = new Token($id, $user, $client, $scopes, $expiry);

        $this->save($token);
    }

    public function revoke($id)
    {
        /** @var Token $token */
        $token = $this->find($id);
        $token->setRevoked(true);

        $this->save($token);
    }

    public function isTokenRevoked($id)
    {
        /** @var Token $token */
        $token = $this->find($id);

        return $token ? $token->isRevoked() : true;
    }

    public function createPersonal($user, $name, $scopes = [])
    {
        return Container::getInstance()->make(PersonalAccessTokenFactory::class)->make(
            $user, $name, $scopes
        );
    }
}

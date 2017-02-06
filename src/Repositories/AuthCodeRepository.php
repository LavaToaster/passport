<?php

namespace LaravelDoctrine\Passport\Repositories;

use Doctrine\ORM\EntityRepository;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\AuthCode;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Passport;

class AuthCodeRepository extends EntityRepository
{
    /**
     * Store the given AuthCode instance.
     *
     * @param  AuthCode  $token
     * @return void
     */
    public function save(AuthCode $token)
    {
        $this->_em->persist($token);
        $this->_em->flush($token);
    }

    public function create($id, $user, $client, $scopes, $expiry)
    {
        $user = $user instanceof OAuthUser ? $user : $this->_em->getReference(Passport::$userEntity, $user);
        $client = $client instanceof Client ? $client : $this->_em->getReference(Client::class, $client);

        $authCode = new AuthCode($id, $user, $client, $scopes, $expiry);

        $this->save($authCode);
    }

    public function revoke($id)
    {
        /** @var AuthCode $token */
        $token = $this->find($id);
        $token->setRevoked(true);

        $this->save($token);
    }

    public function isTokenRevoked($id)
    {
        /** @var AuthCode $token */
        $token = $this->find($id);

        return $token ? $token->isRevoked() : true;
    }
}

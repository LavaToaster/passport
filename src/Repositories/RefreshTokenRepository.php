<?php

namespace LaravelDoctrine\Passport\Repositories;

use Doctrine\ORM\EntityRepository;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\RefreshToken;
use LaravelDoctrine\Passport\Entities\Token;
use LaravelDoctrine\Passport\Passport;

class RefreshTokenRepository extends EntityRepository
{
    /**
     * Store the given token instance.
     *
     * @param  RefreshToken  $token
     * @return void
     */
    public function save(RefreshToken $token)
    {
        $this->_em->persist($token);
        $this->_em->flush($token);
    }

    public function create($id, $accessToken, $expiry)
    {
        $accessToken = $accessToken instanceof Token ? $accessToken : $this->_em->getReference(Token::class, $accessToken);

        $token = new RefreshToken($id, $accessToken, $expiry);

        $this->save($token);
    }

    public function revoke($id)
    {
        /** @var RefreshToken $token */
        $token = $this->find($id);
        $token->setRevoked(true);

        $this->save($token);
    }

    public function isTokenRevoked($id)
    {
        /** @var RefreshToken $token */
        $token = $this->find($id);

        return $token ? $token->isRevoked() : true;
    }
}

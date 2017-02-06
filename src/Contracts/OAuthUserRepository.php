<?php

namespace LaravelDoctrine\Passport\Contracts;

interface OAuthUserRepository
{
    /**
     * Find the a user based on the given identifier.
     *
     * @param string $identifier
     * @return OAuthUser
     */
    public function findForPassport($identifier);
}

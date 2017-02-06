<?php

namespace LaravelDoctrine\Passport\Contracts;

use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Contracts\Auth\Authenticatable;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\Token;

interface OAuthUser
{
    /**
     * Returns the id associated with the user
     *
     * @return string|int
     */
    public function getId();

    /**
     * Get all of the user's registered OAuth clients.
     *
     * @return Client[]|ArrayCollection
     */
    public function getClients();

    /**
     * Get all of the access tokens for the user.
     *
     * @return \LaravelDoctrine\Passport\Entities\Token[]|ArrayCollection
     */
    public function getTokens();

    /**
     * Get the current access token being used by the user.
     *
     * @return \LaravelDoctrine\Passport\Entities\Token|null
     */
    public function getToken();

    /**
     * Determine if the current API token has a given scope.
     *
     * @param  string $scope
     * @return bool
     */
    public function tokenCan($scope);

    /**
     * Set the current access token for the user.
     *
     * @param  \LaravelDoctrine\Passport\Entities\Token $token
     * @return $this
     */
    public function withAccessToken(Token $token);

    /**
     * Validates the given password against the user
     *
     * @param string $password
     * @return bool
     */
    public function validateForPassportPasswordGrant($password);
}

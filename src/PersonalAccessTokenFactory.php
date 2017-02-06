<?php

namespace LaravelDoctrine\Passport;

use Doctrine\ORM\EntityManager;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Entities\Token;
use LaravelDoctrine\Passport\Entities\User;
use LaravelDoctrine\Passport\Repositories\ClientRepository;
use LaravelDoctrine\Passport\Repositories\TokenRepository;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;

class PersonalAccessTokenFactory
{
    /**
     * The authorization server instance.
     *
     * @var AuthorizationServer
     */
    protected $server;

    /**
     * The client repository instance.
     *
     * @var ClientRepository
     */
    protected $clients;

    /**
     * The token repository instance.
     *
     * @var TokenRepository
     */
    protected $tokens;

    /**
     * The JWT token parser instance.
     *
     * @var JwtParser
     */
    protected $jwt;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Create a new personal access token factory instance.
     *
     * @param  AuthorizationServer $server
     * @param  ClientRepository $clients
     * @param  TokenRepository $tokens
     * @param  EntityManager $em
     * @param  JwtParser $jwt
     */
    public function __construct(AuthorizationServer $server,
                                ClientRepository $clients,
                                TokenRepository $tokens,
                                EntityManager $em,
                                JwtParser $jwt)
    {
        $this->jwt = $jwt;
        $this->tokens = $tokens;
        $this->server = $server;
        $this->em = $em;
        $this->clients = $clients;
    }

    /**
     * Create a new personal access token.
     *
     * @param  OAuthUser  $user
     * @param  string  $name
     * @param  array  $scopes
     * @return PersonalAccessTokenResult
     */
    public function make($user, $name, array $scopes = [])
    {
        $response = $this->dispatchRequestToAuthorizationServer(
            $this->createRequest($this->clients->personalAccessClient(), $user, $scopes)
        );

        $token = tap($this->findAccessToken($response), function (Token $token) use ($user, $name) {
            $token->setUser($user);
            $token->setName($name);
            $this->tokens->save($token);
        });

        return new PersonalAccessTokenResult(
            $response['access_token'], $token
        );
    }

    /**
     * Create a request instance for the given client.
     *
     * @param  Client     $client
     * @param  OAuthUser  $user
     * @param  array      $scopes
     * @return ServerRequest
     */
    protected function createRequest(Client $client, OAuthUser $user, array $scopes)
    {
        return (new ServerRequest)->withParsedBody([
            'grant_type' => 'personal_access',
            'client_id' => $client->getId(),
            'client_secret' => $client->getSecret(),
            'user_id' => $user->getId(),
            'scope' => implode(' ', $scopes),
        ]);
    }

    /**
     * Dispatch the given request to the authorization server.
     *
     * @param  ServerRequest  $request
     * @return array
     */
    protected function dispatchRequestToAuthorizationServer(ServerRequest $request)
    {
        return json_decode($this->server->respondToAccessTokenRequest(
            $request, new Response
        )->getBody()->__toString(), true);
    }

    /**
     * Get the access token instance for the parsed response.
     *
     * @param  array  $response
     * @return Token
     */
    protected function findAccessToken(array $response)
    {
        return $this->tokens->find(
            $this->jwt->parse($response['access_token'])->getClaim('jti')
        );
    }
}

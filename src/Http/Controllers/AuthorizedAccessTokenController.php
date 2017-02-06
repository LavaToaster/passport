<?php

namespace LaravelDoctrine\Passport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Token;
use LaravelDoctrine\Passport\Repositories\TokenRepository;

class AuthorizedAccessTokenController
{
    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Get all of the authorized tokens for the authenticated user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function forUser(Request $request)
    {
        /** @var OAuthUser $user */
        $user = $request->user();

        return collect($user->getTokens())->filter(function (Token $token) {
            return ! $token->getClient()->isFirstParty() && ! $token->isRevoked();
        })->values();
    }

    /**
     * Delete the given token.
     *
     * @param  Request  $request
     * @param  string  $tokenId
     * @return Response
     */
    public function destroy(Request $request, $tokenId)
    {
        /** @var OAuthUser $user */
        $user = $request->user();

        $token = $this->tokenRepository->find($tokenId);

        /** @var Token $token */
        if (!$token || $token->getUser()->getId() !== $user->getId()) {
            return new Response('', 404);
        }

        $token->revoke();
    }
}

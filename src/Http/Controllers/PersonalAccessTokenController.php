<?php

namespace LaravelDoctrine\Passport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Entities\Token;
use LaravelDoctrine\Passport\Passport;
use LaravelDoctrine\Passport\PersonalAccessTokenResult;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use LaravelDoctrine\Passport\Repositories\TokenRepository;

class PersonalAccessTokenController
{
    /**
     * The validation factory implementation.
     *
     * @var ValidationFactory
     */
    protected $validation;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * Create a controller instance.
     *
     * @param  ValidationFactory  $validation
     * @return void
     */
    public function __construct(ValidationFactory $validation, TokenRepository $tokenRepository)
    {
        $this->validation = $validation;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Get all of the personal access tokens for the authenticated user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function forUser(Request $request)
    {
        /** @var OAuthUser $user */
        $user = $request->user();

        return collect($user->getTokens())->filter(function (Token $token) {
            return $token->getClient()->isPersonalAccessClient() && ! $token->isRevoked();
        })->values();
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  Request  $request
     * @return PersonalAccessTokenResult
     */
    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'scopes' => 'array|in:'.implode(',', Passport::scopeIds()),
        ])->validate();

        return $this->tokenRepository->createPersonal(
            $request->user(),
            $request->name,
            $request->scopes ?: []
        );
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
        if (!$token || $token->isRevoked() || $token->getUser()->getId() !== $user->getId()) {
            return new Response('', 404);
        }

        $token->revoke();
    }
}

<?php

namespace LaravelDoctrine\Passport\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelDoctrine\Passport\Bridge\User;
use LaravelDoctrine\Passport\Contracts\OAuthUser;

trait RetrievesAuthRequestFromSession
{
    /**
     * Get the authorization request from the session.
     *
     * @param  Request  $request
     * @return AuthorizationRequest
     */
    protected function getAuthRequestFromSession(Request $request)
    {
        return tap($request->session()->get('authRequest'), function ($authRequest) use ($request) {
            if (! $authRequest) {
                throw new Exception('Authorization request was not present in the session.');
            }

            /** @var OAuthUser $user */
            $user = $request->user();

            $authRequest->setUser(new User($user->getId()));

            $authRequest->setAuthorizationApproved(true);
        });
    }
}

<?php

namespace LaravelDoctrine\Passport\Http\Controllers;

use LaravelDoctrine\Passport\Passport;

class ScopeController
{
    /**
     * Get all of the available scopes for the application.
     *
     * @return Response
     */
    public function all()
    {
        return Passport::scopes();
    }
}

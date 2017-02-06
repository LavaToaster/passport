<?php

use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Routing\ResponseFactory;

class AuthorizationControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_authorization_view_is_presented()
    {
        LaravelDoctrine\Passport\Passport::tokensCan([
            'scope-1' => 'description',
        ]);

        $server = Mockery::mock(AuthorizationServer::class);
        $response = Mockery::mock(ResponseFactory::class);

        $controller = new LaravelDoctrine\Passport\Http\Controllers\AuthorizationController($server, $response);

        $server->shouldReceive('validateAuthorizationRequest')->andReturn($authRequest = Mockery::mock());

        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('session')->andReturn($session = Mockery::mock());
        $session->shouldReceive('put')->with('authRequest', $authRequest);
        $request->shouldReceive('user')->andReturn('user');

        $authRequest->shouldReceive('getClient->getIdentifier')->andReturn(1);
        $authRequest->shouldReceive('getScopes')->andReturn([new LaravelDoctrine\Passport\Bridge\Scope('scope-1')]);

        $response->shouldReceive('view')->once()->andReturnUsing(function ($view, $data) use ($authRequest) {
            $this->assertEquals('passport::authorize', $view);
            $this->assertEquals('client', $data['client']);
            $this->assertEquals('user', $data['user']);
            $this->assertEquals('description', $data['scopes'][0]->description);

            return 'view';
        });

        $clients = Mockery::mock('LaravelDoctrine\Passport\Repositories\ClientRepository');
        $clients->shouldReceive('find')->with(1)->andReturn('client');

        $tokens = Mockery::mock('LaravelDoctrine\Passport\Repositories\TokenRepository');
        $tokens->shouldReceive('findValidToken')->with('user', 'client')->andReturnNull();

        $this->assertEquals('view', $controller->authorize(
            Mockery::mock('Psr\Http\Message\ServerRequestInterface'), $request, $clients, $tokens
        ));
    }

    public function test_authorization_exceptions_are_handled()
    {
        $server = Mockery::mock(AuthorizationServer::class);
        $response = Mockery::mock(ResponseFactory::class);

        $controller = new LaravelDoctrine\Passport\Http\Controllers\AuthorizationController($server, $response);

        $server->shouldReceive('validateAuthorizationRequest')->andReturnUsing(function () {
            throw new Exception('whoops');
        });

        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('session')->andReturn($session = Mockery::mock());

        $clients = Mockery::mock('LaravelDoctrine\Passport\Repositories\ClientRepository');

        $tokens = Mockery::mock('LaravelDoctrine\Passport\Repositories\TokenRepository');

        $this->assertEquals('whoops', $controller->authorize(
            Mockery::mock('Psr\Http\Message\ServerRequestInterface'), $request, $clients, $tokens
        )->getOriginalContent());
    }

    /**
     * @group shithead
     */
    public function test_request_is_approved_if_valid_token_exists()
    {
        LaravelDoctrine\Passport\Passport::tokensCan([
            'scope-1' => 'description',
        ]);

        $server = Mockery::mock(AuthorizationServer::class);
        $response = Mockery::mock(ResponseFactory::class);

        $controller = new LaravelDoctrine\Passport\Http\Controllers\AuthorizationController($server, $response);

        $server->shouldReceive('validateAuthorizationRequest')->andReturn($authRequest = Mockery::mock('League\OAuth2\Server\RequestTypes\AuthorizationRequest'));
        $server->shouldReceive('completeAuthorizationRequest')->with($authRequest, Mockery::type('Psr\Http\Message\ResponseInterface'))->andReturn('approved');

        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('user')->once()->andReturn($user = Mockery::mock());
        $user->shouldReceive('getKey')->andReturn(1);
        $request->shouldNotReceive('session');

        $authRequest->shouldReceive('getClient->getIdentifier')->once()->andReturn(1);
        $authRequest->shouldReceive('getScopes')->once()->andReturn([new LaravelDoctrine\Passport\Bridge\Scope('scope-1')]);
        $authRequest->shouldReceive('setUser')->once()->andReturnNull();
        $authRequest->shouldReceive('setAuthorizationApproved')->once()->with(true);

        $clients = Mockery::mock('LaravelDoctrine\Passport\Repositories\ClientRepository');
        $clients->shouldReceive('find')->with(1)->andReturn('client');

        $tokens = Mockery::mock('LaravelDoctrine\Passport\Repositories\TokenRepository');
        $tokens->shouldReceive('findValidToken')->with($user, 'client')->andReturn($token = Mockery::mock('LaravelDoctrine\Passport\Token'));
        $token->shouldReceive('getAttribute')->with('scopes')->andReturn(['scope-1']);

        $this->assertEquals('approved', $controller->authorize(
            Mockery::mock('Psr\Http\Message\ServerRequestInterface'), $request, $clients, $tokens
        ));
    }
}

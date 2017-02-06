<?php

use Illuminate\Container\Container;

class HasApiTokensTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_token_can_indicates_if_token_has_given_scope()
    {
        $user = new HasApiTokensTestStub;
        $token = Mockery::mock();
        $token->shouldReceive('can')->with('scope')->andReturn(true);
        $token->shouldReceive('can')->with('another-scope')->andReturn(false);

        $this->assertTrue($user->withAccessToken($token)->tokenCan('scope'));
        $this->assertFalse($user->withAccessToken($token)->tokenCan('another-scope'));
    }

    public function test_token_can_be_created()
    {
        $container = new Container;
        Container::setInstance($container);
        $container->instance(LaravelDoctrine\Passport\PersonalAccessTokenFactory::class, $factory = Mockery::mock());
        $factory->shouldReceive('make')->once()->with(1, 'name', ['scopes']);
        $user = new HasApiTokensTestStub;

        $user->createToken('name', ['scopes']);
    }
}

class HasApiTokensTestStub implements \LaravelDoctrine\Passport\Contracts\OAuthUser
{
    use LaravelDoctrine\Passport\Traits\HasApiToken;

    public function getId()
    {
        return 1;
    }
}

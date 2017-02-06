<?php

namespace LaravelDoctrine\Passport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use LaravelDoctrine\Passport\Entities\Client;
use LaravelDoctrine\Passport\Contracts\OAuthUser;
use LaravelDoctrine\Passport\Repositories\ClientRepository;

class ClientController
{
    /**
     * The client repository instance.
     *
     * @var ClientRepository
     */
    protected $clients;

    /**
     * The validation factory implementation.
     *
     * @var ValidationFactory
     */
    protected $validation;

    /**
     * Create a client controller instance.
     *
     * @param  ClientRepository  $clients
     * @param  ValidationFactory  $validation
     * @return void
     */
    public function __construct(ClientRepository $clients,
                                ValidationFactory $validation)
    {
        $this->clients = $clients;
        $this->validation = $validation;
    }

    /**
     * Get all of the clients for the authenticated user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function forUser(Request $request)
    {
        $user = $request->user();

        return $this->clients->activeForUser($user)->map->makeVisible('secret');
    }

    /**
     * Store a new client.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => 'required|url',
        ])->validate();

        return $this->clients->create(
            $request->user()->getId(), $request->name, $request->redirect
        )->makeVisible('secret');
    }

    /**
     * Update the given client.
     *
     * @param  Request  $request
     * @param  string  $clientId
     * @return Response
     */
    public function update(Request $request, $clientId)
    {
        /** @var OAuthUser $user */
        $user = $request->user();

        if (! $user->ownsClient($clientId)) {
            return new Response('', 404);
        }

        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => 'required|url',
        ])->validate();

        return $this->clients->update(
            $this->clients->find($clientId),
            $request->name, $request->redirect
        );
    }

    /**
     * Delete the given client.
     *
     * @param  Request  $request
     * @param  string  $clientId
     * @return Response
     */
    public function destroy(Request $request, $clientId)
    {
        /** @var OAuthUser $user */
        $user = $request->user();

        /** @var Client $client */
        $client = $this->clients->find($clientId);

        if (!$client || $user->getId() !== $client->getUser()->getId()) {
            return new Response('', 404);
        }

        $this->clients->delete(
            $client
        );
    }
}

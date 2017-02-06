<?php

namespace LaravelDoctrine\Passport\Bridge;

trait FormatsScopesForStorage
{
    /**
     * Format the given scopes for storage.
     *
     * @param  array  $scopes
     * @return string
     */
    public function formatScopesForStorage(array $scopes, $encode = false)
    {
        $data = array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);

        return $encode ? json_encode($data) : $data;
    }
}

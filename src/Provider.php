<?php

declare(strict_types=1);

namespace Noilty\SocialiteProviders\Wargaming;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    const IDENTIFIER = 'WARGAMING';

    protected $stateless = true;

    protected function getCognitoUrl()
    {
        if (!in_array($domain = $this->request->domain, ['eu', 'com', 'asia'])) {
            throw new InvalidArgumentException();
        }

        return 'https://api.worldoftanks.' . $domain . '/wot';
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getCognitoUrl() . '/auth/login/', $state);
    }

    protected function getTokenUrl()
    {
        return '';
    }

    protected function getCodeFields($state = null)
    {
        return array_merge([
            'application_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'state' => $this->usesState() ? $state : null,
        ], $this->parameters);
    }

    public function getAccessTokenResponse($code)
    {
        return request()->all();
    }

    public function user(): User
    {
        $this->validateState();

        $response = $this->getAccessTokenResponse($this->getCode());
        $token = $this->parseAccessToken($response);
        $user = $this->mapUserToObject($this->getUserByToken($response));

        $this->setTokenInfo($user, $response, $token);

        return $user;
    }

    protected function validateState()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }
    }

    protected function setTokenInfo(User $user, array $response, string $token)
    {
        $this->credentialsResponseBody = $response;

        if ($user instanceof User) {
            $user->setAccessTokenResponseBody($this->credentialsResponseBody);
        }

        $user
            ->setToken($token)
            ->setRefreshToken($this->parseRefreshToken($response))
            ->setExpiresIn($this->parseExpiresIn($response));
    }

    protected function getUserByToken($response)
    {
        return $response;
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['account_id'],
            'nickname' => $user['nickname'],
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    protected function parseExpiresIn($body)
    {
        return Arr::get($body, 'expires_at');
    }
}

<?php
// includes/SocialAuth.php

class SocialAuth
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'google' => [
                'client_id' => getenv('GOOGLE_CLIENT_ID'),
                'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
                'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => 'https://oauth2.googleapis.com/token',
                'user_info_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
                'scope' => 'openid email profile'
            ],
            'github' => [
                'client_id' => getenv('GITHUB_CLIENT_ID'),
                'client_secret' => getenv('GITHUB_CLIENT_SECRET'),
                'auth_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'user_info_url' => 'https://api.github.com/user',
                'scope' => 'user:email'
            ]
        ];
    }

    public function getAuthUrl($provider, $redirect_uri, $state)
    {
        if (!isset($this->config[$provider]))
            return null;

        $p = $this->config[$provider];
        $params = [
            'client_id' => $p['client_id'],
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $p['scope'],
            'state' => $state
        ];

        return $p['auth_url'] . '?' . http_build_query($params);
    }

    public function exchangeCode($provider, $code, $redirect_uri)
    {
        if (!isset($this->config[$provider]))
            return null;

        $p = $this->config[$provider];
        $params = [
            'client_id' => $p['client_id'],
            'client_secret' => $p['client_secret'],
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];

        $ch = curl_init($p['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getUserInfo($provider, $access_token)
    {
        if (!isset($this->config[$provider]))
            return null;

        $p = $this->config[$provider];
        $headers = [
            'Authorization: Bearer ' . $access_token,
            'User-Agent: The Coding Science Auth'
        ];

        $ch = curl_init($p['user_info_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // Standardize output
        if ($provider === 'google') {
            return [
                'id' => $data['id'] ?? $data['sub'],
                'name' => $data['name'],
                'email' => $data['email'],
                'avatar' => $data['picture']
            ];
        }

        if ($provider === 'github') {
            // GitHub doesn't always return email in the main user object if it's private
            $email = $data['email'] ?? null;
            if (!$email) {
                // Secondary call to fetch emails if needed (keeping it simple for now)
            }
            return [
                'id' => $data['id'],
                'name' => $data['name'] ?? $data['login'],
                'email' => $email,
                'avatar' => $data['avatar_url']
            ];
        }

        return $data;
    }
}

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
            ],
            'linkedin' => [
                'client_id' => getenv('LINKEDIN_CLIENT_ID'),
                'client_secret' => getenv('LINKEDIN_CLIENT_SECRET'),
                'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
                'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
                'user_info_url' => 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
                'scope' => 'r_liteprofile r_emailaddress'
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

        if ($provider === 'github') {
            $headers[] = 'Accept: application/vnd.github.v3+json';
        }

        $ch = curl_init($p['user_info_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // Standardize output
        if ($provider === 'google') {
            return [
                'id' => $data['sub'],
                'name' => $data['name'],
                'email' => $data['email'],
                'avatar' => $data['picture']
            ];
        } elseif ($provider === 'github') {
            // Need to fetch email separately for GitHub if not public
            if (empty($data['email'])) {
                $data['email'] = $this->getGitHubEmail($access_token);
            }
            return [
                'id' => $data['id'],
                'name' => $data['name'] ?? $data['login'],
                'email' => $data['email'],
                'avatar' => $data['avatar_url']
            ];
        }

        return $data;
    }

    private function getGitHubEmail($token)
    {
        $ch = curl_init('https://api.github.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'User-Agent: The Coding Science Auth',
            'Accept: application/vnd.github.v3+json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $emails = json_decode($response, true);

        if (is_array($emails)) {
            foreach ($emails as $email) {
                if ($email['primary'])
                    return $email['email'];
            }
        }
        return null;
    }
}

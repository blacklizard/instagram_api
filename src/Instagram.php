<?php

namespace Jeevan;

use Jeevan\Exception\InstagramException;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

//TODO: implement rest of the endpoints

class Instagram
{
    /**
     * base url
     */
    const URL = 'https://api.instagram.com/v1/';

    /**
     * oauth url
     */
    const URL_OAUTH = 'https://api.instagram.com/oauth/authorize/?';

    /**
     * access_token url
     */
    const URL_ACCESS_TOKEN = 'https://api.instagram.com/oauth/access_token';

    /**
     * instagram app client id
     */
    private $client_id = null;

    /**
     * instagram app client secret
     */
    private $client_secret = null;

    /**
     * url where instagram after user authentication
     */
    private $redirect_uri = null;

    /**
     * The access_token
     */
    private $access_token = null;

    /**
     * Currently authed user
     */
    private $user = null;

    /**
     * Instagram constructor.
     *
     * @param array $config
     * @param array $scope
     *
     * @throws InstagramException
     */
    public function __construct($config = [], $scope = [])
    {

        // make sure all required data to make api call exist
        $config_keys = ['CLIENT_ID', 'CLIENT_SECRET', 'REDIRECT_URI'];
        $missing_keys = array_diff_key(array_flip($config_keys), $config);

        if ($missing_keys) {
            $message = '';
            foreach ($missing_keys as $key) {
                $message .= $config_keys[$key] . ',';
            }
            throw new InstagramException(rtrim($message, ',') . ' is required');
        }

        $this->client_id = $config['CLIENT_ID'];
        $this->client_secret = $config['CLIENT_SECRET'];
        $this->redirect_uri = $config['REDIRECT_URI'];

        $this->scope = $scope;
    }

    /**
     * Get authorization URL
     * User will click on this link to authorized our app so that we can use the returned result
     *
     * @ref https://www.instagram.com/developer/authentication/
     */
    public function getAuthorizationURL()
    {

        $query = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code'
        ];

        if (count($this->scope)) {
            $query['scope'] = implode('+', $this->scope);
        }

        return self::URL_OAUTH . http_build_query($query);
    }

    /**
     * Get access token, this function should be called
     * once Instagram responses after user authorization
     */
    public function processAccessToken()
    {
        $request = Request::createFromGlobals();

        if ($request->get('error')) {
            throw new InstagramException($request->get('error_description'));
        }

        $code = $request->get('code');

        $client = new Client();

        $request = $client->request('POST', self::URL_ACCESS_TOKEN, [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirect_uri,
                'code' => $code
            ],
        ]);

        $result = json_decode($request->getBody()->getContents(), true);

        $this->access_token = $result['access_token'];
        $this->user = $result['user'];

        return $result;
    }

    /**
     * Get current user's details
     *
     * @return mixed
     *
     * @throws InstagramException
     */
    public function getSelf()
    {
        return $this->callAPI('users/self');
    }

    /**
     * Make the call to Instagram endpoint
     *
     * @param string $endPoint
     * @param array $parameters
     *
     * @return mixed
     *
     * @throws InstagramException
     */
    private function callAPI($endPoint = null, $parameters = [])
    {

        if (!$endPoint) {
            throw new InstagramException('Endpoint cant be empty');
        }

        $client = new Client();

        $param = [
            'access_token' => $this->access_token
        ];

        if (count($parameters)) {
            $param = array_merge($param, $parameters);
        }

        $request = $client->request('GET', self::URL . $endPoint, [
            'query' => $param
        ]);

        return json_decode($request->getBody()->getContents(), true);
    }
}
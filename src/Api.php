<?php
namespace MicrosoftGraphApiPhp;

use GuzzleHttp\Exception\ClientException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User;

class Api
{
    protected $url_login = "https://login.microsoftonline.com/";
    protected $url_graph = "https://graph.microsoft.com/";
    protected $url_token;
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;
    protected $tenant_id;
    protected $scopes;
    protected $state;
    protected $guzzle;
    protected $graph;

    public function __construct($client_id, $client_secret, $redirect_uri, $tenant_id = "common", $scopes = array("User.Read"), $state = null)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = $redirect_uri;
        $this->tenant_id     = $tenant_id;
        $this->scopes        = $scopes;

        if ($state == null) {
            $state = 42443;
        }
        $this->state = $state;

        $this->guzzle    = new \GuzzleHttp\Client();
        $this->url_token = $this->url_login . $this->tenant_id . "/oauth2/v2.0/token";

        $this->graph = new Graph();
    }

    public function get_url_auth()
    {
        $parameters = array(
            "client_id"     => $this->client_id,
            "response_type" => "code",
            "redirect_uri"  => $this->redirect_uri,
            "response_mode" => "query",
            "scope"         => implode(" ", $this->scopes),
            "state"         => $this->state,
        );
        return $this->url_login . $this->tenant_id . "/oauth2/v2.0/authorize?" . http_build_query($parameters);
    }

    public function get_token($code)
    {
        $request = $this->guzzle->post($this->url_token, array(
            "form_params" => array(
                "client_id"     => $this->client_id,
                "client_secret" => $this->client_secret,
                "redirect_uri"  => $this->redirect_uri,
                "scope"         => implode(" ", $this->scopes),
                "grant_type"    => "authorization_code",
                "code"          => $code,
            ),
        ))->getBody()->getContents();

        $token = json_decode($request);
        $this->graph->setAccessToken($token);

        return $token;
    }

    public function get_data($code)
    {
        $this->get_token($code);

        try {
            $data = $this->graph()->createRequest("get", "/me")->setReturnType(User::class)->execute();
        } catch (ClientException $e) {
            var_dump($e);
            return false;
        }

        return $data;
    }
}

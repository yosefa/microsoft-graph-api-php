<?php
namespace MicrosoftGraphApiPhp;

use GuzzleHttp\Exception\ClientException;
use Microsoft\Graph\Exception\GraphException;
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

    public function __construct($client_id, $client_secret, $redirect_uri, $tenant_id = "common", $scopes = array("User.Read"), $state = 42443)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = $redirect_uri;
        $this->tenant_id     = $tenant_id;
        $this->scopes        = $scopes;
        $this->state         = $state;

        $this->url_token = $this->url_login . $this->tenant_id . "/oauth2/v2.0/token";

        $this->guzzle = new \GuzzleHttp\Client();
        $this->graph  = new Graph();
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

    public function get_token($code = null)
    {
        try {
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

            $result        = json_decode($request);
            $result->valid = true;

            return $result;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());
            $result   = array(
                'valid'   => false,
                'message' => isset($response->error_description) ? $response->error_description : 'Error retrieving token.',
            );

            return (object) $result;
        } catch (Exception $e) {
            print_r($e);
            return false;
        }
    }

    public function get_data($code)
    {
        $token = $this->get_token($code);
        $data  = $token;
        if ($token->valid == true) {
            try {
                $this->graph->setAccessToken($token->access_token);
                $data        = $this->graph->createRequest("get", "/me")->setReturnType(User::class)->execute();
                $data->valid = true;
            } catch (ClientException $e) {
                print_r($e);
                return false;
            } catch (GraphException $e) {
                print_r($e);
                return false;
            } catch (Exception $e) {
                print_r($e);
                return false;
            }
        }

        return $data;
    }
}

<?php


namespace App\Tests\Utils;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

trait ApiTestTrait
{

    /** @var Client */
    protected $client;

    /**
     * @return array
     */
    protected function getUserData()
    {
        // Get first event
        $response = $this->authenticatedRequest('GET', '/api/users?username=jquinson');
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @return array
     */
    protected function getAdminUserData()
    {
        // Get first event
        $response = $this->authenticatedRequest('GET', '/api/users?username=admin');
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|array|null $content
     * @param array $headers
     * @param bool $authenticated
     * @param array $credentials
     * @return Response
     */
    protected function request(string $method, string $uri, $content = null, array $headers = [], $authenticated = false, array $credentials = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;
                continue;
            }

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        // Authenticate
        if ($authenticated) {
            $authResponse = $this->authUser($credentials);
            $authContent = $authResponse->getContent();
            $authUser = json_decode($authContent, true);
            $token = $authUser['token'];
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }

    /**
     * @param array $credentials
     * @return mixed
     */
    protected function authUser(array $credentials = [])
    {
        $username = $credentials[0] ?? 'jquinson';
        $password = $credentials[1] ?? 'test';
        $content = json_encode(['username' => $username, 'password' => $password]);
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        $this->client->request('POST', '/login_check', [], [], $server, $content);
        return $this->client->getResponse();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|array|null $content
     * @param array $headers
     * @param array $credentials
     * @return Response
     */
    protected function authenticatedRequest(string $method, string $uri, $content = null, array $headers = [], array $credentials = []): Response
    {
        return $this->request($method, $uri, $content, $headers, true, $credentials);
    }

}

<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends WebTestCase
{

    /** @var Client */
    protected $client;

    /**
     * Retrieves the book list.
     */
    public function testRetrieveEventList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/events');
        // @todo: tests
    }


    /**
     * @param string $method
     * @param string $uri
     * @param string|array|null $content
     * @param array $headers
     * @return Response
     */
    protected function authenticatedRequest(string $method, string $uri, $content = null, array $headers = []): Response
    {
        return $this->request($method, $uri, $content, $headers, true);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|array|null $content
     * @param array $headers
     * @param bool $authenticated
     * @return Response
     */
    protected function request(string $method, string $uri, $content = null, array $headers = [], $authenticated = false): Response
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
            $authUser = $this->getAuthUser();
            $token = $authUser['token'];
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request($method, $uri, ['username' => 'jquinson', 'password' => 'test'], [], $server, $content);

        return $this->client->getResponse();
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     */
    protected function getAuthUser($username = 'jquinson', $password = 'testtest')
    {
        $content = json_encode(['username' => $username, 'password' => $password]);
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        $this->client->request('POST', '/login_check', [], [], $server, $content);
        $content = $this->client->getResponse()->getContent();
        return json_decode($content, true);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }


}

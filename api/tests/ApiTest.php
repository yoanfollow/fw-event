<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiTest extends WebTestCase
{

    /** @var Client */
    protected $client;

    /**
     * Test Event
     */
    public function testRetrieveEventList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/events');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(500, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(500, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('name', $member);
        $this->assertArrayHasKey('description', $member);
        $this->assertArrayHasKey('organizer', $member);
        $this->assertArrayHasKey('startAt', $member);
        $this->assertArrayHasKey('endAt', $member);
        $this->assertArrayHasKey('participants', $member);
        $this->assertArrayHasKey('place', $member);
        $this->assertArrayHasKey('comments', $member);
        $this->assertArrayHasKey('createdAt', $member);

        // Shouldn't embed nor other private fields
        $this->assertArrayNotHasKey('updatedAt', $member);
        $this->assertArrayNotHasKey('deletedAt', $member);

        // Organizer must be embedded
        $this->assertInternalType('array', $member['organizer']);
        $this->assertArrayHasKey('@id', $member['organizer']);
        $this->assertArrayHasKey('username', $member['organizer']);

        // recipient embedded
        $this->assertInternalType('array', $member['participants']);

        $this->assertInternalType('array', $member['comments']);

    }


    /**
     * Test Event
     */
    public function testRetrieveEventItem(): void
    {

    }



    /**
     * Test Invitation
     */
    public function testRetrieveInvitationList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/invitations');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(5070, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(30, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('event', $member);
        $this->assertArrayHasKey('recipient', $member);
        $this->assertArrayHasKey('confirmed', $member);
        $this->assertArrayHasKey('expireAt', $member);
        $this->assertArrayHasKey('createdAt', $member);
        $this->assertArrayHasKey('expired', $member);

        // Shouldn't embed event list nor other private fields
        $this->assertArrayNotHasKey('events', $member);
        $this->assertArrayNotHasKey('updatedAt', $member);
        $this->assertArrayNotHasKey('deletedAt', $member);

        // Event must be embedded
        $this->assertInternalType('array', $member['event']);
        $this->assertArrayHasKey('@id', $member['event']);
        $this->assertArrayHasKey('place', $member['event']);
        $this->assertInternalType('string', $member['event']['place']); // No event Place embedded
        $this->assertArrayNotHasKey('invitations', $member['event']); // No event invitations embedded

        // recipient embedded
        $this->assertInternalType('array', $member['recipient']);
        $this->assertArrayHasKey('@id', $member['recipient']);
        $this->assertArrayHasKey('id', $member['recipient']);
        $this->assertArrayHasKey('email', $member['recipient']);
        $this->assertArrayNotHasKey('password', $member['recipient']); // No password

        // Filter by event id
        $response = $this->authenticatedRequest('GET', '/api/invitations?event=%2Fapi%2Fevents%2F'.$member['event']['id']); // search for "amazing place 9"
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertGreaterThan(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertGreaterThan(0, $json['hydra:member']);
    }


    /**
     * Test Invitation
     */
    public function testRetrieveInvitationItem(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/invitation');
        $json = json_decode($response->getContent(), true);

        $data = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $data['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('event', $member);
        $this->assertArrayHasKey('recipient', $member);
        $this->assertArrayHasKey('confirmed', $member);
        $this->assertArrayHasKey('expireAt', $member);
        $this->assertArrayHasKey('createdAt', $member);
        $this->assertArrayHasKey('expired', $member);

        // Shouldn't embed event list nor other private fields
        $this->assertArrayNotHasKey('events', $member);
        $this->assertArrayNotHasKey('updatedAt', $member);
        $this->assertArrayNotHasKey('deletedAt', $member);

        // Event must be embedded
        $this->assertInternalType('array', $member['event']);
        $this->assertArrayHasKey('@id', $member['event']);
        $this->assertArrayHasKey('place', $member['event']);
        $this->assertInternalType('string', $member['event']['place']); // No event Place embedded
        $this->assertArrayNotHasKey('invitations', $member['event']); // No event invitations embedded

        // recipient embedded
        $this->assertInternalType('array', $member['recipient']);
        $this->assertArrayHasKey('@id', $member['recipient']);
        $this->assertArrayHasKey('id', $member['recipient']);
        $this->assertArrayHasKey('email', $member['recipient']);
        $this->assertArrayNotHasKey('password', $member['recipient']); // No password

        // Check not found user
        $response = $this->authenticatedRequest('GET', '/api/users/999999999');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test place
     */
    public function testRetrievePlaceList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/places');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(20, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(20, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('name', $member);
        $this->assertArrayHasKey('streetNumber', $member);
        $this->assertArrayHasKey('city', $member);
        $this->assertArrayHasKey('streetName', $member);
        $this->assertArrayHasKey('postalCode', $member);
        $this->assertArrayHasKey('country', $member);
        $this->assertArrayHasKey('createdAt', $member);

        // Shouldn't embed event list nor other private fields
        $this->assertArrayNotHasKey('events', $member);
        $this->assertArrayNotHasKey('updatedAt', $member);
        $this->assertArrayNotHasKey('deletedAt', $member);

        // Filter
        $response = $this->authenticatedRequest('GET', '/api/places?name=place%209'); // search for "amazing place 9"
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(1, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(1, $json['hydra:member']);
    }


    /**
     * Test place
     */
    public function testRetrievePlaceItem(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/places');
        $json = json_decode($response->getContent(), true);

        $userData = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $userData['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // Test item's structure
        $this->assertArrayHasKey('@id', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('streetNumber', $json);
        $this->assertArrayHasKey('city', $json);
        $this->assertArrayHasKey('streetName', $json);
        $this->assertArrayHasKey('postalCode', $json);
        $this->assertArrayHasKey('country', $json);
        $this->assertArrayHasKey('createdAt', $json);

        // Shouldn't embed event list nor other private fields
        $this->assertArrayNotHasKey('events', $json);
        $this->assertArrayNotHasKey('updatedAt', $json);
        $this->assertArrayNotHasKey('deletedAt', $json);

        // Check not found user
        $response = $this->authenticatedRequest('GET', '/api/users/999999999');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Retrieves users
     */
    public function testRetrieveUserList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/users');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(19, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(19, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('email', $member);
        $this->assertArrayHasKey('roles', $member);
        $this->assertInternalType('array', $member['roles']);
        $this->assertArrayHasKey('username', $member);
        $this->assertArrayHasKey('avatar', $member);
        $this->assertArrayHasKey('avatarUrl', $member);
        $this->assertArrayHasKey('createdAt', $member);

        // Shouldn't embed children list nor other private fields
        $this->assertArrayNotHasKey('invitations', $member);
        $this->assertArrayNotHasKey('comments', $member);
        $this->assertArrayNotHasKey('updatedAt', $member);
        $this->assertArrayNotHasKey('deletedAt', $member);


        // Filter
        $response = $this->authenticatedRequest('GET', '/api/users?username=real');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(7, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(7, $json['hydra:member']);

        // Ensure that every "username" contains the filtered value
        foreach ($json['hydra:member'] as $item) {
            $this->assertGreaterThan(0, substr_count($item['username'], 'real'));
        }


        // Filter with username which don't exists
        $response = $this->authenticatedRequest('GET', '/api/users?username=usernamenotfound');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(0, $json['hydra:member']);
    }


    /**
     * Retrieves users
     */
    public function testRetrieveUserItem(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/users?username=admin');
        $json = json_decode($response->getContent(), true);
        $userData = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $userData['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // Test item's structure
        $this->assertArrayHasKey('@id', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('email', $json);
        $this->assertArrayHasKey('roles', $json);
        $this->assertInternalType('array', $json['roles']);
        $this->assertArrayHasKey('username', $json);
        $this->assertArrayHasKey('avatar', $json);
        $this->assertArrayHasKey('avatarUrl', $json);
        $this->assertArrayHasKey('createdAt', $json);

        // Shouldn't embed children list nor other private fields
        $this->assertArrayNotHasKey('invitations', $json);
        $this->assertArrayNotHasKey('comments', $json);
        $this->assertArrayNotHasKey('updatedAt', $json);
        $this->assertArrayNotHasKey('deletedAt', $json);

        // Check not found user
        $response = $this->authenticatedRequest('GET', '/api/users/999999999');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Retrieves users
     */
    public function testRetrieveUserInvitationList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/users?username=jack.thedog.real');
        $json = json_decode($response->getContent(), true);
        $userData = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', '/api/users/'.$userData['id'].'/invitations');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(10, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(10, $json['hydra:member']);

        $member = $json['hydra:member'][0];

        // Test item's structure
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('confirmed', $member);
        $this->assertArrayHasKey('expireAt', $member);
        $this->assertArrayHasKey('createdAt', $member);
        $this->assertArrayHasKey('event', $member);

        // Event must be embedded
        $this->assertInternalType('array', $member['event']);
        $this->assertArrayHasKey('@id', $member['event']);
        $this->assertArrayHasKey('name', $member['event']);

        // Place must be the @id
        $this->assertArrayHasKey('place', $member['event']);
        $this->assertInternalType('string', $member['event']['place']);

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
            $response = $this->authUser();
            $content = $response->getContent();
            $authUser = json_decode($content, true);
            $token = $authUser['token'];
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     */
    protected function authUser($username = 'jquinson', $password = 'test')
    {
        $content = json_encode(['username' => $username, 'password' => $password]);
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        $this->client->request('POST', '/login_check', [], [], $server, $content);
        return $this->client->getResponse();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }


}

<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Utils\ApiTestTrait;

class ApiTest extends WebTestCase
{

    use ApiTestTrait;

    /**
     * Test Comment
     */
    public function testRetrieveCommentList(): void
    {
        // Get first event
        $response = $this->authenticatedRequest('GET', '/api/events?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);
        $firstEvent = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', '/api/events/'.$firstEvent['id'].'/comments');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertGreaterThan(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertGreaterThan(0, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('author', $member);
        $this->assertArrayHasKey('content', $member);
        $this->assertArrayHasKey('rate', $member);
        $this->assertArrayHasKey('createdAt', $member);

        // Author mus be embedded
        $this->assertInternalType('array', $member['author']);
        $this->assertArrayHasKey('@id', $member['author']);
        $this->assertArrayHasKey('id', $member['author']);
        $this->assertArrayHasKey('username', $member['author']);
        $this->assertArrayHasKey('email', $member['author']);
        $this->assertArrayHasKey('avatarUrl', $member['author']);
        $this->assertArrayNotHasKey('invitations', $member['author']);
        $this->assertArrayNotHasKey('comments', $member['author']);
    }


    /**
     * Test Comment
     */
    public function testRetrieveCommentItem(): void
    {
        // Get first event
        $response = $this->authenticatedRequest('GET', '/api/events?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);
        $firstEvent = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', '/api/events/'.$firstEvent['id'].'/comments?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);
        $data = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $data['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));


        // Test item's structure
        $this->assertArrayHasKey('@id', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('author', $json);
        $this->assertArrayHasKey('content', $json);
        $this->assertArrayHasKey('rate', $json);
        $this->assertArrayHasKey('createdAt', $json);

        // Author mus be embedded
        $this->assertInternalType('array', $json['author']);
        $this->assertArrayHasKey('@id', $json['author']);
        $this->assertArrayHasKey('id', $json['author']);
        $this->assertArrayHasKey('username', $json['author']);
        $this->assertArrayHasKey('email', $json['author']);
        $this->assertArrayHasKey('avatarUrl', $json['author']);
        $this->assertArrayNotHasKey('invitations', $json['author']);
        $this->assertArrayNotHasKey('comments', $json['author']);
    }

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
        $this->assertCount(30, $json['hydra:member']);

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

        // Participants and comment list must be array of @id
        $this->assertInternalType('array', $member['participants']);
        $this->assertInternalType('array', $member['comments']);

        foreach ($member['participants'] as $participant) {
            $this->assertInternalType('string', $participant);
        }

        foreach ($member['comments'] as $comment) {
            $this->assertInternalType('string', $comment);
        }

        // Filter by organizer
        $response = $this->authenticatedRequest('GET', '/api/events?organizer='.urlencode($member['organizer']['@id']));
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertGreaterThan(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertGreaterThan(0, $json['hydra:member']);

        foreach ($json["hydra:member"] as $item) {
            $this->assertEquals($member['organizer']['@id'], $item['organizer']['@id']);
        }

        // get passed events
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d H:i:s');

        $response = $this->authenticatedRequest('GET', '/api/events?endAt%5Bstrictly_before%5D='.urlencode($todayStr));

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // check that all events ended
        foreach ($json["hydra:member"] as $item) {
            $endAt = new \DateTime($item['endAt']);
            $this->assertLessThanOrEqual($today->getTimestamp(), $endAt->getTimestamp());
        }

        // Get future events
        $response = $this->authenticatedRequest('GET', '/api/events?startAt%5Bstrictly_after%5D='.urlencode($todayStr));
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // check that all events ended
        foreach ($json["hydra:member"] as $item) {
            $endAt = new \DateTime($item['startAt']);
            $this->assertGreaterThanOrEqual($today->getTimestamp(), $endAt->getTimestamp());
        }
    }

    /**
     * Test Event
     */
    public function testRetrieveEventItem(): void
    {
        // get one element
        $response = $this->authenticatedRequest('GET', '/api/events?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);

        $data = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $data['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // Test item's structure
        $this->assertArrayHasKey('@id', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertArrayHasKey('organizer', $json);
        $this->assertArrayHasKey('startAt', $json);
        $this->assertArrayHasKey('endAt', $json);
        $this->assertArrayHasKey('participants', $json);
        $this->assertArrayHasKey('place', $json);
        $this->assertArrayHasKey('comments', $json);
        $this->assertArrayHasKey('createdAt', $json);

        // Shouldn't embed nor other private fields
        $this->assertArrayNotHasKey('updatedAt', $json);
        $this->assertArrayNotHasKey('deletedAt', $json);

        // Organizer must be embedded
        $this->assertInternalType('array', $json['organizer']);
        $this->assertArrayHasKey('@id', $json['organizer']);
        $this->assertArrayHasKey('username', $json['organizer']);

        // Participants and comment list must be array of @id
        $this->assertInternalType('array', $json['participants']);
        $this->assertInternalType('array', $json['comments']);

        foreach ($json['participants'] as $participant) {
            $this->assertInternalType('string', $participant);
        }

        foreach ($json['comments'] as $comment) {
            $this->assertInternalType('string', $comment);
        }
    }


    /**
     * Test Event Participant list
     */
    public function testRetrieveEventParticipantList(): void
    {
        $response = $this->authenticatedRequest('GET', '/api/events?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);

        $data = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', '/api/events/'.$data['id'].'/participants');
        $json = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertGreaterThan(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertGreaterThan(0, $json['hydra:member']);

        // Test item's structure
        $member = $json['hydra:member'][0];
        $this->assertArrayHasKey('@id', $member);
        $this->assertArrayHasKey('id', $member);
        $this->assertArrayHasKey('recipient', $member);
        $this->assertArrayHasKey('confirmed', $member);
        $this->assertArrayHasKey('expireAt', $member);
        $this->assertArrayHasKey('createdAt', $member);
        $this->assertArrayHasKey('expired', $member);
        $this->assertInternalType('array', $member['recipient']);
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
        $this->assertGreaterThan(30, $json['hydra:totalItems']);

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
        $this->assertArrayHasKey('organizer', $member['event']);
        $this->assertInternalType('array', $member['event']['organizer']); // Organizer Place embedded
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
        $response = $this->authenticatedRequest('GET', '/api/invitations?itemsPerPage=1');
        $json = json_decode($response->getContent(), true);

        $data = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('GET', $data['@id']);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        // Test item's structure
        $this->assertArrayHasKey('@id', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('event', $json);
        $this->assertArrayHasKey('recipient', $json);
        $this->assertArrayHasKey('confirmed', $json);
        $this->assertArrayHasKey('expireAt', $json);
        $this->assertArrayHasKey('createdAt', $json);
        $this->assertArrayHasKey('expired', $json);

        // Shouldn't embed event list nor other private fields
        $this->assertArrayNotHasKey('events', $json);
        $this->assertArrayNotHasKey('updatedAt', $json);
        $this->assertArrayNotHasKey('deletedAt', $json);

        // Event must be embedded
        $this->assertInternalType('array', $json['event']);
        $this->assertArrayHasKey('@id', $json['event']);
        $this->assertArrayHasKey('place', $json['event']);
        $this->assertInternalType('string', $json['event']['place']); // No event Place embedded
        $this->assertArrayHasKey('organizer', $json['event']);
        $this->assertInternalType('array', $json['event']['organizer']); // Organizer Place embedded
        $this->assertArrayNotHasKey('invitations', $json['event']); // No event invitations embedded

        // recipient embedded
        $this->assertInternalType('array', $json['recipient']);
        $this->assertArrayHasKey('@id', $json['recipient']);
        $this->assertArrayHasKey('id', $json['recipient']);
        $this->assertArrayHasKey('email', $json['recipient']);
        $this->assertArrayNotHasKey('password', $json['recipient']); // No password


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
        $response = $this->authenticatedRequest('GET', '/api/places?itemsPerPage=1');
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
        $this->assertGreaterThan(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);

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
        $this->assertEquals(5, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(5, $json['hydra:member']);

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

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }


}

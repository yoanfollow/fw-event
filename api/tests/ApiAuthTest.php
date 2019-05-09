<?php


namespace App\Tests;

use App\Tests\Utils\ApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test authentication, registration and access_control
 */
class ApiAuthTest extends WebTestCase
{

    use ApiTestTrait;

    /**
     * @todo
     * Comment => PUT/DELETE only author or admin
     * Event => PUT/DELETE only author or admin
     * Invitation => PUT/DELETE only author or admin. Confirm => Only invited or admin
     * User => PUT/DELETE only author or admin
     */


    public function testLoginCheck()
    {
        $response = $this->request('POST', '/login_check', [
            'username' => 'jquinson',
            'password' => 'test',
        ]);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('token', $json);
        $this->assertArrayHasKey('user', $json);
        $this->assertInternalType('array', $json['user']);
        $this->assertArrayHasKey('@id', $json['user']);
    }

    public function testLoginCheckError()
    {
        $response = $this->request('POST', '/login_check', [
            'username' => 'jquinson',
            'password' => 'badcredentials',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAclComment()
    {
        $comment = $this->getFirstComment();

        // Forbidden (not organizer)
        $response = $this->authenticatedRequest('PUT', $comment['@id'], [
            'name' => $comment['content'],
        ], [], ['jquinson']);
        $this->assertEquals(403, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', $comment['@id'], [
            'name' => $comment['content'],
        ], [], [$comment['author']['username']]);
        $this->assertEquals(200, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', $comment['@id'], [
            'name' => $comment['content'],
        ], [], ['admin']);
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testAclInvitationPost()
    {
        $event = $this->getFirstEvent();

        // Create user
        $response = $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+'.time().'@gmail.com',
            'username' => 'jeremie.quinson+'.time(),
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        $user = json_decode($response->getContent(), true);

        // Can't invite
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'recipient' => '/api/users/'.$user['id'],
            'event' => $event['@id'],
        ], [
            'content-type' => 'application/json',
        ], ['jquinson']);
        $this->assertEquals(403, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'recipient' => '/api/users/'.$user['id'],
            'event' => $event['@id'],
        ], [
            'content-type' => 'application/json',
        ], [$event['organizer']['username']]);
        $this->assertEquals(201, $response->getStatusCode());

        // Ok admin
        // Create new user
        $response = $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+'.time().'@gmail.com',
            'username' => 'jeremie.quinson+'.time(),
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        $user = json_decode($response->getContent(), true);

        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'recipient' => '/api/users/'.$user['id'],
            'event' => $event['@id'],
        ], [
            'content-type' => 'application/json',
        ], ['admin']);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testAclInvitationConfirm()
    {
        $invitation = $this->getFirstInvitation();

        // Forbidden (not organizer)
        $response = $this->authenticatedRequest('PUT', '/api/invitations/'.$invitation['id'].'/confirm', [
            'confirmed' => true,
        ], [
            'content-type' => 'application/json',
        ], ['jquinson']);
        $this->assertEquals(403, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', '/api/invitations/'.$invitation['id'].'/confirm', [
            'confirmed' => true,
        ], [
            'content-type' => 'application/json',
        ], [$invitation['recipient']['username']]);
        $this->assertEquals(200, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', '/api/invitations/'.$invitation['id'].'/confirm', [
            'confirmed' => true,
        ], [
            'content-type' => 'application/json',
        ], ['admin']);
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testAclUser()
    {
        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+testaclupdate@gmail.com',
            'username' => 'jeremie.quinson+updatetest',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Forbidden
        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'email' => $userJson['email'],
        ], [], ['jquinson']);

        $this->assertEquals(403, $response->getStatusCode());

        // Ok
        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'email' => $userJson['email'],
        ], [], [$userJson['username'], 'testtest']);

        $this->assertEquals(200, $response->getStatusCode());

        // Ok
        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'email' => $userJson['email'],
        ], [], ['admin']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAclEvent()
    {
        $event = $this->getFirstEvent();

        // Forbidden (not organizer)
        $response = $this->authenticatedRequest('PUT', $event['@id'], [
            'name' => $event['name'],
        ], [], ['jquinson']);
        $this->assertEquals(403, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', $event['@id'], [
            'name' => $event['name'],
        ], [], [$event['organizer']['username']]);
        $this->assertEquals(200, $response->getStatusCode());

        // Ok
        $response = $this->authenticatedRequest('PUT', $event['@id'], [
            'name' => $event['name'],
        ], [], ['admin']);
        $this->assertEquals(200, $response->getStatusCode());
    }



}

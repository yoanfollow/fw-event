<?php


namespace App\Tests;

use App\Tests\Utils\ApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiWriteTest extends WebTestCase
{

    use ApiTestTrait;



    public function testPostEvent(): void
    {
        // Get place with name "Amazing place 20"
        $placeJson = $this->getOnePlace(urlencode('Amazing place 20'));
        [$startAt, $endAt] = $this->getEventDate();

        $response = $this->authenticatedRequest('POST', '/api/events', [
            "name" => "TEDx \"Doit-on bannir le mot SWAG ?\"",
            "description" => "Talk à propos des effets néfastes lié à la surutilisation du mot SWAG",
            "startAt" => $startAt,
            "endAt" => $endAt,
            'place' => $placeJson['@id'],
        ], [], ['jquinson']);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostEventAndPlace(): void
    {
        [$startAt, $endAt] = $this->getEventDate();

        $response = $this->authenticatedRequest('POST', '/api/events', [
            'name' => "TEDx \"Doit-on supprimer les frites à la cantine ?\"",
            'description' => "Des frites, Des frites, Des frites",
            'startAt' => $startAt,
            'endAt' => $endAt,
            'place' => [
                'name' => 'New Amazing place '.time(),
                'streetNumber' => "12",
                'city' => 'Paris',
                'streetName' => 'Rue de la paix',
                'postalCode' => '75000',
                'country' => 'FR',
            ],
        ], [], ['jquinson']);


        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostEventErrors(): void
    {
        // Get place with name "Amazing place 20"
        $placeJson = $this->getOnePlace(urlencode('Amazing place 20'));
        [$startAt, $endAt] = $this->getEventDate();

        // Invalid dates (end before start)
        $response = $this->authenticatedRequest('POST', '/api/events', [
            'name' => "TEDx \"Doit-on faire des TEDx sur des TEDx ?\"",
            'description' => "Doit-on mettre une description ?",
            'startAt' => '2019-04-11 00:00:00',
            'endAt' => '2019-02-11 00:00:00',
            'place' => $placeJson['@id'],
        ], [], ['jquinson']);

        $errorJson = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $errorJson['@type']);

        // Test create with duplicated place
        $response = $this->authenticatedRequest('POST', '/api/events', [
            'name' => "TEDx \"Doit-on supprimer les frites à la cantine ?\"",
            'description' => "Des frites, Des frites, Des frites",
            'startAt' => $startAt,
            'endAt' => $endAt,
            'place' => [
                'name' => 'Amazing place 1',
                'streetNumber' => "12",
                'city' => 'Paris',
                'streetName' => 'Rue de la paix',
                'postalCode' => '75000',
                'country' => 'FR',
            ],
        ], [], ['jquinson']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $errorJson['@type']);
    }


    public function testPutEvent(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $response = $this->authenticatedRequest('PUT', $event['@id'], [
            "name" => "New name for swag event",
        ], [], ['jquinson']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutEventWithNewPlace(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $response = $this->authenticatedRequest('PUT', $event['@id'], [
            "place" => [
                'name' => 'New Amazing place PUT '.time(),
                'streetNumber' => "12",
                'city' => 'Paris',
                'streetName' => 'Rue de la paix',
                'postalCode' => '75000',
                'country' => 'FR',
            ],
        ], [], ['jquinson']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteEvent(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $response = $this->authenticatedRequest('DELETE', $event['@id'], [], [], ['jquinson']);

        $this->assertEquals(204, $response->getStatusCode());
    }


    public function testPostInvitation(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $users = $this->getUsers('jquinson');
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            "event" => $event['@id'],
            "recipient" => $users[0]['@id'],
        ], [], ['jquinson']);

        $this->assertEquals(201, $response->getStatusCode());

        // Using expiration date
        $expireAt = new \DateTime();
        $expireAt->add(New \DateInterval('P2D'));
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $users[1]['@id'],
            'expireAt' => $expireAt->format('Y-m-d H:i:s'),
        ], [], ['jquinson']);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostInvitationError(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $users = $this->getUsers('jquinson');
        $author = $this->getOneUser('jquinson');

        // Trying to invite author
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            "event" => $event['@id'],
            "recipient" => $author['@id'],
        ], [], ['jquinson']);

        $errorJson = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $errorJson['@type']);

        // Using bad expiration date (< today)
        $expireAt = new \DateTime();
        $expireAt->sub(New \DateInterval('P2D'));
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $users[2]['@id'],
            'expireAt' => $expireAt->format('Y-m-d H:i:s'),
        ], [], ['jquinson']);
        $errorJson = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $errorJson['@type']);

        // Duplicate invitation
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $users[3]['@id'],
        ], [], ['jquinson']);
        $this->assertEquals(201, $response->getStatusCode());

        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $users[3]['@id'],
        ], [], ['jquinson']);

        $errorJson = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $errorJson['@type']);
    }

    public function testPutInvitation(): void
    {
        $event = $this->getFirstEvent('jquinson');

        // Get invitation for created event
        $response = $this->authenticatedRequest('GET', '/api/events/'.$event['id'].'/participants', [], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $invitation = $json['hydra:member'][0];

        // Using expiration date
        $expireAt = new \DateTime();
        $expireAt->add(New \DateInterval('P2D'));
        $response = $this->authenticatedRequest('PUT', $invitation['@id'], [
            'expiredAt' => $expireAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConfirmInvitation(): void
    {
        $event = $this->getFirstEvent();
        $user = $this->getOneUser('jquinson');

        // Invite current user as admin for access
        $expireAt = new \DateTime();
        $expireAt->add(New \DateInterval('P2D'));
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $user['@id'],
            'expireAt' => $expireAt->format('Y-m-d H:i:s'),
        ], [], ['admin']);

        $invitationJson = json_decode($response->getContent(), true);
        $this->assertEquals(201, $response->getStatusCode());

        // Confirm
        $response = $this->authenticatedRequest('PUT', '/api/invitations/'.$invitationJson['id'].'/confirm', [
            'confirmed' => true,
        ], [], ['jquinson']);

        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->authenticatedRequest('GET', $invitationJson['@id'], [], [], ['jquinson']);
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);

        $this->assertTrue($json['confirmed']);
    }

    public function testBadConfirmInvitation(): void
    {
        $event = $this->getFirstEvent();
        $user = $this->getOneUser('jquinson');

        // Create Invite current user. If it already exist it no important
        $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $user['@id'],
        ], [], ['admin']);

        $response = $this->authenticatedRequest(
            'GET',
            '/api/invitations?event='.urlencode($event['@id']).'&recipient='.urlencode($user['@id']),
            [],
            ['admin']
        );

        $json = json_decode($response->getContent(), true);
        $invitationJson = $json['hydra:member'][0];

        $expireAt = new \DateTime();
        $expireAt->sub(New \DateInterval('P2D'));

        // Expire invitation
        $this->authenticatedRequest('PUT', $invitationJson['@id'], [
            'expireAt' => $expireAt->format('Y-m-d H:i:s'),
        ], [], ['admin']);

        // Confirm
        $response = $this->authenticatedRequest('PUT', '/api/invitations/'.$invitationJson['id'].'/confirm', [
            'confirmed' => true,
        ], [], ['jquinson']);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testDeleteInvitation(): void
    {
        $event = $this->getFirstEvent('jquinson');
        $users = $this->getUsers('jquinson');

        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'event' => $event['@id'],
            'recipient' => $users[4]['@id'],
        ], [], ['jquinson']);

        $this->assertEquals(201, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);

        $response = $this->authenticatedRequest('DELETE', $json['@id'], [], [], ['jquinson']);
        $this->assertEquals(204, $response->getStatusCode());
    }





    public function testPostPlace(): void
    {
        // Create place
        $response = $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'New Amazing place '.time(),
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostPlaceError(): void
    {
        // Existing name
        $response = $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'Amazing place 1', // existing name
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $json['@type']);
    }

    public function testPutPlace(): void
    {
        // Try to post place. It doesn't matter if place already exists and throw 400
        $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'New Amazing place for test',
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        // Get it through GET request if previous request sent 400
        $placeJson = $this->getOnePlace('New Amazing place for test');

        // Update
        $response = $this->authenticatedRequest('PUT', $placeJson['@id'], [
            'name' => 'Another amazing place name for test '.time(),
            'postalCode' => '35000',
            'city' => 'Rennes',
        ], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->authenticatedRequest('GET', $json['@id'], [], [], ['jquinson']);
        $placeJson = json_decode($response->getContent(), true);

        $this->assertEquals('Rennes', $placeJson['city']);
        $this->assertEquals('35000', $placeJson['postalCode']);
        $this->assertEquals('FR', $placeJson['country']);
    }

    public function testPutPlaceError(): void
    {
        // Try to post place. It doesn't matter if place already exists and throw 400
        $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'New Amazing place for test',
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        // Get it through GET request if previous request sent 400
        $placeJson = $this->getOnePlace('New Amazing place for test');

        // Update with existing name
        $response = $this->authenticatedRequest('PUT', $placeJson['@id'], [
            'name' => 'Amazing place 1', // existing name
        ], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $json['@type']);
    }

    public function testDeletePlace(): void
    {
        // Try to post place. It doesn't matter if place already exists and throw 400
        $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'New Amazing place for to delete',
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        // Get it through GET request if previous request sent 400
        $placeJson = $this->getOnePlace('New Amazing place for to delete');

        // Delete place
        $response = $this->authenticatedRequest('DELETE', $placeJson['@id'], [], [], ['jquinson']);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeletePlaceError(): void
    {
        $event = $this->getFirstEvent('jquinson');

        // Try to post place. It doesn't matter if place already exists and throw 400
        $this->authenticatedRequest('POST', '/api/places', [
            'name' => 'New Amazing place to delete in event',
            'streetNumber' => "12",
            'city' => 'Paris',
            'streetName' => 'Rue de la paix',
            'postalCode' => '75000',
            'country' => 'FR',
        ], [], ['jquinson']);

        // Get it through GET request if previous request sent 400
        $placeJson = $this->getOnePlace('New Amazing place to delete in event');

        // Try to post place. It doesn't matter if place already exists and throw 400
        $this->authenticatedRequest('PUT', $event['@id'], [
            'place' => $placeJson['@id'],
        ], [], ['jquinson']);

        // DELETE place
        $response = $this->authenticatedRequest('DELETE', $placeJson['@id'], [], [], ['jquinson']);

        // @todo It's bad, I have to add hook to send a proper response when trying to delete place associated to an event
        $this->assertEquals(500, $response->getStatusCode());
    }



    public function testChangeAvatar(): void
    {
        // Post avatar
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('jack.png'),
        ]);

        $avatarJson = json_decode($response->getContent(), true);
        $this->assertEquals(201, $response->getStatusCode());

        $userJson = $this->getOneUser('jquinson');

        // Update user with new avatar
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'avatar' => $avatarJson['@id'],
        ], [], ['jquinson']);

        $this->assertEquals(200, $response->getStatusCode());

        $userJson = $this->getOneUser('jquinson');
        $this->assertArrayHasKey('avatar', $userJson);
        $this->assertArrayHasKey('avatarUrl', $userJson);
        $this->assertNotEmpty($userJson['avatarUrl']);
    }

    public function testUploadAvatar(): void
    {
        // Post avatar jpg
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('finn.jpg'),
        ]);
        $this->assertEquals(201, $response->getStatusCode());

        // Post avatar JPG
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('finn.JPG'),
        ]);
        $this->assertEquals(201, $response->getStatusCode());

        // Post avatar jpeg
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('finn.jpeg'),
        ]);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUploadAvatarError(): void
    {
        // Post pdf
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('cv.pdf'),
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // Post bad js
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('virus-badass-qui-vole-toutes-tes-data.js'),
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // Post bad png
        $response = $this->authenticatedRequest('POST', '/api/media', [], [], ['jquinson'], [
            'file' => $this->getUploadedFile('virus-badass-qui-pete-ton-processeur.png'),
        ]);
        $this->assertEquals(400, $response->getStatusCode());

    }



    public function testPostUser(): void
    {
        // Create user
        $response = $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+'.time().'@gmail.com',
            'username' => 'jeremie.quinson+'.time(),
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostUserError(): void
    {
        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+'.time().'@gmail.com',
            'username' => 'jeremie.quinson+duplicated',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Post same username
        $response = $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+'.time().'@gmail.com',
            'username' => 'jeremie.quinson+duplicated',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $json);
        $this->assertEquals('invalid_inputs', $json['code']);

        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+duplicated@gmail.com',
            'username' => 'jeremie.quinson+'.time(),
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Post same username
        $response = $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+duplicated@gmail.com',
            'username' => 'jeremie.quinson+'.time(),
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $json);
        $this->assertEquals('invalid_inputs', $json['code']);
    }

    public function testUpdateUser(): void
    {
        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+updatetest@gmail.com',
            'username' => 'jeremie.quinson+updatetest',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Update email
        $newEmail = 'jeremie.quinson+updatetest'.time().'@gmail.com';
        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'email' => $newEmail,
        ], [], ['admin']);

        $this->assertEquals(200, $response->getStatusCode());

        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $this->assertEquals($newEmail, $userJson['email']);
    }

    public function testUpdateErrorUser(): void
    {
        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+updatetest@gmail.com',
            'username' => 'jeremie.quinson+updatetest',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Try to post user. It doesn't matter if user already exists and throw 400
        $this->request('POST', '/register', [
            'email' => 'jeremie.quinson+updatetest2@gmail.com',
            'username' => 'jeremie.quinson+updatetest2',
            'plainPassword' => 'testtest',
        ], [
            'content-type' => 'application/json',
        ]);

        // Update with existing username
        $userJson = $this->getOneUser('jeremie.quinson+updatetest');
        $response = $this->authenticatedRequest('PUT', $userJson['@id'], [
            'email' => 'jeremie.quinson+updatetest2@gmail.com',
        ], [], ['admin']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $json['@type']);
    }


    public function testPostComment(): void
    {
        $eventData = $this->getFirstEvent();

        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Ceci est très swaggué',
            'rate' => 4,
            'event' => $eventData['@id'],
        ], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(201, $response->getStatusCode());

        // Test item's structure
        foreach (['@id', 'id', 'author', 'content', 'rate', 'createdAt'] as $key) {
            $this->assertArrayHasKey($key, $json);
        }
        $this->assertInternalType('string', $json['author']);
        $this->assertEquals('Ceci est très swaggué', $json['content']);
    }


    public function testPostCommentErrors(): void
    {
        $userJack = $this->getOneUser('jack.thedog.real');
        $userFinn = $this->getOneUser('finn.thehuman.real');

        // Get place with name "Amazing place 20"
        $placeJson = $this->getOnePlace(urlencode('Amazing place 20'));
        [$startAt, $endAt] = $this->getEventDate(true);

        // Jack create event
        $response = $this->authenticatedRequest('POST', '/api/events', [
            "name" => "Party hard",
            "description" => "Party hard with dancing bugs",
            "startAt" => $startAt,
            "endAt" => $endAt,
            'place' => $placeJson['@id'],
        ], [], [$userJack['username']]);


        $eventJson = json_decode($response->getContent(), true);

        // Jack invite finn
        $response = $this->authenticatedRequest('POST', '/api/invitations', [
            'recipient' => $userFinn['@id'],
            'event' => $eventJson['@id'],
        ], [], [$userJack['username']]);
        $invitationJson = json_decode($response->getContent(), true);


        // Finn want to post comment but invitation is not confirmed
        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Non confirmed',
            'rate' => 4,
            'event' => $eventJson['@id'],
        ], [], [$userFinn['username']]);

        $this->assertEquals(400, $response->getStatusCode());


        // Post comment but event is not past
        // Confirm
        $this->authenticatedRequest('PUT', '/api/invitations/'.$invitationJson['id'].'/confirm', [
            'confirmed' => true,
        ], [], [$userFinn['username']]);

        [$futureStartAt, $futurEndAt] = $this->getEventDate(false);
        $this->authenticatedRequest('PUT', $eventJson['@id'], [
            "startAt" => $futureStartAt,
            "endAt" => $futurEndAt,
        ], [], [$userJack['username']]);

        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Non finished',
            'rate' => 4,
            'event' => $eventJson['@id'],
        ], [], [$userFinn['username']]);
        $this->assertEquals(400, $response->getStatusCode());


        // Event is finished but finn try to post twice
        $this->authenticatedRequest('PUT', $eventJson['@id'], [
            "startAt" => $startAt,
            "endAt" => $endAt,
        ], [], [$userJack['username']]);

        // Comment is duplicated
        $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Comment duplicated',
            'rate' => 4,
            'event' => $eventJson['@id'],
        ], [], [$userFinn['username']]);

        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Comment duplicated',
            'rate' => 4,
            'event' => $eventJson['@id'],
        ], [], [$userFinn['username']]);

        $this->assertEquals(400, $response->getStatusCode());

        // Non invited user post comment
        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Non invited',
            'rate' => 4,
            'event' => $eventJson['@id'],
        ], [], ['jquinson']);

        $this->assertEquals(400, $response->getStatusCode());

    }

    public function testPutComment(): void
    {
        $eventData = $this->getFirstEvent();

        // Get last created comment
        $response = $this->authenticatedRequest('GET', '/api/events/'.$eventData['id'].'/comments?order%5Bid%5D=desc', [], [], ['jquinson']);
        $json = json_decode($response->getContent(), true);
        $member = $json['hydra:member'][0];

        $response = $this->authenticatedRequest(
            'PUT',
            $member['@id'],
            [
                'content' => 'Ouais en fait bof',
                'rate' => 2,
            ], [], ['jquinson']
        );

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());

        // Test item's structure
        foreach (['@id', 'author', 'content', 'rate', 'createdAt'] as $key) {
            $this->assertArrayHasKey($key, $json);
        }
        $this->assertInternalType('string', $json['author']);
        $this->assertEquals('Ouais en fait bof', $json['content']);
        $this->assertEquals(2, $json['rate']);
    }

    public function testDeleteComment(): void
    {
        $eventData = $this->getFirstEvent();

        // Get last created comment
        $response = $this->authenticatedRequest('GET', '/api/events/'.$eventData['id'].'/comments?order%5Bid%5D=desc', [], [], ['jquinson']);
        $json = json_decode($response->getContent(), true);
        $member = $json['hydra:member'][0];

        $response = $this->authenticatedRequest('DELETE', $member['@id'], [], [], ['jquinson']);

        $this->assertEquals(204, $response->getStatusCode());
    }




    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

}

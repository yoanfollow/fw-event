<?php


namespace App\Tests;

use App\Tests\Utils\ApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiWriteTest extends WebTestCase
{

    use ApiTestTrait;

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

    public function testPostCommentDuplicate(): void
    {
        $eventData = $this->getFirstEvent();

        $response = $this->authenticatedRequest('POST', '/api/comments', [
            'content' => 'Comment duplicated',
            'rate' => 4,
            'event' => $eventData['@id'],
        ], [], ['jquinson']);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('ConstraintViolationList', $json['@type']);
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
    
    /**
     * @param null $authorUsername
     * @return array
     */
    protected function getFirstEvent($authorUsername = null)
    {
        // Get first event
        $query = '';
        if (!empty($authorUsername)) {
            $author = $this->getOneUser($authorUsername);
            $query = '&organizer='.urlencode($author['@id']);
        }

        $response = $this->authenticatedRequest('GET', '/api/events?itemsPerPage=1'.$query);
        $json = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === 200 && !isset($json['hydra:member'][0])) {
            throw new \Exception(sprintf('Event not found for author %s', $authorUsername));
        }

        return $json['hydra:member'][0];
    }

    /**
     * @param null $name
     * @return array
     */
    protected function getOnePlace($name = null)
    {
        $queryStr = !empty($name) ? '&name='.$name : '';
        $response = $this->authenticatedRequest('GET', '/api/places?itemsPerPage=1'.$queryStr);
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param $username
     * @return array
     */
    protected function getOneUser(string $username)
    {
        $response = $this->authenticatedRequest('GET', '/api/users?itemsPerPage=1&username='.urlencode($username));
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param bool $past
     * @return array
     * @throws \Exception
     */
    protected function getEventDate($past = false)
    {
        $startAt = new \DateTime();
        if ($past) {
            $startAt->add(new \DateInterval('P5D'));
        } else {
            $startAt->sub(new \DateInterval('P5D'));
        }
        $startAt->setTime(15, 0);

        $endAt = clone $startAt;
        $endAt->setTime(16, 0);
        return [$startAt->format('Y-m-d H:i:s'), $endAt->format('Y-m-d H:i:s')];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

}

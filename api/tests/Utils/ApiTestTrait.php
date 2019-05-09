<?php


namespace App\Tests\Utils;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @param array $files
     * @return Response
     */
    protected function request(string $method, string $uri, $content = null, array $headers = [], $authenticated = false, array $credentials = [], array $files = []): Response
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

        $this->client->request($method, $uri, [], $files, $server, $content);

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
     * @param array $files
     * @return Response
     */
    protected function authenticatedRequest(string $method, string $uri, $content = null, array $headers = [], array $credentials = [], array $files = []): Response
    {
        return $this->request($method, $uri, $content, $headers, true, $credentials, $files);
    }

    /**
     * @param string|null $authorUsername
     * @return array
     * @throws \Exception
     */
    protected function getFirstEvent(?string $authorUsername = null) : array
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
     * @param string|null $eventAuthorUsername
     * @return array
     * @throws \Exception
     */
    protected function getFirstComment(?string $eventAuthorUsername = null) : array
    {
        $event = $this->getFirstEvent($eventAuthorUsername);
        $response = $this->authenticatedRequest('GET', '/api/events/'.$event['id'].'/comments');
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param string|null $eventAuthorUsername
     * @return array
     * @throws \Exception
     */
    protected function getFirstInvitation(?string $eventAuthorUsername = null) : array
    {
        $event = $this->getFirstEvent($eventAuthorUsername);
        $response = $this->authenticatedRequest('GET', '/api/events/'.$event['id'].'/participants');
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param string|null $name
     * @return array
     */
    protected function getOnePlace(?string $name = null) : array
    {
        $queryStr = !empty($name) ? '&name='.$name : '';
        $response = $this->authenticatedRequest('GET', '/api/places?itemsPerPage=1'.$queryStr);
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param string $username
     * @return array
     */
    protected function getOneUser(string $username) : array
    {
        $response = $this->authenticatedRequest('GET', '/api/users?itemsPerPage=1&username='.urlencode($username));
        $json = json_decode($response->getContent(), true);
        return $json['hydra:member'][0];
    }

    /**
     * @param string|null $username
     * @return array
     */
    protected function getUsers(?string $username) : array
    {
        $response = $this->authenticatedRequest('GET', '/api/users');
        $json = json_decode($response->getContent(), true);
        $users = [];
        foreach ($json['hydra:member'] as $user) {
            if ($username !== $user['username']) {
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * @param bool $past To get dates < today
     * @return array
     * @throws \Exception
     */
    protected function getEventDate(bool $past = false) : array
    {
        $startAt = new \DateTime();
        $endAt = new \DateTime();
        if ($past) {
            $startAt->sub(new \DateInterval('P5D'));
            $endAt->sub(new \DateInterval('P5D'));
        } else {
            $endAt->add(new \DateInterval('P5D'));
            $startAt->add(new \DateInterval('P5D'));
        }

        $startAt->setTime(15, 0);
        $endAt->setTime(16, 0);

        return [$startAt->format('Y-m-d H:i:s'), $endAt->format('Y-m-d H:i:s')];
    }

    /**
     * @param string $fileName
     * @return UploadedFile
     * @throws \Exception
     */
    protected function getUploadedFile(string $fileName) : UploadedFile
    {
        $cacheDir = static::$kernel->getCacheDir().'/uploads';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777);
        }

        $parts = explode('.', $fileName);
        if (!$parts[1]) {
            throw new \Exception(sprintf('No extension in filename %s', $fileName));
        }

        $fileNameRaw = $parts[0];
        $fileExtension = $parts[1];


        $newFileName = $fileNameRaw.md5('/data/avatars/jack'.time()).'.'.$fileExtension;
        $newFilePath = $cacheDir.'/'.$newFileName;
        copy(__DIR__.'/data/avatars/'.$fileName, $newFilePath);

        $uploadFile = new UploadedFile(
            $newFilePath,
            $newFileName,
            'image/'.$fileExtension,
            null,
            true
        );

        return $uploadFile;
    }
}

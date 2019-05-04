<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\Invitation;
use App\Entity\Place;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /** @var UserPasswordEncoderInterface $encoder */
    private $encoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Main user
        $user = new User();
        $user
            ->setUsername('admin')
            ->setEmail('jeremie.quinson@gmail.com')
            ->setPassword($this->encoder->encodePassword($user, 'test'))
            ->setRoles(['ROLE_ADMIN'])
        ;
        $manager->persist($user);

        // Main user
        $user = new User();
        $user
            ->setUsername('jquinson')
            ->setEmail('jeremie.quinson+2@gmail.com')
            ->setPassword($this->encoder->encodePassword($user, 'test'))
        ;
        $manager->persist($user);


        // List of fake data to build fake names
        $firstNames = ['Jack', 'Finn', 'Lady', 'Noel', 'Hubert', 'Jimmy', 'Johnny'];
        $lastNames = ['The Dog', 'The Human', 'Rainicorn', 'Flantier', 'Bonnisseur de la Bath', 'Hendrix', 'English'];
        $eventQualifiers = ['Amazing', 'Boring', 'Weird', 'Common', 'Interesting', 'Agile', 'Satanic'];
        $eventTypes = ['Event', 'Meeting', 'Lunch', 'Date', 'Ritual'];

        // Add random places for event fixtures
        $places = [];
        for ($placeKey = 1; $placeKey < 20; $placeKey++) {
            $place = new Place();
            $place->setName(sprintf('Amazing place %d', $placeKey))
                ->setCity(sprintf('City %d', $placeKey))
                ->setCountry(['France', 'Usa', 'Spain'][rand(0,2)])
                ->setStreetNumber($placeKey)
                ->setStreetName(sprintf('A random street "%s"', $placeKey))
                ->setPostalCode(sprintf('350%s', sprintf("%02d", $placeKey)))
                ;
            $manager->persist($place);
            $places[] = $place;
        }


        $users = [];
        for ($i = 0; $i < 10; $i++) {

            // Pick a random first name and last name to build a complet username and email
            $firstName = $firstNames[rand(0, count($firstNames) - 1)];
            $lastName = $lastNames[rand(0, count($lastNames) - 1)];
            $userName = sprintf('%s.%s.%d', strtolower(str_replace(' ', '', $firstName)), strtolower(str_replace(' ', '', $lastName)), $i);
            $email = sprintf('%s.%s@yopmail.com', strtolower(str_replace(' ', '', $firstName)), strtolower(str_replace(' ', '', $lastName)));

            $user = new User();
            $user
                ->setUsername($userName)
                ->setEmail($email)
                ->setPassword($this->encoder->encodePassword($user, 'test'))
                ;
            $manager->persist($user);
            $users[] = $user;
        }


        $events = [];
        foreach ($users as $userKey => $user) {

            // Create event organized for current user
            $maxEvents = rand(20, 150);
            for ($eventKey = 0; $eventKey < $maxEvents; $eventKey++) {
                $eventQualifier = $eventQualifiers[rand(0, count($eventQualifiers) - 1)];
                $eventType = $eventTypes[rand(0, count($eventTypes) - 1)];
                $place = $places[rand(0, count($places) - 1)];

                // Days before or after current date
                $delay = rand(-10, 20);

                // Start at
                $startAt = new \DateTime();
                $startAt->setTime(rand(8, 17), 0); // Random time from 8:00 to 17:00

                // If delay < 0, event is in the past, if delay > 0, event is in the future. Otherwise, event is today
                if ($delay < 0) {
                    $startAt->sub(new \DateInterval(sprintf('P%sD', abs($delay))));
                } else if ($delay > 0) {
                    $startAt->add(new \DateInterval(sprintf('P%sD', $delay)));
                }

                // Clone startAt date to create endAt
                $endAt = clone $startAt;
                $endAt->add(new \DateInterval(sprintf('PT%dM', [30, 60, 90][rand(0, 2)]))); // Add random duration

                // Create event
                $event = new Event();
                $event
                    ->setName(sprintf('%s %s', $eventQualifier, $eventType))
                    ->setDescription(
                        sprintf('<p>%s %s details</p>
                        <p><strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit. Duis ac ipsum tellus. Duis ut elit maximus, vulputate sem nec, consequat velit. Nam eget ligula nec felis rhoncus feugiat eu nec odio. Cras lacinia tellus nisl, ut blandit dui laoreet id. Ut vulputate mattis elit eget pellentesque. Cras ullamcorper magna non tincidunt dictum. Etiam pulvinar varius est, et convallis massa gravida sed. Suspendisse potenti. Phasellus ac rutrum est, vitae sodales dolor. Nam id lobortis libero. Maecenas non ipsum neque.</p>
                        <p>In vitae aliquet sapien. In hac habitasse platea dictumst. Duis molestie, nisi quis ullamcorper mattis, tortor justo condimentum ipsum, non pulvinar justo ante quis ipsum. Pellentesque consequat sagittis felis, ac tincidunt mauris molestie quis. Etiam a sapien fringilla, condimentum mauris ut, dignissim ipsum. Aenean commodo diam a porttitor tempor. Maecenas sed volutpat nisi. Donec ut neque vitae arcu rutrum tincidunt. Morbi nunc neque, bibendum id massa ut, pharetra auctor nibh. Etiam sit amet sem odio.</p>', $eventQualifier, $eventType)
                    )
                    ->setOrganizer($user)
                    ->setStartAt($startAt)
                    ->setEndAt($endAt)
                    ->setPlace($place)
                ;

                $manager->persist($event);
                $events[] = $event;

                // Create random invitation.
                // Add invited user key in a excluded keys list.
                // Prevent current user from receiver list by adding key in exclude list.
                $excludeUsersKeys = [$userKey];

                // Create invitations
                for ($inviteKey = 0; $inviteKey < rand(4, 10); $inviteKey++) {
                    while(in_array(($receiverKey = rand(0, count($users) - 1)), [$excludeUsersKeys]));
                    $excludeUsersKeys[] = $receiverKey;

                    $invitation = new Invitation();
                    $invitation
                        ->setEvent($event)
                        ->setRecipient($users[$receiverKey])
                        ->setConfirmed(rand(0, 1) !== 0) // randomly set state confirmed
                        ;

                    // Randomly add an expiration date (or no)
                    if (rand(0, 1) === 0) {
                        $expireAt = clone $startAt;
                        $expireAt->sub(new \DateInterval(sprintf('P%dD', rand(1, 4))));
                        $invitation->setExpireAt($expireAt);
                    }

                    $manager->persist($invitation);
                }
            }
        }


        $commentContent = [
            'I would prefer a date with my mother in law',
            'Meh, I liked nothing but not the worst',
            'Not Bad. Not the best event of the world',
            'Good',
            'Best event of the world'
        ];
        foreach ($events as $event) {

            // Create random comment.
            // Add user key who commented in a excluded keys list.
            // Prevent organizer user to comment his own event by adding key in exclude list.
            $excludeUsersKeys = [$event->getOrganizer()->getId()];
            for ($inviteKey = 0; $inviteKey < rand(4, 10); $inviteKey++) {
                while (in_array(($authorKey = rand(0, count($users) - 1)), [$excludeUsersKeys]));

                $note = rand(1, 5);
                $comment = new Comment();
                $comment
                    ->setEvent($event)
                    ->setAuthor($users[$authorKey])
                    ->setContent($commentContent[$note - 1])
                    ->setRate($note)
                ;

                $manager->persist($comment);
            }
        }


        // Add more "consistent data" for specific tests


        $manager->flush();
    }
}

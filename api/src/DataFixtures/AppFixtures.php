<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\Invitation;
use App\Entity\Place;
use App\Entity\User;
use App\Helpers\DateHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Create fixtures
 * When I realized that Alice Bundle make it easier it was too late :(
 */
class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $maxRandomPlaces = 20;
        $maxRandomUsers = 10;
        $maxRandomEventsPerUser = 50; // 10x50=500
        $maxRandomInvitationsPerEvent = 5; // 5x50x10=5000
        $maxRandomCommentPerEvent = 5;  // 5x50x10=5000

        // 2 Main users
        $user = new User();
        $user
            ->setUsername('admin')
            ->setEmail('jeremie.quinson@gmail.com')
            ->setPassword('test')
            ->setRoles(['ROLE_ADMIN'])
        ;
        $manager->persist($user);

        $user = new User();
        $user
            ->setUsername('jquinson')
            ->setEmail('jeremie.quinson+2@gmail.com')
            ->setPassword('test')
        ;
        $manager->persist($user);


        // List of fake data to build fake names
        $firstNames = ['Jack', 'Finn', 'Lady', 'Noel', 'Hubert', 'Jimmy', 'Johnny'];
        $lastNames = ['The Dog', 'The Human', 'Rainicorn', 'Flantier', 'Bonnisseur de la Bath', 'Hendrix', 'English'];
        $eventQualifiers = ['Amazing', 'Boring', 'Weird', 'Common', 'Interesting', 'Agile', 'Satanic'];
        $eventTypes = ['Event', 'Meeting', 'Lunch', 'Date', 'Ritual'];

        // Add 20 random places for event fixtures
        $places = [];
        for ($placeKey = 0; $placeKey < $maxRandomPlaces; $placeKey++) {
            $place = new Place();
            $place
                ->setName(sprintf('Amazing place %d', $placeKey + 1))
                ->setCity(sprintf('City %d', $placeKey + 1))
                ->setCountry(['France', 'Usa', 'Spain'][rand(0,2)])
                ->setStreetNumber($placeKey + 1)
                ->setStreetName(sprintf('A random street "%s"', $placeKey + 1))
                ->setPostalCode(sprintf('350%s', sprintf("%02d", $placeKey + 1)))
                ;
            $manager->persist($place);
            $places[] = $place;
        }


        // Create 10 random users
        $users = [];
        for ($i = 0; $i < $maxRandomUsers; $i++) {

            // Pick a random first name and last name to build a complet username and email
            $firstName = $firstNames[rand(0, count($firstNames) - 1)];
            $lastName = $lastNames[rand(0, count($lastNames) - 1)];
            $userName = sprintf('%s.%s.%d', strtolower(str_replace(' ', '', $firstName)), strtolower(str_replace(' ', '', $lastName)), $i);
            $email = sprintf(
                '%s.%s+%d@yopmail.com',
                strtolower(str_replace(' ', '', $firstName)),
                strtolower(str_replace(' ', '', $lastName)),
                $i
            );

            $user = new User();
            $user
                ->setUsername($userName)
                ->setEmail($email)
                ->setPassword('test')
                ;
            $manager->persist($user);
            $users[] = $user;
        }

        // Create 50 random events per users (500 events)
        // Set 20 passed events, 1 event in current day and 29 future events
        $events = [];
        $invitations = [];
        foreach ($users as $userKey => $user) {

            // Create event organized for current user
            for ($eventKey = 0; $eventKey < $maxRandomEventsPerUser; $eventKey++) {
                $eventQualifier = $eventQualifiers[rand(0, count($eventQualifiers) - 1)];
                $eventType = $eventTypes[rand(0, count($eventTypes) - 1)];
                $place = $places[rand(0, count($places) - 1)];

                // Days before or after current date
                $delay = $eventKey - 20; // 20 past event, 1 event today and 29 future events

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
                    ->setName(sprintf('event name %d %s %s', $eventKey, $eventQualifier, $eventType))
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
                $randomUsers = $users;
                unset($randomUsers[$userKey]);

                // Create 5 random invitations per events (5000)
                for ($inviteKey = 0; $inviteKey < $maxRandomInvitationsPerEvent; $inviteKey++) {
                    // Randomly pick user, remove it from array to always pick a new user
                    $randomUserKey = array_rand($randomUsers);
                    $userToInvite = $randomUsers[$randomUserKey];
                    unset($randomUsers[$randomUserKey]);

                    if (count($randomUsers) === 0) {
                        throw new \Exception('Not enough user for invitation creation loop');
                    }

                    $invitation = new Invitation();
                    $invitation
                        ->setEvent($event)
                        ->setRecipient($userToInvite)
                        ->setConfirmed(rand(0, 1) !== 0) // randomly set state confirmed
                        ;

                    // Randomly add an expiration date (or no)
                    if (rand(0, 1) === 0) {
                        $expireAt = clone $startAt;
                        $expireAt->sub(new \DateInterval(sprintf('P%dD', rand(1, 4))));
                        $invitation->setExpireAt($expireAt);
                    }

                    $manager->persist($invitation);
                    $invitations[] = $invitation;
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

        // Filters event and get only finished events
        // 20 events per users so 200 events
        $finishedEvents = array_filter($events, function(Event $event) {
            // If today, skip
            if ($event->getEndAt()->format('Y-m-d') === DateHelper::getToday()->format('Y-m-d')) return false;
            return ($event->getEndAt() < DateHelper::getToday());
        });

        // Add 10 comments per event (2000 comments)
        foreach ($finishedEvents as $event) {
            // Create 5 random comment per events (2500).
            // Copy user list to pick random user in it
            $randomUsers = $users;
            for ($commentKey = 0; $commentKey < $maxRandomCommentPerEvent; $commentKey++) {

                // Randomly pick user, remove it from array to always pick a new user.
                // If picked user is the author of the event, don't use it
                $continuePick = true;
                while (true === $continuePick) {
                    if (count($randomUsers) === 0) {
                        throw new \Exception('Not enough user for comment creation loop');
                    }

                    $randomUserKey = array_rand($randomUsers);
                    $author = $randomUsers[$randomUserKey];
                    unset($randomUsers[$randomUserKey]);

                    // Check username (because there is no id yet)
                    if ($author->getUsername() !== $event->getOrganizer()->getUsername()) {
                        $continuePick = false;
                    }
                }

                $note = rand(1, 5);
                $comment = new Comment();
                $comment
                    ->setEvent($event)
                    ->setAuthor($author)
                    ->setContent($commentContent[$note - 1])
                    ->setRate($note)
                ;

                $manager->persist($comment);
            }
        }


        // Add 7 more user
        $usersNames = ['jack.thedog.real', 'lady.rainicorn.real', 'finn.thehuman.real', 'noel.flantier.real', 'hubert.bonisseurdelabath.real', 'jimmy.hendrix.real', 'johnny.english.real'];

        foreach ($usersNames as $usersName) {
            // 2 Main users
            $user = new User();
            $user
                ->setUsername($usersName)
                ->setEmail($usersName.'@yopmail.com')
                ->setPassword('test')
                ->setRoles(['ROLE_USER'])
            ;

            $manager->persist($user);

            // 5 invitations per users (35 more in total)
            for ($i = 0; $i < 5; $i++) {
                $invitation = new Invitation();
                $invitation
                    ->setEvent($events[$i])
                    ->setRecipient($user)
                    ->setConfirmed(rand(0, 1) !== 0)
                ;
                $manager->persist($invitation);
                $invitations[] = $invitation;
            }

            // 5 comments per users (35 more in total)
            for ($i = 0; $i < 5; $i++) {
                $comment = new Comment();
                $comment
                    ->setEvent($finishedEvents[$i])
                    ->setAuthor($user)
                    ->setContent('Great')
                    ->setRate(4)
                ;
                $manager->persist($comment);
                $comments[] = $comment;
            }

            $users[] = $user;
        }

        // Add more "consistent data" for specific tests


        $manager->flush();
    }
}

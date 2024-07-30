<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPassworHasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $listAuthor = [];
        for ($i = 0; $i < 10; $i++) {
            $author = new Author;
            $author->setFirstname('Prenom' . $i);
            $author->setLastname('Nom' . $i);
            $manager->persist($author);
            $listAuthor[] = $author;
        }
        for ($i = 0; $i < 20; $i++) {
            $book = new Book();
            $book->setTitle('Book ' . $i);
            $book->setCoverText('Cover Text ' . $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }
        $userAdmin = new User();
        $userAdmin->setEmail('admin@example.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword($this->userPassworHasher->hashPassword($userAdmin, 'password'));
        $manager->persist($userAdmin);
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPassworHasher->hashPassword($user, 'password'));
        $manager->persist($user);
        $manager->flush();
    }
}

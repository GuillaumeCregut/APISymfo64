<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $listAuthor = [];
        for($i=0;$i<10;$i++) {
            $author = new Author;
            $author->setFirstname('Prenom' . $i);
            $author->setLastname('Nom' . $i);
            $manager->persist($author);
            $listAuthor[]=$author;
        }
        for ($i = 0; $i < 20; $i++) {
            $book= new Book();
            $book->setTitle('Book ' . $i);
            $book->setCoverText('Cover Text ' . $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}

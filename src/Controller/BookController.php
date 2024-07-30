<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'book', methods: ['GET'])]
    public function getAllBooks(BookRepository $books, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $books->findAll();
        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/books/{id}', name: 'detailBook', methods: ['GET'])]
    public function getOneBook(SerializerInterface $serializer, Book $book): JsonResponse
    {
        // $book = $books->find($id);
        //  dd($book);
        if ($book) {
            $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(['message' => 'Book not found'], Response::HTTP_NOT_FOUND, []);
    }

    #[Route('/api/books/{id}', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteBook(EntityManagerInterface $em, Book $book): JsonResponse
    {
        if ($book) {
            $em->remove($book);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['message' => 'Book not found'], Response::HTTP_NOT_FOUND, []);
    }

    #[Route('/api/books/', name: 'AddBook', methods: ['POST'])]
    public function addBook(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        AuthorRepository $authors,
        ValidatorInterface $validator
    ): JsonResponse {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $errors = $validator->validate($book);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_UNPROCESSABLE_ENTITY, [], true);
        }
        $em->persist($book);
        $em->flush();
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'];
        $book->setAuthor($authors->find($idAuthor));
        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/books/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBook(
        EntityManagerInterface $em,
        Book $book,
        Request $request,
        SerializerInterface $serializer,
        AuthorRepository $authors,
        ValidatorInterface $validator
    ): JsonResponse {
        $updateBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $book]);
        $errors = $validator->validate($updateBook);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_UNPROCESSABLE_ENTITY, [], true);
        }
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'];
        $updateBook->setAuthor($authors->find($idAuthor));
        $em->persist($updateBook);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

<?php

namespace App\Controller;

use App\Entity\Author;
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

class AuthorController extends AbstractController
{
    #[Route('api/authors', name: 'authors', methods: ['GET'])]
    public function index(AuthorRepository $authors, SerializerInterface $serializer): JsonResponse
    {
        $authorsList = $authors->findAll();
        $jsonAuthorsList = $serializer->serialize($authorsList, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthorsList, Response::HTTP_OK, [], true);
    }

    #[Route('api/authors/{id}', name: 'authorsDetail', methods: ['GET'])]
    public function findOne(SerializerInterface $serializer, Author $author): JsonResponse
    {

        $jsonAuthorsList = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthorsList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(EntityManagerInterface $em, Author $author): JsonResponse
    {
        if ($author) {
            $em->remove($author);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['message' => 'Author not found'], Response::HTTP_NOT_FOUND, []);
    }

    #[Route('api/authors', name: 'addAuthors', methods: ['POST'])]
    public function addAuthor(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        Request $request,
        UrlGeneratorInterface $urlGenerator, 
        ValidatorInterface $validator
    ): JsonResponse {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');
        $errors = $validator->validate($author);
        if($errors->count()>0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_UNPROCESSABLE_ENTITY, [], true);
        }
        $em->persist($author);
        $em->flush();
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBooks']);
        $location = $urlGenerator->generate('authorsDetail', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['PUT'])]
    public function updateAuthor(
        EntityManagerInterface $em,
        Author $author,
        SerializerInterface $serializer,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        $updateAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $author]);
        $errors = $validator->validate($updateAuthor);
        if($errors->count()>0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_UNPROCESSABLE_ENTITY, [], true);
        }
        $em->persist($updateAuthor);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

<?php

namespace App\Controller;

use App\Entity\BlogArticle;
use App\Repository\BlogArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api', name: 'api_')]
class BlogArticleController extends AbstractController
{
    #[Route('/blog-articles', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->request->all();
        $file = $request->files->get('coverPicture');
        
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $blogArticle = new BlogArticle();
        $blogArticle->setAuthorId($data['authorId']);
        $blogArticle->setTitle($data['title']);
        $blogArticle->setPublicationDate(new \DateTime($data['publicationDate']));
        $blogArticle->setCreationDate(new \DateTime($data['creationDate']));
        $blogArticle->setContent($data['content']);
        $blogArticle->setKeywords(json_decode($data['keywords'], true));
        $blogArticle->setStatus($data['status']);
        $blogArticle->setSlug($data['slug']);

        if ($file instanceof UploadedFile) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploaded_pictures', $fileName);
            } catch (FileException $e) {
                return $this->json(['error' => 'Failed to upload file.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $blogArticle->setCoverPictureRef($fileName);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($blogArticle);
        $entityManager->flush();

        return $this->json($blogArticle, Response::HTTP_CREATED);
    }

    #[Route('/blog-articles', methods: ['GET'])]
    public function list(BlogArticleRepository $repository): Response
    {
        $articles = $repository->findAll();
        return $this->json($articles);
    }

    #[Route('/blog-articles/{id}', methods: ['GET'])]
    public function get(BlogArticle $article): Response
    {
        return $this->json($article);
    }

    #[Route('/blog-articles/{id}', methods: ['PATCH'])]
    public function update(Request $request, BlogArticle $article, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->request->all();
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $article->setTitle($data['title'] ?? $article->getTitle());
        $article->setPublicationDate(new \DateTime($data['publicationDate'] ?? $article->getPublicationDate()->format('Y-m-d H:i:s')));
        $article->setCreationDate(new \DateTime($data['creationDate'] ?? $article->getCreationDate()->format('Y-m-d H:i:s')));
        $article->setContent($data['content'] ?? $article->getContent());
        $article->setKeywords(json_decode($data['keywords'] ?? json_encode($article->getKeywords()), true));
        $article->setStatus($data['status'] ?? $article->getStatus());
        $article->setSlug($data['slug'] ?? $article->getSlug());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->json($article);
    }

    #[Route('/blog-articles/{id}', methods: ['DELETE'])]
    public function delete(BlogArticle $article): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article->setStatus('deleted');
        $entityManager->flush();

        return $this->json(['status' => 'Article soft-deleted.']);
    }
}

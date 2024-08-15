<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BlogArticleControllerTest extends WebTestCase
{
    public function testCreateBlogArticle()
    {
        $client = static::createClient();
        $client->request('POST', '/api/blog-articles', [
            'json' => [
                'authorId' => 1,
                'title' => 'Test Article',
                'publicationDate' => '2024-08-15T00:00:00',
                'creationDate' => '2024-08-15T00:00:00',
                'content' => 'This is a test article.',
                'keywords' => json_encode(['test', 'article']),
                'status' => 'draft',
                'slug' => 'test-article',
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }
}

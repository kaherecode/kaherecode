<?php

namespace App\Service;

use App\Entity\Comment;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    protected $endpoint;

    /**
     * @var RequestContext
     */
    protected $requestContext;


    public function __construct(
        HttpClientInterface $client,
        string $akismetKey,
        RequestContext $requestContext
    ) {
        $this->client = $client;
        $this->endpoint = sprintf(
            'https://%s.rest.akismet.com/1.1/comment-check',
            $akismetKey
        );
        $this->requestContext = $requestContext;
    }

    /**
     * @return bool
     *
     * @throws \RuntimeException if the call did not work
     */
    public function isSpam(Comment $comment, array $context): bool
    {
        $response = $this->client->request('POST', $this->endpoint, [
            'body' => array_merge($context, [
                'blog' => $this->requestContext->getHost(),
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor()->getUsername(),
                'comment_author_email' => $comment->getAuthor()->getEmail(),
                'comment_content' => htmlspecialchars($comment->getContent()),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'en, fr',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ]),
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return true;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to check for spam: %s (%s).',
                    $content,
                    $headers['x-akismet-debug-help'][0]
                )
            );
        }

        return 'true' === $content ? true : false;
    }
}

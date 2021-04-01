<?php

namespace App\IbexaTests\PostPublicationBundle\Service;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformAdminUi\Event\ContentProxyTranslateEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostPublicationService
{
    /**
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var ContentService
     */
    private $contentService;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var LoggerInterface
     */
    public $logger;

    private $notifyHost;

    private $notifyEndPoint;

    public function __construct(HttpClientInterface $client, ContentService $contentService, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, string $notifyHost, string $notifyEndPoint)
    {
        $this->client = $client;
        $this->contentService = $contentService;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;

        $this->notifyHost = $notifyHost;
        $this->notifyEndPoint = $notifyEndPoint;
    }

    /**
     *
     * Send notification to remote server
     * @param array $datas
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function newVersionNotification(array $datas): bool
    {
        $jsonDatas = json_encode($datas);
        try {
            $response = $this->client->request('GET', $this->notifyHost . $this->notifyEndPoint, ['body' => $jsonDatas]);

            return $this->handleNotificationResponse($response);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add new translation for currently published content
     * @param string $newLanguageCode
     * @param Content $content
     * @return bool
     */
    public function newTranslation(string $newLanguageCode, Content $content): bool
    {
        try {
            /** @var \EzSystems\EzPlatformAdminUi\Event\ContentProxyTranslateEvent $event */
            $this->eventDispatcher->dispatch(
                new ContentProxyTranslateEvent(
                    $content->id,
                    $content->contentInfo->mainLanguageCode,
                    $newLanguageCode
                )
            );

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Could not create translation.', ['exception' => $e]);

            return false;
        }
    }

    private function handleNotificationResponse($response): bool
    {
        // Status is always ok so not much treatment is expected
        if ($response->getStatusCode() === 200) {
            $this->logger->debug('OK ! :)');

            return true;
        }

        return false;
    }
}

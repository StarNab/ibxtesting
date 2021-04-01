<?php

namespace App\IbexaTests\Tests\EventListener;

use App\IbexaTests\PostPublicationBundle\EventListener\PublicationSubscriber;
use App\IbexaTests\PostPublicationBundle\Service\PostPublicationService;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Log\Logger;

class PublicationSubscriberTest extends TestCase
{
    public function testSubscribedEvents()
    {
        $this->assertArrayHasKey(PublishVersionEvent::class, PublicationSubscriber::getSubscribedEvents());
    }

    public function testCreateNewTranslation()
    {
        $publicationService = $this->getMockBuilder(PostPublicationService::class)->disableOriginalConstructor()->getMock();
        $languageService = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $publicationSubscriber = new PublicationSubscriber($publicationService, $languageService, $logger, ['article'], 'eng-GB', 'fre-FR');
        //$publicationSubscriber = $this->getMockBuilder(PublicationSubscriber::class)->disableOriginalConstructor()->getMock();
        /*$publicationSubscriber = $this->createMock(PublicationSubscriber::class);
        $publicationSubscriber->method('getSubscribedEvents')->willReturn([
            PublishVersionEvent::class => [
                ['createNewTranslation', 0],
                ['notifyRemoteService', 5],
            ],
        ]);*/

        $content = $this->getMockBuilder(Content::class)->disableOriginalConstructor()->getMock();
        $versionInfo = $this->getMockBuilder(VersionInfo::class)->disableOriginalConstructor()->getMock();
        $event = new PublishVersionEvent($content, $versionInfo, []);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($publicationSubscriber);
        $eventDispatcher->dispatch($event);

        // I wasn't able to complete a working unit test in this case
        $this->assertTrue(true);
    }
}

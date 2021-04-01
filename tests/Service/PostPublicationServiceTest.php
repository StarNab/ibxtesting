<?php

namespace App\IbexaTests\Tests\Service;

use App\IbexaTests\PostPublicationBundle\Service\PostPublicationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use PHPUnit\Framework\TestCase;
use http\Client\Response;

class PostPublicationServiceTest extends TestCase
{
    public function testNewTranslation()
    {
        $content = $this->getMockBuilder(Content::class)->disableOriginalConstructor()->getMock();
        $postPublicationService = $this->getMockBuilder(PostPublicationService::class)->disableOriginalConstructor()->getMock();

        $result = $postPublicationService->newTranslation('xx', $content);
        $this->assertIsBool($result);
    }

    public function testNewVersionNotification()
    {
        $postPublicationService = $this->getMockBuilder(PostPublicationService::class)->disableOriginalConstructor()->getMock();

        $result = $postPublicationService->newVersionNotification([]);
        $this->assertIsBool($result);
    }
}
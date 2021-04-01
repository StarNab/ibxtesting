<?php

namespace App\IbexaTests\PostPublicationBundle\EventListener;

use App\IbexaTests\PostPublicationBundle\Service\PostPublicationService;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent;
use eZ\Publish\API\Repository\LanguageService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PublicationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * @var PostPublicationService
     */
    private $postPublicationService;
    /**
     * @var LanguageService
     */
    private $languageService;
    /**
     * @var array
     * The published content content type needs to be one of those in this array to trigger the PostPublication events
     */
    private $targetClasses;
    /**
     * @var string
     * The published content needs to be in this $targetContentLanguage to trigger the PostPublication events defined here
     */
    private $targetContentLanguage;
    /**
     * @var string
     * The new version will be in translated in this language
     */
    private $newTranslationCode;
    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(PostPublicationService $postPublicationService, LanguageService $languageService, array $targetClasses, string $targetContentLanguage, string $newTranslationCode)
    {
        $this->postPublicationService = $postPublicationService;
        $this->languageService = $languageService;
        $this->targetClasses = $targetClasses;
        $this->targetContentLanguage = $targetContentLanguage;
        $this->newTranslationCode = $newTranslationCode;
    }

    public static function getSubscribedEvents(): array
    {
        return [PublishVersionEvent::class => ['onNewVersion', 0]];
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function onNewVersion(PublishVersionEvent $event)
    {
        if ($this->hasNotifcation($event)) {
            $datas = ['name' => $event->getContent()->getName(),
                'contentId' => $event->getContent()->id,
                'locationId' => $event->getContent()->contentInfo->mainLocationId, //
                'languageCode' => $event->getVersionInfo()->initialLanguageCode, ];

            if ($this->postPublicationService->newVersionNotification($datas)) {
                $this->logger->info('Notification Sent', ['content_id' => $event->getContent()->id]);
            }
            if ($this->isNewLanguageAvailable()) {
                if ($this->postPublicationService->newTranslation($this->newTranslationCode, $event->getContent())) {
                    $this->logger->info('Content Translated', ['content_id' => $event->getContent()->id]);
                } else {
                    $this->logger->error('New translation could not be created', ['content_id' => $event->getContent()->id]);
                }
            } else {
                $this->logger->critical('New Target Language is not registered in Ibexa Instance. Please add required language', ['new_language_code' => $this->newTranslationCode]);
            }
        } else {
            $this->logger->debug('No notification');
        }
    }

    /**
     * Checks if currently published content matches the requirements
     * @param PublishVersionEvent $event
     * @return bool
     */
    private function hasNotifcation(PublishVersionEvent $event): bool
    {
        if (\in_array($event->getContent()->getContentType()->identifier, $this->targetClasses)) {
            if ($event->getVersionInfo()->initialLanguageCode === $this->targetContentLanguage) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if new language is available in current Ibexa instance.
     */
    private function isNewLanguageAvailable(): bool
    {
        $languages = $this->languageService->loadLanguages();
        foreach ($languages as $lang) {
            if ($lang->languageCode === $this->newTranslationCode) {
                return true;
            }
        }

        return false;
    }
}
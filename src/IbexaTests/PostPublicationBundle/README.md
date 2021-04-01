This bundle provides tools to extend publication process.

The first tool sends a notification through REST to a distant server.

The 2nd one automatically creates a translation for a given content type object.

Requirements
------------

- A running Ibexa Content Instance 3.x

- symfony/http_client bundle

Installation
------------
1. Unzip the PostPublicationBundle into your src directory
2. Declare the PostPublicationBundle into your bundles.php as follows :
   
    `App\IbexaTests\PostPublicationBundle\PostPublicationBundle::class => ['all' => true],`
3. Add declaration into your services.yaml file as follows :
   `app.ibexatests.postpublicationservice:
      class: App\IbexaTests\PostPublicationBundle\Service\PostPublicationService

   App\IbexaTests\PostPublicationBundle\EventListener\PublicationSubscriber:
   arguments:
      - "@app.ibexatests.postpublicationservice"
      - "@ezpublish.api.service.language"
      - %content_types%
      - %target_language_code%
      - %new_language_code%
   tags:
      - { name: kernel.event_subscriber }`

%content_types% : is an array of content types identifier. The event will be triggered only for objects of those content types
%target_language_code% : is the language code on which you want to trigger the event.
%new_language_code% : is the new language code of the version newly created

License
-------

PostPublicationBundle is licensed under the MIT license.
 
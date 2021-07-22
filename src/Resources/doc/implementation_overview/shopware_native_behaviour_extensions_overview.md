# Shopware native behaviour extensions
***

## StylaDefaultRouterDecorator
This class was added in order to add a possibility to decorate UrlGenerator. By default, the
only way to change it is to replace class with another one, but in this case there are could be conflicts
with another plugins that will do the same.

Unfortunately there was no way to use proper decoration here because of the typehint 
`Symfony\Bundle\FrameworkBundle\Routing\Router` in `Shopware\Storefront\Framework\Routing\Router`. In the
future if that typehint will be changed to `RouterInterface` this class should be refactored.

## StylaUrlGenerator
This class was added in order to support url generation to the Styla page that does not exist in the Shopware
it was required to avoid an error during the url generation in the scenarios when User switch language
from the Styla page that is not exist in the native Shopware.

## StylaPagesUrlProvider
This class was added in order to support generation of the sitemap URL for the imported Styla pages

## RestartConsumerAfterPluginConfigChangedEventSubscriber
This event listener is responsible for send consumer interruption signal in case if Styla plugin configuration 
was changed. It is necessary because Styla plugin configuration could be cached in the container in multiple places
(not only in the formant of the `Styla\CmsIntegration\Configuration\Configuration` instances but also in form of 
values in the property of the system configuration accessor service, check `Shopware\Core\System\SystemConfig\SystemConfigService::$configs`)

## StorefrontRequestControllerSubstituteEventSubscriber
This event listener is responsible for rendering a Styla page instead of the original page if it is needed
Service will use `Styla\CmsIntegration\UseCase\StylaPagesInteractor` to get appropriate `StylaPage` entity and render it using 
`Styla\CmsIntegration\Controller\Storefront\StylaPageController`. 
In case of any error happen during the execution, this service is responsible for 
loading native page and adding record to the logger

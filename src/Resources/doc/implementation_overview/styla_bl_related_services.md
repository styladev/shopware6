# Styla Business logic related services

## Overview

In general those classes are private and can not be used outside the plugin.
They are responsible for execution of the business logic related to interaction with Styla CMS

**Namespace:** `Styla\CmsIntegration\Styla`

### Styla CMS service API client

Those classes responsible for interaction with the Styla CMS API endpoints.

**Namespace:** `Styla\CmsIntegration\Styla\Client`

#### Most important classes definitions:

##### Client
This class represents an entry point to the functionality, it
is responsible to send requests to the Styla CMS server and translate responses to the
intermediate format. It is also responsible to log any error with the context because this
service has more execution and error contexts than caller.
To get a valid instance of this service, dependent service should use ClientRegistry that is
registered in the Symfony container.

##### ClientRegistry
This service is stateful and contains the list of the "Client" instances
configured to work with different accounts, there are will be single instance for each
account defined in the `StylaCmsIntegrationPlugin.config.accountNames` plugin configuration.
Registry contain a public method **getClientByAccountName** that could be used to get an appropriate
Client instance for the Styla CMS account

### Pages synchronization related services

Those classes responsible for synchronization of the Styla CMS pages and `StylaPage` entities 

**Namespace:** `Styla\CmsIntegration\Styla\Synchronization`

#### PagesListSynchronizationProcessor
Responsible for the execution of the Styla Pages Synchronization represented by `StylaPagesSynchronization` entity.
Processor can validate instance of `StylaPagesSynchronization` in order to skip stuck synchronization
in case if it was marked as stuck but actually was not executed because of Message Consumer Queue length

#### PageDataMapper
Instance of this class is used to map `GeneralPageInfo` DTO that contains Styla Server response intermediate representation 
into `StylaPage` entity.

#### CacheInvalidator
Responsible for Symfony HTTP cache invalidation and invalidation of page details cache in case if page was changed 
on the Styla side  

### Styla pages guessers
Instances of those classes are used to guess which `StylaPage` entity could be rendered instead of the shopware page.

**Namespace:** `Styla\CmsIntegration\Styla\Page\Guesser`

#### VirtualStylaPageToReplaceGuesser
Virtual proxy class, that will delegate work to th actual guesser that could guess `StylaPage` entity 
based on `ShopwarePageDetails` DTO. This class is stateful and contains all existing guessers, tagged by
`styla.cms_integration.styla_page_to_replace.guesser` tag. In case if multiple guessers support the page first one will be executed.
This functionality was added in order to simplify extending of the
page replacement logic, as an example Guessers could be used to:
  * disable possibility to override some page. To do this you can just add a guesser with higher priority and 
    return null in the `guessStylaPage` method
  * enable possibility to override some page by default. Just add another guesser

**List of guessers:**

* **CategoryPagesReplaceGuesser** - Supports only Category pages. This guesser will use original page path
  (Shopware make a redirect to the specific controller action for all category pages) to find appropriate Styla page
* **HomePageReplaceGuesser** - Supports only Home page. This guesser will work only for `frontend.home.page` route and 
  will find a `StylaPage` entity with an empty path
* **NoRouteFoundStylaPageGuesser** - Supports only 404 page. This guesser will work only in case if there are no
native Shopware page by this path, so any `StylaPage` entity with the same path could be loaded instead 
* **ConfiguredPagesReplaceGuesser** - Supports only pages configured in 
  `StylaCmsIntegrationPlugin.config.extraPagesAllowedToOverride` plugin configuration.

### API DTO translators
Instances of those classes are used to translate(or convert, especially I, like metaphor: "translate" so it is used everywhere) 
Shopware entities into the API DTO used by API use case interactors

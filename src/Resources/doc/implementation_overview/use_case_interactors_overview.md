# Use case interactors

## Overview

In general those classes are public and can be used outside the plugin.
They are responsible for execution of the high level feature use cases.
In other worlds they are an entry points to the plugin functionality

### Namespace
`Styla\CmsIntegration\UseCase`

## Available interactors

### StylaPagesSynchronizer

Responsible for execution of the use-cases related to the styla pages synchronization

Methods definition:

* **schedulePagesSynchronization** - Schedule `StylaPage` entities synchronization if it is possible.
  In case if there is an existing synchronization that was not finished method will
  throw special exception `SynchronizationIsAlreadyRunning`. Method will check if stuck synchronizations are present
  and mark them as stuck.

  Method supposed to be called on demand when client want to schedule pages
  synchronization out of the schedule
* **resetSynchronizationStatus** - Resseting any running or stuck synchronization if there is.
  In case if synchronization status on database is active but there are actually no process running and user wanted
  to forcefully clear the status, because default mark as failed for stuck status is 60 minutes.
* **scheduleAutomaticPagesSynchronizationIfNeeded** - Schedule `StylaPage` entities synchronization if 
  last finished synchronization was scheduled more than `StylaCmsIntegrationPlugin.config.pagesListSynchronizationInterval`
  minutes ago(check plugin configuration). Method will check if stuck synchronizations are present
  and mark them as stuck.

  Method supposed to be called once per minute by Shopware 6 scheduled tasks
  check for more details: `Styla\CmsIntegration\Async\StylaPagesListSyncScheduledTaskHandler::run`
* **synchronizeStylaPages** - Run actual synchronization of the `StylaPage` entities by `StylaPagesSynchronization` 
  entity ID. Method is responsible to log any error that was happened during the execution
  Method is responsible to mark synchronization as failed if any error happened

### StylaPagesInteractor

* **getPageDetails** - method returns Styla page details, it either receives page details from
  Style CMS server or from the cache
* **refreshPageDetails** - method warmups styla page details cache, so it will call
  Style CMS server for page details and save them to the cache
* **guessPageToReplaceShopwarePage** - method is responsible to guess which Styla page should be loaded
  instead of native shopware page

### ShoppingCartInteractor

* **addItemToCurrentUserCart** - Add an item to the current user cart.
  Responsible to log any exception that was happened during the method execution

### ProductApiActionsInteractor

* **findProducts** - Return list of products matched the criteria. Will return only
  simple products(without product variants) or container products(parent of found product variant)
  Returned value is JSON serializable DTO, that can be serialized to the structure specified
  in the [Styla documentation](https://docs.styla.com/product-data-for-styla) (Check "Search Endpoint" topic)

* **getProductDetails** - Return product details. Returned value is JSON serializable DTO, that can be serialized to
  the structure specified in the [Styla documentation](https://docs.styla.com/product-data-for-styla) (Check "Product Details Endpoint" topic)

### CategoryApiActionsInteractor

* **getCategoriesList** - Return list of categories. Will return only
  simple products(without product variants) or container products(parent of found product variants)
  Returned value is JSON serializable DTO, that can be serialized to the structure specified
  in the [Styla documentation](https://docs.styla.com/product-data-for-styla) (Check "Search Endpoint" topic)

# Services responsible for Message queue interaction
***
## Overview

In general those classes needed in order to use Message Queue implementation of the Symfony/Shopware
most of them are responsible for execution of the plugin asynchronous functionality

**Namespace:** `Styla\CmsIntegration\Async`

## Services definition

### StylaPagesListSynchronizationMessageHandler
Message handler for `StylaPagesListSynchronizationMessage`.
Responsible for the execution of the pages synchronization scenario.
Delegates most of the work to `Styla\CmsIntegration\UseCase\StylaPagesSynchronizer`([check for more details](./use_case_interactors_overview.md))

### StylaPagesListSyncScheduledTaskHandler
Responsible for handling `StylaPagesListSyncScheduledTask` message. It is a descendant of the
`Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler` because it is required in order to work with
Shopware scheduled tasks. `StylaPagesListSyncScheduledTask` is configured to run every minute and schedule is checked 
by the `Styla\CmsIntegration\UseCase\StylaPagesSynchronizer`. Native Shopware schedule was not used because of the 
bug in `Shopware\Core\Framework\MessageQueue\ScheduledTask\Scheduler\TaskScheduler`
(more details in the comment in the class), in case if bug will be fixed in the future versions of the Shopware,
consider to refactor this approach

### StylaPagesSyncOldRecordsClearTaskHandler
Responsible for handling `StylaPagesSyncOldRecordsClearTask`. Responsible for clear old `StylaPagesSynchronization`
records. It is a descendant of the
`Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler` because it is required in order to work with
Shopware scheduled tasks. Executed once per day

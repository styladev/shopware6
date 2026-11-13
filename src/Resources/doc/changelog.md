## Changelog

### v1.4.5

* Security: the Styla admin API routes (page synchronization, page refresh, synchronization status) require admin API authentication; the status reset is a POST request
* Security: removed the `_stylaDebug` request parameter that dumped request data
* Fix absolute URLs to Styla pages on Shopware 6.7 (`RequestStack::getMasterRequest()` was removed)
* Fix HTTP cache invalidation: Styla page responses are tagged per page and storefront pages per path; a page synchronization and "refresh details" invalidate them through Shopware's cache invalidator, including a Shopware page cached under the path of a new Styla page
* Styla pages replace Shopware pages also when the paths only differ by a trailing slash (e.g. category URL `/Clothing/` and Styla path `/Clothing`)
* Fix the Slot ID placeholder in the Shopping Experiences element preview
* Add to cart from Styla pages goes through Shopware's cart service; `id` and `quantity` are validated (quantity defaults to 1)
* Styla Store API: `limit` is capped at 100; categories only from the sales channel's main navigation tree (footer and service navigation are no longer returned; `limit`/`offset` no longer apply) and active only; product details only for active products visible in the sales channel; search terms are limited to 10 words / 100 characters
* Logs no longer contain the sales channel context (context token)
* Timeouts for requests to the Styla APIs (3s connect, 10s total)
* Use Symfony route attributes and explicit nullable types (PHP 8.4)

### v1.4.4

* Fix Store API routes answering with 500 (e.g. routes with a `Criteria` argument): the Styla page argument resolver now only resolves `StylaPage` arguments
* Fix Styla pages rendered for unknown URLs redirecting (301) to themselves on Shopware 6.7
* Fix scheduled task `StylaPagesSyncOldRecordsClearTask` failing on Shopware 6.7 (missing logger in handler constructor)
* Register the Styla scheduled task handlers explicitly for their own task classes

### v1.4.3

* Fix modular content slot ID field in Shopping Experiences for Shopware 6.7 (`sw-field` → `mt-text-field`)

### v1.3

* Support up to shopware core v6.6.10.1
* Upgrade shopware core to v6.6.10.1
* Fixed routing annotation formatting
* Fixed wrong api path
* Change bundle name to `StylaCmsIntegration`
* Fixed sync status update race condition
* Local dev setup documentation

* Bugfix and improvement

### v1.2

* Support up to shopware core v6.4
* Bugfix and improvement
<br /><br />
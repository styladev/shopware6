Capabilities and Restrictions
==============================
***

At this moment plugin provides next integrations with Styla:

* Integration with the Styla pages
* Integration with Styla Modular Content
* Integration with "Add to Cart" functionality

### Integration with the Styla pages

#### Overview
Plugin is responsible to synchronize the list of pages that was published in the Styla accounts
configured in the plugin configuration. Those pages will be shown on the Shopware "storefront" by the
path set in the [Styla Editor](https://editor.styla.com/)

You could review the list of all synchronized pages in the administration menu `Content => Styla CMS => Styla Pages`
or by direct link `http(s)://{your shopware application domain}/admin#/styla/cms/integration/styla/page`

##### Restrictions:
* Styla pages will be loaded only on the default Storefront(Sales channel with the "Storefront" type).
  Other sales channels are not supported for now
* Sometimes Styla page path is the same as native shopware page path, in this case native shopware page will be loaded
except of the next cases:
    * Shopware page is the "Home" page
    * Shopware page is the "Category" page
    * Page path exists in the plugin configuration("List of Shopware paths the plugin can override")
* Styla pages could not be unpublished from the Shopware for now so you will not be able to remove the
page specifically for shopware application


### Integration with Styla Modular Content

#### Overview
Plugin provides specific CMS block and CMS element for Shopware page editor. Using those
block and/or element you could render Styla module content inside shopware pages. 
You could also render multiple Styla module content blocks inside the single Shopware page

##### Restrictions:
* You could add Styla module content only into the pages that could be edited with the native shopware page editor
* All module content blocks on the same page should be created in the same account

### Integration with "Add to Cart" functionality

#### Overview
In the scope of this plugin endpoint `http(s)://{domain}/styla/cart/add` was implemented according to the
[specification](https://docs.styla.com/product-data-for-styla) (check "enable "add to cart" functionality" topic)

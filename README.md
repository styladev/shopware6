# Styla Shopware 6 extension

## Table of Contents  
[About the plugin](#about-the-plugin)   
[Installation (on-premise)](#installation-on-premise)   
[Activation](#activation)   
[Configuration](#configuration)   
[Creating pages in Styla Editor](#creating-pages-in-styla-editor)   
[How does syncing work](#how-does-syncing-work)     
[Debugging](#debugging)   
[Modular Content](#modular-content)    

## About the plugin

The plugin lets you automatically include Styla content created with [Styla Editor](https://editor.styla.com/)(an external CMS) on your Shopware 6 frontend. [Styla Pages](https://docs.styla.com/styla-pages) are included automatically as new Shopware pages and can take over some Shopware paths. [Styla Modular Content](https://docs.styla.com/styla-modular-content) can be added to Shopware page templates manually by using the Shopping Experiences editor available in Shopware backend. 

In addition, the plugin provides product data over an API with three endpoints so that products from S6 can be used in Styla content created in Styla Editor. It also provides one additional endpoint to add products from Styla content to Shopware cart.  

## Installation (on-premise)

Upload the plugin file downloaded from this GitHub repository (the **Releases** tab) to the location where all plugin files are stored.

**IMPORTANT:** The plugin has not been tested on a Shopware 6 hosted in a public/private cloud, so it's not covered in these instructions. 

## Activation

Once you upload the plugin files, it becomes visible on the **My Extensions** list. To activate it, simply turn the switch on the left **ON**.  

## Configuration

Click the **…** button on the right of the extensions list and then **Configuration** to set up the plugin. 

On the resulting form with the plugin settings do the following: 

1. Select the Sales Channel to use Styla on. We suggest to leave it set to **All Sales Channels** as per default.
2. Enter **Default Account Name** that you should have received from your Styla Customer Success Manager or Integration Manager. Styla account names usually look like this: `account-name-en-gb`. This account name will define from which account your Styla pages will be synced to the Shopware frontend. This should be the same account you can see in your Styla Editor URL: `https://editor.styla.com/account-name-en-gb/pages`.
3. If you have multiple language/country versions on your Shopware, override the default Styla account for each of them by filling the **{Locale} Account Name** with respective Styla account names. The plugin will source content for this Shopware locale from a different Styla account.
4. Fill in the **List of overridable pages**. Per default Styla plugin can create new paths on Shopware routing and override home pages and category pages. This will happen if you publish in Styla Editor a page matching the Shopware path. However, this will not happen for any other Shopware path (product details, the cart etc) unless you put it on this list. Add relative paths of such pages separated by new line.
5. If need be, update the **Interval of the Pages list synchronisation** field. This setting defines how often the plugin picks up page updates from a Styla API endpoint. This in turn triggers page content update. The default 10 minutes give you a solid balance between the speed and using your resources but if you want to get your Styla content updated as soon as possible and don’t care about your resources usage, decrease the delay. 
6. If need be, update the **Page details cache duration** field. The default 60 minutes give you a solid balance between the speed and using your resources but if you want to cache page content for a shorter time and and don’t care about your resources usage, decrease the delay.

SCREENSHOT 

Click the **Save** button every time you change the settings and want the plugin to use them. 

Every time you **change Styla account names**, the plugin **will remove all Shopware pages** synced from this specific Styla account. So they will not be visible on the Shopware frontend until you re-add the same account name. 

**IMPORTANT**: If you want to have your Shopware products available in Styla content, please pass the **API access key** value to your Styla Integration Manager. You can find it in your Shopware backend in **Sales Channels > Name > API access**. A Styla app will use this credential to access an API providing your product data. 

SCREENSHOT  

## Creating pages in Styla Editor

You need to create and publish the content you want the plugin to sync in the Styla Editor at https://editor.styla.com/. What you enter in the **Page URL path** in Styla page’s settings will define where your page will show up when synced to your Shopware. If a specific path is already taken by an Shopware path other than the home page or a category page and you don’t have the path in the **List of overridable pages** in the plugin settings, your Styla page won’t show up.

**IMPORTANT:** If you publish a page with **a blank path**, this page **will override your default Shopware home page**. If you then unpublish this Styla page or change its path, the default Shopware home page will come back after a delay related to the sync process. This logic also applies to other paths serving native Shopware content. They will appear again if you unpublish Styla pages matching their paths. 

## Styla pages list

You can always check list of your Styla pages synced to your Shopware in **Content > Styla CMS > Styla Pages**. The list shows them with title, path, Styla account name from which they are synced and update time stamp. In general, this list should include all pages you have published with Styla Editor for this account - unless some of them had not been synced/updated yet due to queuing/caching times. 

The list is actionable too. You can do the following:
* click the **Schedule pages synchronization** top of the list to force the page synced, overriding the interval set per default to 10 minutes,
* click **Page path** to open the page directly on your Shopware frontend,
* click the **…** button on the right to force updating a specific page, overriding the caching time set per default to 3600 seconds.
The list is only updated on page reload. It does not reflect automatically a Styla page update.

## How does syncing work

The plugin picks up page updates from this API endpoint: `https://paths.styla.com/v1/delta/${accountName}`. The endpoint reflects page content updates done with Styla Editor. Once a page is updated it moves to the top of the list with updated metadata fields. There is up to 15 minutes delay in this process alone.

The update on the above endpoint triggers checking for new page content (unless unpublished). Page content is synced from another API endpoint: `https://seoapi.styla.com/clients/${accountName}?url=${pagePath}`. This content is rendered on a matching Shopware path and cached. Resulting Shopware pages with Styla content include a Styla script from https://engine.styla.com/init.js which adds some functionality to the static HTML content. 

## Debugging

If you don’t see your Styla pages published on your Shopware frontend, try debugging in this sequence:

* Check if the plugin is activated,
* Check if you have Styla accounts entered correctly in the plugin's settings,
* Check if your updates done in the Editor are reflected by the Styla API endpoints above (allow 15 minutes for this to happen),
* Decrease your syncing interval and caching time,
* Check your Shopware error logs, especially if error messages appear in the backend,
* Check for conflicts with other Shopware extensions or with customisations like any out-of-the-box caching,
* Describe your problem in details, attach screenshots, include steps to reproduce and pass to your Styla Integration Manager or to support@styla.com.

**IMPORTANT:** Please test any potentially breaking changes on your **local/test/stage environment before releasing them on your production**. Especially, check if Styla content is still visible. Do this before updating Shopware version too. 

## Modular Content

The plugin enables you to include pieces of Styla content inside Shopware full-page content too. As opposed to syncing Styla Pages, this is not a Styla full-page content and is rendered with JavaScript only, not server-side as a static HTML. 

In order to include Modular Content, please:
1. Create your pieces of Modular Content in Styla Editor and assign them Slots, as described [in Styla documentation here](https://docs.styla.com/styla-modular-content).
2. Go to **Content > Shopping Experiences** in your Shopware backend and enter a layout you want to add a Modular Content on.
3. Click the **+** icon in the right sidebar and drag&drop the **Styla Module Content** (bottom of the list) widget on your content.
4. Once on the list, click **the cog icon** top-right of the widget and enter the **Slot Id** you get from your **Styla Editor > Slots Manager**. Alternatively, you can click the **replace** icon next to the cog and select the **Styla Modular Content** from the elements list. You can do this wherever the Shopware's rich text editor is used. 
5. Publish the changes. From this moment on all pages using this specific layout will include the Styla https://engine.styla.com/init.js script rendering Styla content based on the `<div data-styla-slot="${slot-id}"></div>` tag rendered in the page’s HTML.

SCREENSHOT

This way you can have a Styla slot included on a thousand category pages at once. You can still define a different content for each path on which the slot is included using the **Custom Path** field for your slot in Styla Editor.

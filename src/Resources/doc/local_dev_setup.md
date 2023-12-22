Local Dev Environment Setup
===============================
***

To be able to setup and run local dev environment using dockware please follow this instructions:

1. Install docker desktop
2. Clone [this shopware6](https://github.com/styladev/shopware6) repo to `shopware6`
3. Create another directory `shopware6-dockware`
4. Create new `docker-compose.yml` file and use this config:

```
version: "3"

services:

    shopware:
      # use either tag "latest" or any other version like "6.5.3.0", ...
      image: dockware/dev:latest
      container_name: shopware
      ports:
         - "80:80"
         - "3306:3306"
         - "22:22"
         - "8888:8888"
         - "9999:9999"
         - "443:443"
      volumes:
         - "db_volume:/var/lib/mysql"
         - "shop_volume:/var/www/html"
         - "../shopware6:/var/www/html/custom/plugins/shopware6"
      networks:
         - web
      environment:
         # default = 0, recommended to be OFF for frontend devs
         - XDEBUG_ENABLED=1
         # default = latest PHP, optional = specific version
         - PHP_VERSION=8.1

volumes:
  db_volume:
    driver: local
  shop_volume:
    driver: local

networks:
  web:
    external: false

```

Pay attention, the important part is mapping:
```
- "../shopware6:/var/www/html/custom/plugins/shopware6"
```

Then from inside `shopware6-dockware` run the command `docker compose up`
When the docker is up, you should be able to login to Shopware6 admin

* Shop URL: [http://localhost](http://localhost)
* Admin URL: [http://localhost/admin](http://localhost/admin)<br />
User: `admin`<br />
Password: `shopware`<br />
* MySQL URL: [http://localhost/adminer.php](http://localhost/adminer.php)<br />
User: `root`<br />
Password: `root`<br />
Port: `3306`
* All credentials listed here:
[https://docs.dockware.io/use-dockware/default-credentials](https://docs.dockware.io/use-dockware/default-credentials)

For plugin installation on shopware6: [Installation instruction](./Resources/doc/installation.md)

### Using HTTPS

To use https on your local, just add port `- "443:443"`, there will be warning on your browser but it's safe to ignore.

### Using Ngrok to expose your local Shopware publicly

To expose your local Shopware publicly, you can use [ngrok](https://ngrok.com/).
There will be an issue with admin panel if you just do `ngrok http 80`, to avoid this please follow this steps:

1. Use `https`` instead of `http`` otherwise you'll get `insecure https from http` kind of error. Follow above step.
2. Install ngrok
3. Open terminal and run `ngrok http https://localhost`
4. Copy the newly generated ngrok url

Now we need to use ngrok url on the sales channel so we don't run into this error:
```
Unable to find a matching sales channel for the request: "https://b50f-110-137-194-52.ngrok-free.app/". Please make sure the domain mapping is correct.
```
To fix this:
1. Go to http://localhost/adminer.php to open mysql database editor
2. Click `select sales_channel_domain`
3. Find the one with url `localhost` and replace with ngrok generated tunnel url (for eg. `https://b50f-110-137-194-52.ngrok-free.app`) DON'T ADD TRAILING SLASH
4. Try opening the ngrok url again

### Testing Styla API endpoint `/styla/page/render`

Render method is called from a job queue.
To quickly test how the render works, please follow these steps:
* On `public_services.yml` file, on the `Styla\CmsIntegration\Controller\Storefront\StylaPageController:` -> `arguments:`<br />
  add `- '@styla_cms_page.repository'`
* On `StylaPageController.php` add these uses:
  * `use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;`
  * `use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;`
* Declare these variables:
  * `private EntityRepository $pageRepository;`
* On the `__construct` param,<br />
  add `EntityRepository $stylaPageRepository`<br />
  and inside also add `$this->stylaPageRepository = $stylaPageRepository;`
* Comment out `StylaPage $stylaPage` and add these as replacement of getting styla page object:
```
$criteria = new Criteria();
$criteria->setLimit(1);
$stylaPage = $this->stylaPageRepository->search(
    $criteria,
    $context->getContext()
)->getEntities()->first();
```

<br /><br />
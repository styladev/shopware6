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
<br /><br />
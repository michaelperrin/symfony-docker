Docker configuration to run a Symfony app.

## Run

    docker-compose up -d
    docker-compose run --rm composer install

You can now visit http://localhost:8080 and you will see the default Symfony page.

## Useful commands

Symfony commands:

    docker-compose exec php bin/console


## Included stuff

### Nginx

The official Nginx image is used with a slight addition for permissions.

The virtual host is defined in the [app.conf](docker/nginx/app.conf) file. This file is shared with the container
as defined in the `docker-compose.yml` file. I prefer to share the host like this instead of using `COPY` in the Dockerfile
as there is no need to build a new image if the host changes. A simple `docker-compose up -d` will take
the new host into account.

### PHP-FPM

PHP-FPM 7.1 is installed with the following configuration:

* Necessary extensions for Symfony
* MySQL
* GD
* Composer
* PHPUnit
* Xdebug


### Xdebug

Making Xdebug working with Docker is quite tricky as there is currently a limitation to Docker for Mac
that prevents a container to make a request to the the host, which is exactly what we would like to do with Xdebug.

To make Xdebug debugging work, you will first need to run this command on the host:

    sudo ifconfig lo0 alias 10.200.10.1/24

This IP address is configured in the environment variables of the PHP container, in the `docker-compose.yml` file:

    services:
      php:
        # ...
        environment:
          XDEBUG_REMOTE_HOST: 10.200.10.1

No extra configuration is needed in your IDE (tested on Sublime Text, Visual Studio Code and Atom), apart the usual.

Interesting resources:

* https://docs.docker.com/docker-for-mac/networking/#/use-cases-and-workarounds
* http://blog.arroyolabs.com/2016/10/docker-xdebug/
* http://joenyland.me/blog/debug-a-php-app-in-a-docker-container-using-xdebug/


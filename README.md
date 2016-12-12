Docker configuration to run a Symfony app.

## Run

    docker-compose up -d

You can now visit http://localhost and see the default Symfony page.

## Useful commands

Symfony commands:

    docker-compose exec php bin/console


## Included stuff

### Xdebug

Making Xdebug working with Docker is quite tricky as there is currently a limitation to Docker for Mac
that prevents a container to make a request to the the host, which is exactly what we would like to do with Xdebug.

To make Xdebug debugging work, you will first need to run this command:

    sudo ifconfig lo0 alias 10.200.10.1/24

This IP address is configured in the environment variables of the PHP container (see `docker-compose.yml` file).

No extra configuration is needed in your IDE (tested on Sublime Text, Visual Studio Code and Atom), apart the usual.

Interesting resources:

* https://docs.docker.com/docker-for-mac/networking/#/use-cases-and-workarounds
* http://blog.arroyolabs.com/2016/10/docker-xdebug/
* http://joenyland.me/blog/debug-a-php-app-in-a-docker-container-using-xdebug/


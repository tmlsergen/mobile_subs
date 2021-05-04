# mobile subscription

## Installation
1. Clone Project
> ``
2. Copy .env file
> `cp app/.env.example app/.env`
3. Starting Docker Containers
> `docker-compose up`
4. You can see the working containers with `docker ps`
```
CONTAINER ID   IMAGE                                                 COMMAND                  CREATED        STATUS       PORTS                                            NAMES 
46ba07424176   mobile-subscription_app                               "docker-php-entrypoi…"   37 hours ago   Up 2 hours   9000/tcp                                         mobile-subscription_app_1
54072082de34   mobile-subscription_node                              "docker-entrypoint.s…"   37 hours ago   Up 2 hours   0.0.0.0:3000->3000/tcp                           mobile-subscription_node_1
1ed509ef683c   docker.elastic.co/elasticsearch/elasticsearch:7.9.0   "/tini -- /usr/local…"   2 days ago     Up 2 hours   0.0.0.0:9200->9200/tcp, 0.0.0.0:9300->9300/tcp   mobile-subscription_elasticsearch_1
18a9415f7cf5   nginx:alpine                                          "/docker-entrypoint.…"   2 days ago     Up 2 hours   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp         mobile-subscription_webserver_1
b9cb86b2301e   redis:latest                                          "docker-entrypoint.s…"   2 days ago     Up 2 hours   0.0.0.0:6379->6379/tcp                           mobile-subscription_redis_1
7b4caabb0d86   mysql:latest                                          "docker-entrypoint.s…"   2 days ago     Up 2 hours   0.0.0.0:3306->3306/tcp, 33060/tcp                mobile-subscription_db_1

```
5. To install composer dependencies
> `docker exec -ti app_container_id composer install`
6. Generate the laravel app key
> `docker exec -ti app_container_id php artisan key:generate`
7. To install node container (mock) dependencies
>  `docker exec -ti node_container_id npm install`
8. You can access container bash with
> `docker exec -ti app_container_id /bash`

# Environments
.env db connection on container
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=mobile_subs
DB_USERNAME=root
DB_PASSWORD=root
```

If you have installed mysql server on your local you can create a
`docker-compose.override.yml` file to change docker container configs
```
version: '3'

services:
  #MySQL Service
  db:
    image: mysql:5.7.33
    restart: unless-stopped
    tty: true
    ports:
      - "4306:3306"
    environment:
      MYSQL_DATABASE: mobile_subs
      MYSQL_ROOT_PASSWORD: root
      SERVICE_TAGS: dev
      SERVICE_NAME: mysqldb
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - mobile-sub-network

```

When you start the containers add the line below on `/etc/hosts` file for local development
>`127.0.0.1       mobile-subs.test`
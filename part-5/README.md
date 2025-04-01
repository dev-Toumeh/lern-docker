## create php:apache-bookworm container
```sh
docker create --network my-network --name my-server -v /home/naseem91/my-second-project/:/var/www/html -p 8090:80 php:apache-bookworm
```

## create mariadb container
```sh
docker create --network my-network --name mariadb -v db:/var/lib/mysql -p 3306:3306 -e MARIADB_ROOT_PASSWORD=1234 -e MARIADB_DATABASE=todo -e MARIADB_USER=my-user -e MARIADB_PASSWORD=1234 mariadb
```

## copy 000-default.conf from the container to the Host
```sh
docker cp my-server:/etc/apache2/sites-available/000-default.conf /path-to-the-project/apache/000-default.conf
```

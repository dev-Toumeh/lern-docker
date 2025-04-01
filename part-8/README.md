## Start services in detached mode
```sh
docker compose up -d
 ```
## Stop running services
```sh
docker compose stop
```
## Stop and remove containers, networks, and volumes (if defined)
```sh
docker compose down
```
## build all the services
```sh
docker compose build 
```
## build all services without a cache
```sh
docker compose build --no-cache
```
## Build a specific service
```sh
docker compose build <service-name>
```
## Open an interactive shell inside a running container
```sh
docker compose exec <service-name> /bin/bash
```
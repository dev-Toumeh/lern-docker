## Start services in detached mode with prefix
```sh
docker compose -p <your-prefix> up -d
 ```
## Stop running services with prefix
```sh
docker compose -p <your-prefix> stop
```
## Stop and remove containers, networks, and volumes (if defined) with prefix
```sh
docker compose -p <your-prefix> down
```
## build all run all the services with prefix
```sh
docker compose -p <your-prefix> up
```
## Build a specific service with prefix
```sh
docker compose -p <your-prefix> build <service-name>
```
## Open an interactive shell inside a running container with prefix
```sh
docker compose -p <your-prefix> exec <service-name> /bin/bash
```

# Development Guide

A Docker Compose development setup is available, based around the [bitnami/opencart](https://hub.docker.com/r/bitnami/opencart)
image.

## Starting Up

First, you need to start the Docker containers:

```shell
docker compose up -d
```

You can now view OpenCart on http://localhost, and the admin portal can be found on http://localhost/administration.

Default credentials:

- Username: `admin`
- Password: `parcelpro1`

## Installing the Module

The easiest way to install or update the module is by running `./install.sh`.
This will uninstall and remove the old module (if present), and build, upload and install the new module (from the sibling directory).
Note that this script requires [htmlq](https://github.com/mgdm/htmlq) on your machine.

## CLI in Docker

To get a shell in the running OpenCart container, run `./cli.sh`.

## Shutting Down

Stop the containers, but keep their data:

```shell
docker compose stop
```

Stop and remove the containers and volumes:

```shell
docker compose down -v
```

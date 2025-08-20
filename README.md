# Symfony 6 REST Example (Dockerized)

A minimal, **working** Symfony 6 REST API example with caching, DTO/Enum, service layer,
event **subscriber** + **listener**, and a `services.yaml` parameter. Fully Dockerized (PHP + Postgres + Redis).

## What’s included
- **Symfony 6.4** (installed at container start via Composer)
- **REST API** for `Task` entity (list, show, create)
- **Cache** via Redis (used for task list)
- **Classes**: Controller, Entity, Repository, Service, DTO, Enum
- **Event Subscriber** (adds `X-Request-Id` header)
- **Event Listener** (`TaskCreatedEvent` invalidates cache and logs)
- **services.yaml** loads parameter: `app.default_page_size`
- **Composer** used to bootstrap and install deps inside the container
- **Complete Docker setup** (PHP app server using built-in PHP server, Postgres, Redis)

## Quick start

```bash
# 1) Start everything (first run will download Composer deps & set up the DB)
docker compose up --build

# 2) The API will be available at:
#    http://localhost:8080

# 3) Example requests
curl http://localhost:8080/api/tasks

curl -X POST http://localhost:8080/api/tasks   -H "Content-Type: application/json"   -d '{"title":"Learn Symfony","description":"Follow the example project","status":"pending"}'

curl http://localhost:8080/api/tasks/1
```

## Services
- App (PHP 8.2): http://localhost:8080
- Postgres: localhost:5432 (container: `db`, user: `app`, pass: `app`, db: `app`)
- Redis: container `redis` on default port

## Project layout
```
symfony6-rest-example/
├─ docker/
│  └─ php/
│     ├─ Dockerfile
│     └─ entrypoint.sh
├─ config/         # Symfony config overlays copied into the container
├─ migrations/     # Doctrine migration
├─ src/            # Your application code
├─ .env            # Environment overrides for Symfony
└─ docker-compose.yml
```

> The container bootstraps a fresh Symfony 6.4 skeleton **inside** `/var/www/html`, requires needed packages,
> copies the code from `src/`, `config/`, and `migrations/`, runs migrations, then serves via PHP's built-in server.

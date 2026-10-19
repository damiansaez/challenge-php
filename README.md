# Challenge PHP: Gestion de productos

API REST en **PHP 8.3** para administrar productos, con precios en pesos y su conversión a dólares, más un frontend en HTML, CSS y JavaScript que la consume con `fetch`. Todo corre con Docker.

- **Backend:** PHP 8.3 + Apache, MySQL 8.0, PDO, Composer (solo para el autoload PSR-4).
- **Frontend:** HTML, CSS y JavaScript (módulos ES, `fetch`, sin librerías).

## Requisitos

- Docker y Docker Compose v2 (`docker compose`).
- Puertos libres: **8080** (app) y **3307** (MySQL, para conectarse desde el host).

## Cómo levantar el proyecto

```bash
git clone https://github.com/damiansaez/challenge-php.git
cd challenge-php
cp .env.example .env
docker compose up -d --build
```

En el primer arranque:

1. MySQL crea la base y ejecuta `docker/mysql/init.sql`, que crea la tabla `productos`.
2. El contenedor `app` espera a que MySQL esté sano (*healthcheck*), ejecuta `composer install` y arranca Apache.

Verificar que esté arriba:

```bash
curl http://localhost:8080/health
# {"data":{"status":"ok"}}
```

| Qué | URL |
| --- | --- |
| API | http://localhost:8080/productos |
| Frontend | http://localhost:8080/app/ |
| MySQL (desde el host) | `localhost:3307`, usuario y contraseña del `.env` |

Comandos útiles:

```bash
docker compose logs -f app          # logs de Apache y errores de PHP
docker compose down                 # detener
docker compose down -v              # detener y borrar la base (se recrea desde init.sql)
```

## Variables de entorno

Se definen en `.env` (copiado de `.env.example`). Docker Compose lee ese archivo y pasa los valores como variables de entorno a los contenedores; PHP los lee con `getenv()`.

| Variable | Ejemplo | Descripción |
| --- | --- | --- |
| `DB_HOST` | `db` | Host de MySQL (nombre del servicio en Docker) |
| `DB_PORT` | `3306` | Puerto de MySQL dentro de la red de Docker |
| `DB_DATABASE` | `challenge` | Nombre de la base |
| `DB_USERNAME` | `challenge` | Usuario de la aplicación |
| `DB_PASSWORD` | `secret` | Contraseña del usuario |
| `DB_ROOT_PASSWORD` | `rootsecret` | Contraseña de root de MySQL |
| `PRECIO_USD` | `1535.72` | Cotización del dólar: cuántos pesos vale 1 USD |

### `PRECIO_USD`

Es el valor que usa la capa de lógica para calcular `precio_usd` de cada producto. Debe ser un número **positivo con punto decimal**: `1535.72`, no `1.535,72`.


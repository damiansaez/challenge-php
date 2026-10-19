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

## Endpoints

- `GET /productos` lista todos los productos
- `GET /productos/{id}` devuelve un producto
- `POST /productos` crea un producto (responde 201)
- `PUT /productos/{id}` actualiza un producto
- `DELETE /productos/{id}` elimina un producto (responde 204)

El body de POST y PUT es un JSON con `nombre`, `descripcion` (opcional) y `precio` en pesos. Cada producto se devuelve con su `precio_usd` calculado.

Si algo falla, la respuesta viene con el código que corresponde (400, 404, 405, 422 o 500) y un mensaje en `error`. En los 422 además se indica qué campo falló.

## Cómo probar la API

Se puede probar con curl o con Postman.

```bash
# Crear un producto
curl -X POST http://localhost:8080/productos \
  -H "Content-Type: application/json" \
  -d '{"nombre": "Mate", "descripcion": "De calabaza", "precio": "49999.99"}'

# Listar todo los productos
curl http://localhost:8080/productos

# Obtener un producto en detalle
curl http://localhost:8080/productos/1

# Actualizar un producto
curl -X PUT http://localhost:8080/productos/1 \
  -H "Content-Type: application/json" \
  -d '{"nombre": "Mate imperial", "precio": "75000"}'

# Eliminar un producto
curl -X DELETE http://localhost:8080/productos/1
```

Para ver los errores de validación se puede mandar algo inválido, por ejemplo el nombre vacío o el precio con coma:

```bash
curl -X POST http://localhost:8080/productos \
  -H "Content-Type: application/json" \
  -d '{"nombre": "", "precio": "12,50"}'
```

## Frontend

El frontend está en `public/app` y lo sirve el mismo contenedor de PHP, así que no hay que levantar nada aparte. Con el proyecto corriendo, entrar a http://localhost:8080/app/

Desde ahí se pueden listar, agregar, editar y eliminar productos. Los precios se muestran en pesos y en dólares.

Para probarlo:

1. Agregar un producto y ver que aparece en la tabla con su precio en USD.
2. Mandar el formulario vacío o con un precio inválido y ver los mensajes de error en cada campo.
3. Editar un producto y guardar.
4. Eliminar un producto (pide confirmación antes).
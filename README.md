# Mesa de Ayuda

Aplicación Laravel para registrar y dar seguimiento a servicios de soporte técnico, dispositivos, responsables y actividades.

## Requisitos

- PHP 8.3 o posterior con `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo` y `curl`.
- Composer 2.
- Node.js 22 y npm.
- PostgreSQL 15 o posterior para desarrollo normal.
- Postman Desktop o Postman CLI para las pruebas manuales de seguridad.

Comprueba las versiones antes de continuar:

```bash
php --version
php -m
composer --version
node --version
npm --version
psql --version
```

### Windows 10/11

1. Instala PHP 8.3 o posterior, Composer, Node.js 22 LTS y PostgreSQL desde sus instaladores oficiales. Durante la instalación de PostgreSQL conserva la contraseña del usuario administrador `postgres`.
2. Localiza el `php.ini` que usa la terminal con `php --ini` y habilita, quitando el `;` inicial, las extensiones `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo` y `curl`.
3. Cierra y abre PowerShell, y verifica los comandos de la sección anterior.

Laragon también es válido si su terminal utiliza PHP 8.3 o posterior y se habilitan las mismas extensiones. PostgreSQL debe instalarse por separado si el paquete elegido no lo incluye.

### Debian/Ubuntu

En una versión cuya paquetería ofrezca PHP 8.3 o posterior:

```bash
sudo apt update
sudo apt install php php-cli php-pgsql php-sqlite3 php-mbstring php-xml php-curl php-zip composer postgresql postgresql-contrib nodejs npm
sudo systemctl enable --now postgresql
```

Si `php --version` o `node --version` son anteriores a las versiones requeridas, instala PHP desde un repositorio mantenido para tu versión de Debian/Ubuntu y Node.js 22 desde el instalador oficial de Node.js antes de ejecutar Composer o npm.

### Arch Linux/CachyOS

```bash
sudo pacman -Syu php php-pgsql php-sqlite composer postgresql nodejs npm
```

En una instalación nueva de PostgreSQL, inicializa el clúster una sola vez y luego inicia el servicio:

```bash
sudo -iu postgres initdb --locale="$LANG" -D /var/lib/postgres/data
sudo systemctl enable --now postgresql
```

No vuelvas a ejecutar `initdb` si `/var/lib/postgres/data` ya contiene un clúster.

## Base de datos PostgreSQL

En Linux abre PostgreSQL con `sudo -u postgres psql`. En Windows abre **SQL Shell (psql)** o ejecuta `psql -U postgres`. Crea un usuario y dos bases separadas:

```sql
CREATE USER servicetracker_user WITH PASSWORD 'CAMBIA_ESTA_CLAVE_LOCAL';
CREATE DATABASE servicetracker OWNER servicetracker_user;
CREATE DATABASE servicetracker_postman OWNER servicetracker_user;
\q
```

- `servicetracker`: desarrollo normal.
- `servicetracker_postman`: pruebas manuales que pueden borrar y recrear datos.

Si el usuario ya existe, no repitas `CREATE USER`; actualiza su contraseña con `ALTER USER servicetracker_user WITH PASSWORD 'NUEVA_CLAVE';`.

## Instalación del proyecto

Desde la raíz del repositorio:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
```

En PowerShell usa `Copy-Item .env.example .env` en lugar de `cp`.

Configura estas variables en `.env`; nunca subas ese archivo ni contraseñas a Git:

```dotenv
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=servicetracker
DB_USERNAME=servicetracker_user
DB_PASSWORD=TU_CLAVE_LOCAL
```

Después crea las tablas y datos demostrativos:

```bash
php artisan migrate --seed
```

## Iniciar la aplicación

El comando recomendado levanta Laravel, Vite y los procesos auxiliares:

```bash
composer dev
```

Como alternativa, usa dos terminales:

```bash
php artisan serve --host=localhost --port=8000
```

```bash
npm run dev
```

Abre `http://localhost:8000`. Detén los procesos con `Ctrl+C`.

## API disponible

| Método      | Ruta                      | Uso                                   |
| ----------- | ------------------------- | ------------------------------------- |
| `GET`       | `/sanctum/csrf-cookie`    | Inicializa la cookie CSRF de Sanctum. |
| `POST`      | `/login`                  | Inicia una sesión web de Fortify.     |
| `POST`      | `/logout`                 | Cierra la sesión.                     |
| `GET`       | `/api/user`               | Devuelve el usuario autenticado.      |
| `GET`       | `/api/services`           | Lista servicios.                      |
| `POST`      | `/api/services`           | Crea un servicio.                     |
| `GET`       | `/api/services/{service}` | Consulta un servicio.                 |
| `PUT/PATCH` | `/api/services/{service}` | Actualiza un servicio.                |
| `DELETE`    | `/api/services/{service}` | Elimina un servicio.                  |

En el estado actual de la rama base, las rutas de `services` todavía no están protegidas ni aisladas por técnico. Las pruebas de la Persona 3 detectan este incumplimiento; no debe interpretarse como comportamiento correcto.

## Entorno aislado para pruebas manuales

No ejecutes `migrate:fresh` sobre `servicetracker`, producción ni una base que contenga información real. Crea un archivo local `.env.postman` a partir de `.env`, cambia solamente:

```dotenv
APP_ENV=postman
APP_DEBUG=false
DB_DATABASE=servicetracker_postman
```

Luego prepara esa base dedicada:

```bash
php artisan migrate:fresh --seed --env=postman
php artisan serve --host=localhost --port=8000 --env=postman
```

Para una ejecución desechable sin PostgreSQL también se puede usar SQLite. En Bash:

```bash
touch /tmp/mesa-ayuda-postman.sqlite
APP_ENV=postman DB_CONNECTION=sqlite DB_DATABASE=/tmp/mesa-ayuda-postman.sqlite DB_URL= SESSION_DRIVER=file CACHE_STORE=file php artisan migrate:fresh --seed --force
APP_ENV=postman APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=/tmp/mesa-ayuda-postman.sqlite DB_URL= SESSION_DRIVER=file CACHE_STORE=file php artisan serve --host=localhost --port=8000
```

En PowerShell:

```powershell
New-Item "$env:TEMP\mesa-ayuda-postman.sqlite" -ItemType File -Force
$env:APP_ENV='postman'; $env:APP_DEBUG='false'; $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE="$env:TEMP\mesa-ayuda-postman.sqlite"; $env:DB_URL=''; $env:SESSION_DRIVER='file'; $env:CACHE_STORE='file'
php artisan migrate:fresh --seed --force
php artisan serve --host=localhost --port=8000
```

## Pruebas manuales en Postman

La colección versionada está en `postman/collections/Mesa de Ayuda - Seguridad API` y el entorno en `postman/environments/Mesa de Ayuda - Local.environment.yaml`. 
En Postman Desktop:

1. Abre **Mesa de Ayuda - Seguridad API** y selecciona el entorno **Mesa de Ayuda - Local**.
2. Confirma que `base_url` sea `http://localhost:8000`.
3. Escribe localmente la contraseña de demostración en el valor **Current** de `demo_password`, con tipo secret. No la escribas en el valor compartido ni la subas a Git.
4. Ejecuta las solicitudes en el orden numérico de las carpetas. La obtención de CSRF guarda y decodifica `XSRF-TOKEN`; login y logout mandan automáticamente `X-XSRF-TOKEN`.
5. Para cada caso abre la solicitud, pulsa **Send** y comprueba tanto el código HTTP como las aserciones de **Test Results**.

Resultados finales esperados cuando autenticación y autorización estén integradas:

| Caso                                            | Resultado esperado                              |
| ----------------------------------------------- | ----------------------------------------------- |
| CSRF y login válido                             | `204` y `200`                                   |
| `/api/user` y servicios con sesión              | `200`                                           |
| Servicios sin sesión                            | `401`                                           |
| Filtros y paginación                            | `200`, solo datos propios y metadatos de página |
| `technician_id` enviado por el cliente          | `422`                                           |
| Consultar, actualizar o eliminar servicio ajeno | `403`                                           |
| Estado inválido                                 | `422`                                           |
| Recurso inexistente                             | `404`                                           |
| Usuario inactivo                                | rechazo, nunca `200`                            |
| Login sin CSRF                                  | `419`                                           |
| Sexto intento de login inválido                 | `429`                                           |

Consulta [`evidencias/README.md`](evidencias/README.md) para saber qué capturar, cómo ocultar datos y cuáles resultados siguen pendientes.

### Postman CLI

El valor secreto se pasa solo al proceso actual. Sustituye el marcador sin guardar el comando con la clave real en archivos compartidos:

```bash
postman collection lint "postman/collections/Mesa de Ayuda - Seguridad API"
postman collection run "postman/collections/Mesa de Ayuda - Seguridad API" \
  --environment "postman/environments/Mesa de Ayuda - Local.environment.yaml" \
  --env-var "demo_password=<CLAVE_LOCAL>"
```

Después de `postman login`, se puede añadir `--output evidencias/postman` para conservar el resumen YAML. Esa salida puede contener datos de solicitudes: revísala antes de incluirla en un commit.

## Pruebas automatizadas

`phpunit.xml` usa SQLite en memoria, por lo que estas pruebas no modifican PostgreSQL:

```bash
php artisan route:list --path=api
php artisan test --filter=SeguridadBasicaTest --do-not-cache-result
composer ci:check
```

Una suite roja es un hallazgo, no una aprobación. En la revisión del 18 de octubre de 2026, `SeguridadBasicaTest` ejecutó 12 casos: 4 pasaron y 8 fallaron por controles aún ausentes en autenticación/autorización. La evidencia final de la suite debe tomarse únicamente cuando los 12 casos pasen.

El mismo día, `npm run check` y el lint de las 29 solicitudes Postman pasaron. `composer ci:check` todavía falla en `npm run types:check` porque el archivo generado e ignorado `resources/js/actions/Illuminate/Routing/RedirectController.ts` incluye el método `query`, que no pertenece al tipo `Method` actual. Este bloqueo de generación/tipos es independiente de las pruebas de seguridad y también debe resolverse antes de aprobar el PR definitivo.

## Evidencias

El entregable requiere como mínimo: listado de rutas protegidas; comparación `401/200`; `technician_id` rechazado con `422`; acceso ajeno con `403`; filtros/paginación; ejecución completa de `SeguridadBasicaTest`; y este README con endpoints, variables necesarias y pasos de ejecución.

La carpeta `evidencias/` contiene resultados reales que ya pasan, pero son evidencia complementaria.

## Seguridad de archivos locales

- `.env`, `.env.postman`, cookies, tokens y contraseñas están ignorados por Git.
- Usa `servicetracker_postman` o SQLite temporal para pruebas destructivas.
- Antes de compartir una captura, oculta valores de cookies, `X-XSRF-TOKEN`, contraseñas y cadenas de conexión.
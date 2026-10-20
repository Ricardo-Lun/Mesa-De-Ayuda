# Evidencias y auditoría de la colección Postman

Esta carpeta conserva la salida real de Postman CLI. Las imágenes actuales corresponden a la primera ejecución de 29 solicitudes registrada en `test.txt`; son útiles para diagnosticar, pero no representan cumplimiento final.

## Conclusión de la primera ejecución

Postman CLI ejecutó 29 solicitudes, 29 scripts y 38 aserciones. No hubo errores de transporte, pero fallaron 18 aserciones. La columna `requests failed = 0` solo significa que Postman pudo enviar todas las solicitudes; no significa que las pruebas hayan aprobado.

Los 18 fallos originales no equivalían a 18 vulnerabilidades independientes. Varios resultados posteriores estaban contaminados porque:

- El logout de Carlos devolvió `419`, por lo que su sesión podía seguir activa.
- El login de Laura devolvió `403` porque Carlos seguía autenticado mediante el guard web.
- La limpieza posterior aceptaba cualquier estado inferior a `500`, incluso un `419` que no había cerrado la sesión.
- Los primeros cinco intentos inválidos recibieron `403` porque había una sesión residual; el sexto `429` sí mostró que el limitador se activó.
- La aserción de propietario convertía una respuesta no paginada en una lista vacía y podía aprobarse vacíamente.

## Correcciones aplicadas a la colección

La colección local contiene ahora 33 solicitudes y fue validada con `postman collection lint` sin errores ni advertencias.

Se hicieron estas correcciones sin reducir las expectativas de seguridad:

1. Renovar `XSRF-TOKEN` inmediatamente antes de cerrar la sesión de Carlos, cerrar la de Laura y limpiar una sesión residual.
2. Leer la cookie XSRF vigente justo antes de cada logout y actualizar `X-XSRF-TOKEN`.
3. Exigir que la limpieza responda `204` o `401`; un `419` ya no cuenta como limpieza válida.
4. Añadir `GET /api/user` después del login de Laura para demostrar que ella es la usuaria activa antes de probar un servicio de Carlos.
5. Guardar el ID del usuario confirmado por `/api/user` y compararlo con `technician_id`; la prueba ya no usa una lista vacía ni un ID fijo para aprobar.

## Resultado de control después de corregir la secuencia

La colección corregida se ejecutó contra SQLite temporal, con datos recién sembrados y `APP_DEBUG=false`:

| Métrica             |                 Resultado |
| ------------------- | ------------------------: |
| Solicitudes         |                        33 |
| Scripts de prueba   |                        33 |
| Scripts previos     |                         3 |
| Aserciones          |                        47 |
| Aserciones fallidas |                        15 |
| Lint                | 0 errores, 0 advertencias |

Las correcciones eliminaron los fallos artificiales de logout, login de Laura, limpieza y límite de intentos:

- Logout de Carlos: `204`.
- Login de Laura: `200`.
- Logout de Laura: `204`.
- Limpieza de la sesión residual: `204`.
- Primeros cinco intentos inválidos: `422`.
- Sexto intento: `429`.

## Fallos reales que permanecen

| Área                    | Resultado actual                                                               | Evaluación                                                                                                            |
| ----------------------- | ------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------- |
| Sesión Sanctum          | Login `200`, pero `/api/user` devuelve `401`                                   | Incumplimiento confirmado de integración de sesión/API.                                                               |
| Listado                 | Devuelve un arreglo directo, sin `data`, `current_page`, `per_page` ni `total` | Incumplimiento confirmado de paginación.                                                                              |
| Aislamiento del listado | No puede verificarse el usuario porque `/api/user` falla                       | Pendiente de corregir autenticación; la aserción ya no aprueba vacíamente.                                            |
| Propietario falsificado | Enviar `technician_id` crea el servicio con `201`                              | Incumplimiento confirmado; debe responder `422`.                                                                      |
| Identidad de Laura      | Su login responde `200`, pero `/api/user` devuelve `401`                       | La sesión web existe, pero Sanctum no la reconoce en la API.                                                          |
| Recurso ajeno           | GET/PATCH/DELETE responden `200/200/204`                                       | Las respuestas son inseguras, pero la evidencia final `403` debe tomarse después de que `/api/user` confirme a Laura. |
| Acceso anónimo          | `/api/services` devuelve `200`                                                 | Incumplimiento confirmado; debe responder `401`.                                                                      |
| Usuario inactivo        | El login devuelve `200`                                                        | Incumplimiento confirmado; debe rechazarse.                                                                           |

Los 15 fallos restantes son aserciones, no 15 problemas distintos. Algunos casos generan dos fallos para el mismo control: código HTTP, precondición y estructura del JSON.

## Casos que ya funcionan

- CSRF inicial y renovaciones: `204` con cookie.
- Login válido de Carlos y Laura: `200`.
- Estado inválido: `422` e identifica `status`.
- Servicio inexistente: `404`.
- Credenciales inválidas de Fortify: `422`.
- Login sin encabezado CSRF: `419`.
- Límite: cinco respuestas `422` y sexta respuesta `429`.

El documento de referencia usa `200` para logout y `401` para credenciales falsas porque implementa un controlador propio. Mesa de Ayuda usa las respuestas reales de Fortify: `204` para logout y `422` para credenciales inválidas. Esta adaptación conserva el principio de seguridad y debe explicarse en la entrega.

## Estado de las imágenes actuales

| Archivo                     | Qué muestra                                              | Uso correcto                                                                   |
| --------------------------- | -------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `00.sesion.png`             | Login correcto y `/api/user` en `401`                    | Hallazgo preliminar de sesión Sanctum.                                         |
| `01.servicios.png`          | Falta de paginación, propietario aceptado, `422` y `404` | Hallazgos preliminares; la antigua aprobación de propietario quedó invalidada. |
| `02.autorizacion.png`       | Logout `419`, login Laura `403` y operaciones ajenas     | No usar para afirmar falta de Policy; la sesión estaba contaminada.            |
| `03.errores-de-sesion.png`  | Acceso público, inactivo permitido, CSRF `419`           | Mixta; contiene hallazgos válidos y una limpieza que antes aceptaba `419`.     |
| `04.limite-de-intentos.png` | Cinco `403` y sexto `429`                                | Reemplazar: los `403` provenían de una sesión residual.                        |
| `log-18-oct.png`            | Log del servidor durante la corrida                      | Contexto opcional; no sustituye respuestas ni aserciones.                      |

Después de volver a ejecutar la colección corregida, reemplaza por lo menos `02.autorizacion.png`, `03.errores-de-sesion.png` y `04.limite-de-intentos.png`. También conviene reemplazar `00.sesion.png` y `01.servicios.png` para que toda la serie corresponda a una sola corrida de 33 solicitudes.

## Cómo repetir la prueba

Usa una base dedicada recién sembrada. Mantén el mismo host durante toda la corrida: solo `127.0.0.1` o solo `localhost`, nunca ambos.

En Zsh:

```zsh
read -s -P 'Contraseña demo: ' demo_password
echo
postman collection lint "postman/collections/Mesa de Ayuda - Seguridad API"
postman collection run "postman/collections/Mesa de Ayuda - Seguridad API" \
  --environment "postman/environments/Mesa de Ayuda - Local.environment.yaml" \
  --env-var "base_url=http://127.0.0.1:8001" \
  --env-var "demo_password=$demo_password" \
  --no-report-events
unset demo_password
```

Antes de capturar verifica que el resumen diga:

- `requests: 33`.
- `test-scripts: 33`.
- `prerequest-scripts: 3`.
- `assertions: 47`.
- Mientras el backend no se corrija, se esperan 15 aserciones fallidas.

Si vuelven a aparecer `419` en los logout, `403` en el login de Laura o `403` en los primeros cinco intentos, no uses esa corrida como evidencia: confirma que estás ejecutando la colección local actualizada y que el lint escanea 33 solicitudes.

## Evidencia mínima final

Las capturas finales deben hacerse cuando las correcciones de autenticación/autorización estén integradas:

1. Rutas API mostrando middleware de autenticación.
2. `/api/services` sin sesión `401` y con sesión `200`.
3. POST/PATCH con `technician_id` rechazado con `422`.
4. Laura confirmada por `/api/user` y servicio de Carlos rechazado con `403`.
5. Listado filtrado/paginado con `data`, `total` y `current_page`.
6. `SeguridadBasicaTest` completo con cero fallos.

Las capturas agrupadas de Postman CLI sirven como resumen de ejecución. Para el caso de propietario falsificado, la salida normal no muestra el JSON enviado; acompáñala con una captura del cuerpo de la solicitud en Postman Desktop o del archivo de solicitud versionado donde se vea `technician_id`.

No captures todavía como evidencia final los casos que siguen fallando. Conserva las corridas rojas únicamente como registro de hallazgos pendientes.

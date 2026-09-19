# Evidencia tecnica

Fecha de verificacion: `2026-09-19`

## Docker MySQL

Rama: `feature/docker-mysql-schema`

Comando de arranque reproducible:

```powershell
docker compose up -d
```

El servicio utiliza `mysql:8.4`, el contenedor `citas_medicas_mysql` y el volumen nombrado `mysql_data`.

## Estado de contenedores

Comando:

```powershell
docker compose ps
```

Resultado obtenido:

```text
NAME                  IMAGE       SERVICE   STATUS                    PORTS
citas_medicas_mysql   mysql:8.4   mysql     Up 31 seconds (healthy)   0.0.0.0:3306->3306/tcp
```

## Validacion de Docker Compose

Comando:

```powershell
docker compose config --quiet
```

Resultado obtenido: configuracion valida, codigo de salida `0`. Compose reconoce el servicio `mysql` y el volumen `mysql_data`.

## Tablas

Consulta:

```sql
SHOW TABLES;
```

Resultado obtenido:

```text
citas
doctores
pacientes
```

## Datos semilla

Consultas:

```sql
SELECT COUNT(*) AS pacientes FROM pacientes;
SELECT COUNT(*) AS doctores FROM doctores;
SELECT COUNT(*) AS citas FROM citas;
```

Resultado obtenido:

```text
pacientes: 3
doctores: 3
citas: 3
```

## Relaciones

Consulta de comprobacion:

```sql
SELECT
    c.id AS cita,
    CONCAT(p.nombres, ' ', p.apellidos) AS paciente,
    CONCAT(d.nombres, ' ', d.apellidos) AS doctor,
    c.fecha_hora_inicio AS fecha,
    c.estado
FROM citas AS c
INNER JOIN pacientes AS p ON p.id = c.paciente_id
INNER JOIN doctores AS d ON d.id = c.doctor_id
ORDER BY c.id;
```

Resultado obtenido:

```text
1 | Juan Perez     | Andrea Garcia | 2026-10-05 09:00:00 | pendiente
2 | Maria Lopez    | Luis Morales  | 2026-10-05 10:00:00 | confirmada
3 | Carlos Ramirez | Sofia Herrera | 2026-10-05 11:00:00 | pendiente
```

## Persistencia

Procedimiento:

```powershell
docker compose down
docker compose up -d
docker compose exec mysql mysql -u citas_user -p -D citas_medicas -e "SELECT COUNT(*) FROM pacientes;"
```

Resultado obtenido: `docker compose down` elimino el contenedor y la red, pero el volumen `mysql_data` continuo disponible. Despues del segundo arranque, MySQL regreso a estado `healthy` y la consulta devolvio:

```text
pacientes_despues_de_reinicio: 3
```

## Reproducibilidad

Procedimiento de inicializacion limpia:

```powershell
docker compose down -v
docker compose up -d
docker compose ps
```

Resultado obtenido: el volumen `mysql_data` fue eliminado y creado nuevamente. Sin ejecutar SQL manual, MySQL ejecuto en orden `01-schema.sql` y `02-seed.sql` desde `/docker-entrypoint-initdb.d`, regreso a estado `healthy` y produjo:

```text
Tablas: citas, doctores, pacientes
pacientes: 3
doctores: 3
citas: 3
```

## Conexion desde Laravel

Comandos:

```powershell
php artisan config:clear
php artisan db:show
```

Laravel se conecta desde el host a `127.0.0.1:3306` usando las variables `DB_*` del archivo `.env` local.

Resultado obtenido con `php artisan db:show` antes del formateo de estadisticas:

```text
MySQL: 8.4.11
Connection: mysql
Database: citas_medicas
Host: 127.0.0.1
Port: 3306
Username: citas_user
Tables: 3
```

La consulta de conexion ejecutada desde Laravel devolvio `3` pacientes. El comando `db:show` encontro la base correctamente, pero despues reporto que la extension PHP `intl` no esta instalada al intentar formatear estadisticas; esto no afecta la conexion PDO MySQL.

## Estado final de la verificacion

RQNF-01 y RQNF-02 verificados. Los resultados documentados fueron obtenidos contra el contenedor real y no contienen contrasenas.

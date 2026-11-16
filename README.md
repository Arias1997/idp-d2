# IDP D-2 · Iglesia de Dios de la Profecía, Distrito 2 Sacaba

Sitio web en PHP 8 con arquitectura MVC (sin framework) y PDO. Incluye páginas públicas (inicio, iglesias con mapa y sala de transmisiones en vivo), un panel de administración (iglesias, zonas, transmisiones, usuarios y reporte en PDF), registro público y dos roles: **administrador** y **usuario**.

## 🔑 Cuentas de prueba

Después de instalar, entra en **Ingresar** (http://127.0.0.1:8081/index.php?r=login) con:

| Rol | Correo | Contraseña | Qué puede ver |
|---|---|---|---|
| Administrador | `admin@idp.bo` | `Admin12345` | Resumen, Transmisiones, Iglesias, Zonas, Usuarios y reporte PDF |
| Usuario | `usuario@idp.bo` | `Usuario12345` | Su perfil personal |

También se puede crear una cuenta nueva (rol usuario) desde **Ingresar → Crear cuenta**.

> Son cuentas de demostración que crea `database/idp_d2.sql`. Solo existen en la base local de quien instala el proyecto.

## Requisitos

- PHP 8.1 o superior con las extensiones `pdo_mysql`, `mbstring` y `gd` (en XAMPP: quitar el `;` de `extension=gd` en `php.ini`).
- MySQL 8 (o MariaDB 10.4+).
- [Composer](https://getcomposer.org/) para instalar Dompdf (reporte PDF).

## Instalación

**1. Crear la base de datos.** Importa el archivo `database/idp_d2.sql`. Crea la base `idp_d2` con sus tablas y datos de ejemplo.

```bash
mysql -u root -p < database/idp_d2.sql
```

También se puede abrir y ejecutar el archivo desde phpMyAdmin o MySQL Workbench.

**2. Configurar la conexión.** En `config/database.php` escribe el usuario y la contraseña de tu MySQL:

```php
'username' => 'root',
'password' => 'tu_contraseña',   // XAMPP sin contraseña: deja ''
```

**3. Instalar las dependencias:**

```bash
composer install
```

**4. Levantar el servidor:**

```bash
php -S 127.0.0.1:8081 -t public
```

**5. Abrir** http://127.0.0.1:8081

## Estructura

```
app/
  controllers/   reciben la petición, verifican permisos y CSRF
  repositories/  consultas SQL con PDO
  validator/     reglas de validación de los formularios
  models/        clases que representan los datos
  services/      generación del PDF (Dompdf)
  views/         plantillas HTML
config/          conexión a la base de datos y mapa
database/        script SQL de la base de datos
public/          punto de entrada (index.php), CSS, JavaScript e imágenes
```

Toda petición entra por `public/index.php`, que lee la ruta `?r=` y llama al controlador correspondiente.

## Seguridad

Consultas preparadas (PDO), token CSRF en todos los formularios, contraseñas con `password_hash`, salida escapada con `htmlspecialchars`, control de acceso por rol en el servidor y validación en JavaScript y en PHP.

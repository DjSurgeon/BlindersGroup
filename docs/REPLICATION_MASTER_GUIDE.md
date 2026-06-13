# Guía Maestra de Replicación (Product Badges)

Este documento es la "Biblia" técnica para replicar exactamente el entorno de desarrollo y el flujo de trabajo que utilizamos para construir el módulo **Product Badges** en PrestaShop 1.7. 

Sigue este *Roadmap* paso a paso para levantar la infraestructura desde cero y comprender la arquitectura del módulo.

---

## FASE 1: Infraestructura (Docker)

El objetivo es tener un entorno local predecible, con PHP 7.4, MySQL 8.0 y PrestaShop 1.7.8.11, aislado y con soporte para mapeo de volúmenes de desarrollo.

### 0. Estructura de Directorios Inicial
Antes de crear los archivos, asegúrate de tener esta estructura vacía para alojar las configuraciones:

```text
docker-compose/
├── .env
├── docker-compose.yml
├── mysql.cnf
├── data/
│   └── apache/
│       ├── 000-default.conf
│       └── custom.ini
└── modules_dev/
```

### 1. Variables de Entorno (`.env`)
Crea el archivo `.env` en tu directorio `docker-compose/`:

```env
# MYSQL - Base de Datos
MYSQL_ROOT_PASSWORD=R00tPass2026!
MYSQL_USER=prestashop
MYSQL_PASSWORD=PrestashopPass2026!
MYSQL_DATABASE=prestashop_db

# PRESTASHOP - Configuración Tienda
PS_SHOP_NAME=Blinders eCommerce
PS_DOMAIN=prestashop.local:8080
PS_DOMAIN_SSL=0
PS_LANGUAGE=es
PS_COUNTRY=ES
PS_ALL_LANGUAGES=0

# PRESTASHOP - Admin
PS_FOLDER_ADMIN=admin_xyz123blinders
PS_FOLDER_API=api
ADMIN_MAIL=djsurgeon83@gmail.com
ADMIN_PASSWD=AdminBlinders2024!

# PHP - Configuración
PHP_VERSION=7.4
MEMORY_LIMIT=512M
MAX_EXECUTION_TIME=300
UPLOAD_MAX_FILESIZE=100M

# PHPMYADMIN
PMA_USER=root
PMA_PASSWORD=R00tPass2026!
PMA_HOST=mysql
PMA_PORT=3306
```

### 2. Docker Compose (`docker-compose.yml`)
Crea el archivo `docker-compose.yml`. Fíjate especialmente en el mapeo de volúmenes para desarrollo (`modules_dev`).

```yaml
version: '3'

networks:
  prestashop_network:
    driver: bridge

volumes:
  mysql_data:
  prestashop_data:

services:
  mysql:
    image: mysql:8.0.35
    container_name: ps_mysql_8
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    command: --default-authentication-plugin=mysql_native_password
    networks:
      - prestashop_network

  prestashop:
    image: prestashop/prestashop:1.7.8.11
    container_name: ps_main_187
    restart: unless-stopped
    depends_on:
      - mysql
    environment:
      DB_SERVER: mysql
      DB_NAME: ${MYSQL_DATABASE}
      DB_USER: ${MYSQL_USER}
      DB_PASSWD: ${MYSQL_PASSWORD}
      DB_PREFIX: ps_
      PS_SHOP_NAME: ${PS_SHOP_NAME}
      PS_DOMAIN: ${PS_DOMAIN}
      PS_LANGUAGE: ${PS_LANGUAGE}
      PS_INSTALL_AUTO: "1"
      PS_FOLDER_ADMIN: ${PS_FOLDER_ADMIN}
      ADMIN_MAIL: ${ADMIN_MAIL}
      ADMIN_PASSWD: ${ADMIN_PASSWD}
      PS_DEV_MODE: "1"
      PS_DEBUG: "1"
    ports:
      - "8080:80"
    volumes:
      - prestashop_data:/var/www/html
      # Directorio local mapeado al contenedor para desarrollar en vivo
      - ./modules_dev:/var/www/html/modules/productbadges:rw
    networks:
      - prestashop_network

  phpmyadmin:
    image: phpmyadmin:5.2
    container_name: ps_phpmyadmin
    restart: unless-stopped
    depends_on:
      - mysql
    environment:
      PMA_HOST: mysql
      PMA_USER: ${PMA_USER}
      PMA_PASSWORD: ${PMA_PASSWORD}
      PMA_PORT: 3306
    ports:
      - "8888:80"
    volumes:
      - ./data/phpmyadmin:/var/www/html/sessions
    networks:
      - prestashop_network
    logging:
      driver: "json-file"
      options:
        max-size: "5m"
        max-file: "3"
```

### 3. Configuraciones Complementarias (MySQL, Apache, PHP)

Para que los mapeos de volúmenes del `docker-compose.yml` no den error, debes crear los siguientes 3 ficheros:

**A) `mysql.cnf`** (Optimización de Base de Datos para el contenedor `mysql`):
```ini
[mysqld]
# Conexiones
max_connections=100
wait_timeout=600
interactive_timeout=600

# Buffer
innodb_buffer_pool_size=256M
innodb_log_file_size=100M
max_allowed_packet=64M

# Rendimiento
innodb_flush_log_at_trx_commit=2
innodb_flush_method=O_DIRECT

# Logging (mínimo en desarrollo)
general_log=OFF
slow_query_log=ON
slow_query_log_file=/var/log/mysql/slow-query.log
long_query_time=2

# Charset
character_set_server=utf8mb4
collation_server=utf8mb4_unicode_ci
```

**B) `data/apache/000-default.conf`** (VirtualHost de Apache en el contenedor `prestashop`):
```apache
<VirtualHost *:80>
    ServerName prestashop.local
    ServerAlias admin.prestashop.local
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        AllowOverride All
        Order Allow,Deny
        Allow from all
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?$1 [QSA,L]
        </IfModule>
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>
```

**C) `data/apache/custom.ini`** (Reglas PHP personalizadas):
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
max_input_vars = 10000
```

### 4. Permisos y Troubleshooting (El "Error 500")
**Regla de Oro:** Si en algún momento necesitas borrar la caché desde tu terminal o modificas archivos como `root`, PrestaShop puede lanzar un "Error 500" o un error de escritura de Symfony.
**Solución:** Restaura los permisos de Apache en el contenedor:
```bash
docker exec -it ps_main_187 chown -R www-data:www-data /var/www/html/var/cache/
```

### 5. Comandos para Levantar y Gestionar el Entorno
Una vez tengas la estructura completa, abre tu terminal en el directorio `docker-compose/` y ejecuta los comandos vitales:

- **Arrancar el entorno (en segundo plano):**
  ```bash
  docker-compose up -d
  ```
- **Ver logs en tiempo real** (muy útil en el primer arranque, ya que PrestaShop tarda un par de minutos en instalarse la primera vez):
  ```bash
  docker-compose logs -f prestashop
  ```
- **Detener el entorno (sin borrar datos):**
  ```bash
  docker-compose down
  ```
- **Destruir el entorno completamente (Reset desde cero):**
  Borrará contenedores, redes y **los volúmenes de datos** (ideal si la BD se corrompe o quieres volver a instalar PrestaShop de fábrica):
  ```bash
  docker-compose down -v --remove-orphans
  ```

> [!TIP]
> **Acceso:** Cuando los logs confirmen que Apache está listo, entra a la tienda pública en `http://prestashop.local:8080` y a tu Backoffice en `http://prestashop.local￼
:8080/admin_xyz123blinders` (usuario: `djsurgeon83@gmail.com` / clave: `AdminBlinders2024!`).

---

## FASE 2: Desarrollo del Módulo ("The Golden Path")

Una vez levantado el Docker, se desarrolla el código dentro de la carpeta `modules_dev/` mapeada.

### Paso 1: Scaffolding (`productbadges.php` y BD)
1. Extiende de `Module` estándar.
2. Define `install()` registrando los hooks obligatorios: `displayBackOfficeHeader`, `actionProductFlagsModifier`, `displayHeader`, `displayAdminProductsExtra`, y `actionProductUpdate`.
3. Crea las tablas en MySQL (`sql/install.php`):
   - `ps_productbadges`: Para `bg_color`, `text_color`, `position` y `active`.
   - `ps_productbadges_lang`: (OBLIGATORIO para traducciones de `text`).
   - `ps_productbadges_product`: (OBLIGATORIO para asignar varias etiquetas a varios productos M:M).

*Lección Aprendida:* **Evita la tabla `_shop`** a menos que el proyecto demande reglas de multitienda estrictas (etiquetas diferentes por tienda). Al compartir las mismas tablas en un entorno general, evitamos inconsistencias y vacíos de datos.

### Paso 2: Modelo de Datos (`ProductBadgeModel.php`)
Usa `ObjectModel` de PrestaShop. Esto ahorra horas de código SQL manual.
- Activa `'multilang' => true`.
- Define el campo `text` con `'lang' => true`.
- PrestaShop automáticamente usará la tabla `_lang` creada en el paso 1.

### Paso 3: BackOffice UI (`AdminProductBadgesController.php`)
Extiende de `ModuleAdminController`.
- **Listado:** Usa `$this->fields_list`.
- **Formulario:** Sobrescribe `renderForm()` usando `HelperForm`. PrestaShop detectará el `'lang' => true` del modelo e inyectará automáticamente un **selector de idiomas (ES/EN)** en la caja de texto del Backoffice.

### Paso 4: Ficha del Producto (Asignación)
En `productbadges.php`, usa `hookDisplayAdminProductsExtra`:
1. Consulta las etiquetas activas y las que ya tiene asignadas el producto.
2. Renderiza una plantilla Smarty (`admin_products_extra.tpl`) con casillas de verificación (`checkboxes`).
3. **Validación JS:** Inyecta un script de Javascript en esta plantilla que lea `PRODUCTBADGES_MAX_ITEMS`. Si el usuario marca el máximo de casillas permitidas, deshabilita el resto dinámicamente.
4. En `hookActionProductUpdate`, captura el `$_POST` y guarda las asociaciones en `ps_productbadges_product`.

### Paso 5: Renderizado Frontend (El Ecosistema 1.7)
**NUNCA** inyectes HTML manual modificando plantillas del tema o usando hooks obsoletos como `displayProductFlags`.
1. Usa **`hookActionProductFlagsModifier`**: Este hook recibe un array `$params['flags']`. Simplemente añade tus etiquetas a ese array. El tema nativo Classic (o cualquier otro bien hecho) se encargará de posicionarlas sobre la imagen de forma estándar.
2. Usa **`hookDisplayHeader`**: Extrae las configuraciones de colores de la Base de Datos e inyecta dinámicamente una etiqueta `<style>` con clases CSS (`.product-flag.productbadge-X { background: ... }`) en la cabecera. Si la posición es `top-right`, aplícale un `float: right` o flexbox según la disposición.

---

## FASE 3: Empaquetado y Configuración Git

Una vez terminado el desarrollo, el código debe aislarse del entorno de Docker para su entrega:

1. **Aislamiento:** Mueve o copia todo el contenido del módulo de `docker-compose/modules_dev/` a una carpeta limpia `modules/productbadges/` en la raíz de tu repositorio final.
2. **Ignorar Docker:** Asegúrate de configurar un archivo `.gitignore` robusto que ignore todo el directorio de Docker (`prestashop/`, `docker-compose/`) para que el repositorio solo contenga el módulo entregable y sus documentos.
3. **Auditoría IA:** Siempre deja a la vista tus carpetas ocultas de IA (`.gemini/`, `.claude/`, `.cursor/`) para que los evaluadores validen cómo generaste el código.

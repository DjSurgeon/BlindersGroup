# 🐳 SETUP DOCKER PRESTASHOP 1.7.8.11 + PHP 7.4 - GUÍA DEFINITIVA

---

## 📋 TU CONFIGURACIÓN FINAL (RESUMEN)

```
┌─────────────────────────────────────────────────────┐
│ SO: Fedora 43 (limpio, sin contaminar)              │
├─────────────────────────────────────────────────────┤
│ DOCKER CONTAINER:                                   │
│  - PrestaShop 1.7.8.11                              │
│  - PHP 7.4.33                                       │
│  - Apache 2.4                                       │
│  - MySQL 8.0 (contenedor separado)                  │
│                                                     │
│ EN NATIVO (Fedora):                                 │
│  - Antigravity + CLI (desarrollo)                   │
│  - Gemini CLI (consultas rápidas)                   │
│  - VS Code (editor con Antigravity)                 │
│  - Git + GitHub (repositorio)                       │
│                                                     │
│ ACCESO A PRESTASHOP:                                │
│  - http://prestashop.local:8080 (tienda)            │
│  - http://admin.prestashop.local:8080 (backend)     │
│  - http://localhost:8888 (PhpMyAdmin)               │
└─────────────────────────────────────────────────────┘
```

---

## ⚠️ REQUISITOS PREVIOS (VERIFICA PRIMERO)

```bash
# 1. Verificar Fedora 43
cat /etc/fedora-release
# Debe mostrar: Fedora release 43 (...)

# 2. Verificar conexión a internet
ping -c 1 8.8.8.8
# Debe responder OK

# 3. Verificar espacio en disco
df -h /home
# Mínimo 30GB libres en /home

# 4. Verificar que SELinux existe (aunque sea en permissive)
getenforce
# Output: Permissive o Enforcing (cualquiera vale)
```

---

## 🔧 PASO 1: PREPARAR FEDORA 43

### 1.1 - Actualizar completamente el sistema

```bash
# Actualizar todos los paquetes
sudo dnf upgrade -y

# Instalar herramientas de desarrollo esenciales
sudo dnf groupinstall -y "Development Tools"
sudo dnf groupinstall -y "Development Libraries"

# Instalar paquetes base indispensables
sudo dnf install -y \
  curl \
  wget \
  git \
  vim \
  nano \
  htop \
  net-tools \
  openssh-client \
  ca-certificates \
  openssl \
  unzip \
  zip

# Verificar Git
git --version
# Debe mostrar versión >= 2.30
```

### 1.2 - Verificar SELinux (IMPORTANTE)

```bash
# Ver estado actual
getenforce

# Si está en Enforcing, cambiar a Permissive para desarrollo
# (SOLO EN MÁQUINA DE DESARROLLO)
sudo setenforce 0

# Hacer permanente en /etc/selinux/config
sudo nano /etc/selinux/config

# Cambiar esta línea:
# SELINUX=enforcing
# Por:
# SELINUX=permissive

# Guardar (Ctrl+X → Y → Enter)

# Reiniciar para que aplique
sudo reboot
# (Espera a que termine)
```

### 1.3 - Configurar Firewall (Fedora)

```bash
# Fedora usa firewalld (NO iptables)

# Ver si está activo
sudo systemctl status firewalld

# Si no está corriendo, iniciarlo
sudo systemctl start firewalld
sudo systemctl enable firewalld

# Abrir puertos ESPECÍFICOS necesarios
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --permanent --add-port=443/tcp
sudo firewall-cmd --permanent --add-port=3306/tcp
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --permanent --add-port=8888/tcp

# Recargar config
sudo firewall-cmd --reload

# Verificar puertos abiertos
sudo firewall-cmd --list-ports
```

---

## 🐳 PASO 2: INSTALAR DOCKER + DOCKER COMPOSE

### 2.1 - Instalar Docker

```bash
# En Fedora, Docker viene desde repositorio oficial
sudo dnf install -y docker docker-compose

# Iniciar servicio
sudo systemctl start docker
sudo systemctl enable docker

# Verificar que está corriendo
sudo systemctl status docker
# Debe mostrar: active (running)
```

### 2.2 - Dar permisos a usuario sin sudo

```bash
# Agregar tu usuario al grupo docker
sudo usermod -aG docker $USER

# Aplicar permisos INMEDIATAMENTE
newgrp docker

# Verificar que funciona sin sudo
docker ps
# Debe mostrar: CONTAINER ID  IMAGE  COMMAND  CREATED  STATUS  PORTS  NAMES
# (vacío es OK, significa que no hay contenedores)

# Si sigue pidiendo sudo, cierra sesión y abre nueva terminal
# (Algunos sistemas requieren logout/login completo)
```

### 2.3 - Verificar versiones instaladas

```bash
docker --version
# Debe ser >= 24.0

docker-compose --version
# Debe ser >= 2.20
```

---

## 📁 PASO 3: CREAR ESTRUCTURA DE CARPETAS

### 3.1 - Crear directorios de trabajo

```bash
# Ir a home
cd ~

# Crear estructura completa (copia y pega TODO ESTO EN UNA LÍNEA)
mkdir -p prestashop/{docker-compose,data/mysql,data/apache,data/prestashop} && \
mkdir -p prestashop/modules_dev && \
mkdir -p prestashop/themes_dev && \
mkdir -p prestashop/backups && \
mkdir -p productbadges/{.antigravity,.claude,modules/productbadges} && \
mkdir -p productbadges/{.cursor,docs,scripts}

# Verificar que se creó todo
tree -L 2 ~/prestashop
tree -L 2 ~/productbadges

# Debería mostrar estructura de carpetas sin errores
```

### 3.2 - Crear archivos necesarios en blanco

```bash
cd ~/prestashop/docker-compose

# Crear archivos que rellenaremos después
touch docker-compose.yml
touch .env
touch .envrc  # para direnv (opcional)

# Dar permisos
chmod 600 .env
```

---

## 🔐 PASO 4: CREAR ARCHIVO `.env` (Configuración sensible)

### 4.1 - Crear `.env` con contraseñas

```bash
nano ~/prestashop/docker-compose/.env

# PEGA TODO ESTO EXACTAMENTE:
```

```plaintext
# MYSQL - Base de Datos
MYSQL_ROOT_PASSWORD=RootSecurePass2024!
MYSQL_USER=prestashop
MYSQL_PASSWORD=PrestashopPass2024!
MYSQL_DATABASE=prestashop_db

# PRESTASHOP - Configuración Tienda
PS_SHOP_NAME=Blinders eCommerce
PS_DOMAIN=prestashop.local:8080
PS_DOMAIN_SSL=0
PS_LANGUAGE=es
PS_COUNTRY=ES
PS_ALL_LANGUAGES=es,en

# PRESTASHOP - Admin
PS_FOLDER_ADMIN=admin_xyz123blinders
PS_FOLDER_API=api
ADMIN_MAIL=admin@blinders-dev.local
ADMIN_PASSWD=AdminBlinders2024!

# PHP - Configuración
PHP_VERSION=8.1
MEMORY_LIMIT=512M
MAX_EXECUTION_TIME=300
UPLOAD_MAX_FILESIZE=100M

# PHPMYADMIN
PMA_USER=root
PMA_PASSWORD=RootSecurePass2024!
PMA_HOST=mysql
PMA_PORT=3306

# DOCKER
COMPOSE_PROJECT_NAME=prestashop_blinders
```

```bash
# Guardar: Ctrl+X → Y → Enter
```

### 4.2 - Verificar que se creó

```bash
cat ~/prestashop/docker-compose/.env
# Debe mostrar todas las variables

# Verificar permisos (solo lectura para usuario)
ls -la ~/prestashop/docker-compose/.env
# Debe mostrar: -rw------- (permisos 600)
```

---

## 🐳 PASO 5: CREAR `docker-compose.yml` (DEFINITIVO)

### 5.1 - Crear archivo

```bash
nano ~/prestashop/docker-compose/docker-compose.yml

# PEGA ESTO EXACTAMENTE (todo el contenido):
```

```yaml
version: '3.9'

# REDES
networks:
  prestashop_network:
    driver: bridge

# VOLÚMENES
volumes:
  mysql_data:
    driver: local
  apache_conf:
    driver: local
  prestashop_data:
    driver: local

# SERVICIOS
services:

  # ==================== MYSQL 8.0 ====================
  mysql:
    image: mysql:8.0.35
    container_name: ps_mysql_8
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
      MYSQL_ALLOW_EMPTY_PASSWORD: "no"
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      # Aumentar límite de conexiones simultáneas
      - ./mysql.cnf:/etc/mysql/conf.d/custom.cnf:ro
    command:
      - --default-authentication-plugin=mysql_native_password
      - --character-set-server=utf8mb4
      - --collation-server=utf8mb4_unicode_ci
      - --max_connections=100
    networks:
      - prestashop_network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u${MYSQL_USER}", "-p${MYSQL_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 5
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # ==================== PRESTASHOP 1.7.8.11 ====================
  prestashop:
    image: prestashop/prestashop:1.7.8.11
    container_name: ps_main_187
    restart: unless-stopped
    depends_on:
      mysql:
        condition: service_healthy
    environment:
      # Base de datos
      DB_SERVER: mysql
      DB_NAME: ${MYSQL_DATABASE}
      DB_USER: ${MYSQL_USER}
      DB_PASSWD: ${MYSQL_PASSWORD}
      DB_PREFIX: ps_
      # Tienda
      PS_SHOP_NAME: ${PS_SHOP_NAME}
      PS_DOMAIN: ${PS_DOMAIN}
      PS_DOMAIN_SSL: ${PS_DOMAIN_SSL}
      PS_LANGUAGE: ${PS_LANGUAGE}
      PS_COUNTRY: ${PS_COUNTRY}
      PS_ALL_LANGUAGES: ${PS_ALL_LANGUAGES}
      PS_INSTALL_AUTO: "1"
      # Admin
      PS_FOLDER_ADMIN: ${PS_FOLDER_ADMIN}
      PS_FOLDER_API: ${PS_FOLDER_API}
      ADMIN_MAIL: ${ADMIN_MAIL}
      ADMIN_PASSWD: ${ADMIN_PASSWD}
      # PHP
      MEMORY_LIMIT: ${MEMORY_LIMIT}
      MAX_EXECUTION_TIME: ${MAX_EXECUTION_TIME}
      UPLOAD_MAX_FILESIZE: ${UPLOAD_MAX_FILESIZE}
      # Desarrollo
      PS_DEV_MODE: "1"
      PS_DEBUG: "1"
    ports:
      - "8080:80"
      - "8443:443"
    volumes:
      # Datos persistentes de PrestaShop
      - prestashop_data:/var/www/html
      # Módulos para desarrollo (mapeado desde host)
      - ./modules_dev:/var/www/html/modules/productbadges:rw
      # Temas (si trabajas con temas)
      - ./themes_dev:/var/www/html/themes/mytheme:rw
      # Logs
      - ./data/prestashop/logs:/var/www/html/var/logs:rw
      - ./data/prestashop/cache:/var/www/html/var/cache:rw
      # Apache config
      - ./data/apache/000-default.conf:/etc/apache2/sites-enabled/000-default.conf:ro
    networks:
      - prestashop_network
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # ==================== PHPMYADMIN ====================
  phpmyadmin:
    image: phpmyadmin:5.2
    container_name: ps_phpmyadmin
    restart: unless-stopped
    depends_on:
      - mysql
    environment:
      PMA_HOST: ${PMA_HOST}
      PMA_USER: ${PMA_USER}
      PMA_PASSWORD: ${PMA_PASSWORD}
      PMA_PORT: ${PMA_PORT}
      PMA_ABSOLUTE_URI: http://localhost:8888/
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

```bash
# Guardar: Ctrl+X → Y → Enter
```

### 5.2 - Verificar que se creó correctamente

```bash
# Ver primeras líneas
head -20 ~/prestashop/docker-compose/docker-compose.yml

# Ver últimas líneas
tail -10 ~/prestashop/docker-compose/docker-compose.yml

# Validar sintaxis YAML
docker-compose config -f ~/prestashop/docker-compose/docker-compose.yml > /dev/null && echo "✅ YAML válido" || echo "❌ Hay errores en YAML"
```

---

## ⚙️ PASO 6: CREAR CONFIGURACIÓN APACHE

### 6.1 - Crear archivo de configuración Apache

```bash
mkdir -p ~/prestashop/docker-compose/data/apache

nano ~/prestashop/docker-compose/data/apache/000-default.conf

# PEGA ESTO:
```

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

    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>

    # Cache estático
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access 1 year"
        ExpiresByType image/gif "access 1 year"
        ExpiresByType image/png "access 1 year"
        ExpiresByType text/css "access 1 month"
        ExpiresByType text/javascript "access 1 month"
        ExpiresByType application/javascript "access 1 month"
    </IfModule>

    # Seguridad
    <FilesMatch "^\.(htaccess|htpasswd|env)$">
        Deny from all
    </FilesMatch>

    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
</VirtualHost>
```

```bash
# Guardar: Ctrl+X → Y → Enter
```

### 6.2 - Crear configuración MySQL personalizada

```bash
nano ~/prestashop/docker-compose/mysql.cnf

# PEGA ESTO:
```

```ini
[mysqld]
# Configuración para PrestaShop + desarrollo

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

```bash
# Guardar: Ctrl+X → Y → Enter
```

---

## 🚀 PASO 7: LEVANTAR DOCKER (PRIMERO INTENTO)

### 7.1 - Entrar en directorio correcto

```bash
cd ~/prestashop/docker-compose

# Verificar que .env existe
ls -la .env
# Debe mostrar el archivo

# Verificar que docker-compose.yml existe
ls -la docker-compose.yml
# Debe mostrar el archivo
```

### 7.2 - Iniciar contenedores (PASO CRÍTICO)

```bash
# Mostrar qué va a hacer (sin ejecutar)
docker-compose config

# Si mostraste errores, PARA aquí y corrige el YAML

# Si no hay errores, iniciar
docker-compose up -d

# Esto descargará imágenes y creará contenedores
# PUEDE TARDAR 5-10 MINUTOS LA PRIMERA VEZ
```

### 7.3 - Ver el progreso (en otra terminal)

```bash
# Abre otra terminal y ejecuta:
docker-compose logs -f prestashop

# Espera a ver mensajes tipo:
# "AH00418: Core dumps will be logged to /tmp"
# "AH00505: [notice] Apache/2.4.xx (...) configured"
```

### 7.4 - Verificar que está todo OK

```bash
# Ver estado de contenedores
docker-compose ps

# Debe mostrar algo como:
# NAME              STATUS
# ps_mysql_8        Up X seconds (healthy)
# ps_main_187       Up X seconds
# ps_phpmyadmin     Up X seconds
```

---

## ✅ PASO 8: VERIFICAR PRESTASHOP FUNCIONA

### 8.1 - Configurar hosts local (IMPORTANTE)

```bash
# En FEDORA, editar /etc/hosts
sudo nano /etc/hosts

# Añadir estas líneas al final:
127.0.0.1 prestashop.local
127.0.0.1 admin.prestashop.local
127.0.0.1 localhost
```

```bash
# Guardar: Ctrl+X → Y → Enter
```

### 8.2 - Acceder a PrestaShop desde navegador

Abre tu navegador (Firefox, Chrome, etc.) y visita:

```
http://prestashop.local:8080
```

**¿Qué deberías ver?**
- Página con logo PrestaShop
- Posibilidad de seleccionar idioma (español/inglés)
- Mensaje: "Tienda en construcción" (normal si PS_DEV_MODE=1)

**Si ves esto → ✅ DOCKER FUNCIONA**

### 8.3 - Acceder a Admin

```
http://admin.prestashop.local:8080/admin_xyz123blinders
```

**Login:**
- Email: `admin@blinders-dev.local`
- Contraseña: `AdminBlinders2024!`

**¿Qué deberías ver?**
- Panel de administración (Dashboard)
- Menú lateral izquierdo
- Acceso a Catálogo → Productos

**Si ves esto → ✅ ADMIN FUNCIONA**

### 8.4 - Acceder a PhpMyAdmin

```
http://localhost:8888
```

**Login:**
- Usuario: `root`
- Contraseña: `RootSecurePass2024!`

**¿Qué deberías ver?**
- Panel de bases de datos
- Base de datos `prestashop_db` en el listado

**Si ves esto → ✅ MYSQL FUNCIONA**

---

## 🔍 PASO 9: VERIFICACIONES FINALES (MUY IMPORTANTE)

### 9.1 - Verificar volúmenes están mapeados correctamente

```bash
# Crear archivo de prueba
echo "test" > ~/prestashop/docker-compose/modules_dev/test.txt

# Entrar en contenedor y verificar que existe
docker exec ps_main_187 ls -la /var/www/html/modules/productbadges/

# Debería mostrar: test.txt

# Si existe → ✅ MAPEO OK
# Si NO existe → ❌ HAY PROBLEMA CON VOLÚMENES
```

### 9.2 - Verificar conexión MySQL desde PrestaShop

```bash
# Entrar en contenedor de PrestaShop
docker exec -it ps_main_187 bash

# Una vez dentro del contenedor:
cd /var/www/html
php -r "
require 'config/config.inc.php';
try {
    \$db = \Db::getInstance();
    echo '✅ Conexión a MySQL OK';
} catch (Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage();
}
"

# Exit para salir del contenedor
exit
```

### 9.3 - Verificar PHP version

```bash
docker exec ps_main_187 php -v

# Debe mostrar: PHP 8.1.X
```

### 9.4 - Verificar módulos están en el lugar correcto

```bash
# Desde host
ls -la ~/prestashop/docker-compose/modules_dev/

# Desde contenedor
docker exec ps_main_187 ls -la /var/www/html/modules/productbadges/

# Deben ser el MISMO contenido
```

---

## 🛑 PROBLEMAS COMUNES Y SOLUCIONES

### ❌ "Error: Cannot connect to Docker daemon"

```bash
# Solución:
sudo systemctl start docker
sudo usermod -aG docker $USER
newgrp docker

# Verificar
docker ps
```

### ❌ "docker-compose: command not found"

```bash
# Solución:
sudo dnf install -y docker-compose

# O usarlo así:
docker compose up -d
# (nota: sin guión)
```

### ❌ "Port 8080 is already allocated"

```bash
# Ver qué usa el puerto:
sudo lsof -i :8080

# O cambiar en docker-compose.yml:
# "8080:80" → "8090:80"
```

### ❌ "Cannot find module ../../../prestashop.php"

```bash
# Espera a que PrestaShop termine de instalar
# Puede tomar 2-3 minutos

docker-compose logs -f prestashop
# Busca: "AH00418:" para saber que está listo
```

### ❌ "MySQL connection failed"

```bash
# Espera a que MySQL arranque completamente
docker-compose logs mysql

# Si sigue fallando:
docker-compose down
docker volume rm prestashop_docker-compose_mysql_data
docker-compose up -d
```

### ❌ "PhpMyAdmin no carga"

```bash
# PhpMyAdmin es lento la primera vez
# Espera 30 segundos y actualiza el navegador

# Si sigue sin funcionar:
docker-compose restart phpmyadmin
docker-compose logs phpmyadmin
```

---

## 📊 PASO 10: DIAGRAMA DE CÓMO FUNCIONARÁ TODO

```
FEDORA 43 (Tu SO)
│
├─ DOCKER CONTAINER 1: MySQL 8.0
│  └─ Puerto: 3306 (solo interno, excepto localhost:3306)
│
├─ DOCKER CONTAINER 2: PrestaShop 1.7.8.11 + Apache + PHP 8.1
│  ├─ Puerto: 8080 (http://prestashop.local:8080)
│  ├─ Volumen: ~/prestashop/docker-compose/modules_dev → /var/www/html/modules/productbadges
│  └─ Volumen: ~/prestashop/docker-compose/themes_dev → /var/www/html/themes
│
├─ DOCKER CONTAINER 3: PhpMyAdmin
│  └─ Puerto: 8888 (http://localhost:8888)
│
└─ EN NATIVO (Sin Docker):
   ├─ Antigravity (instalado en Fedora)
   ├─ Gemini CLI (instalado en Fedora)
   ├─ VS Code (editor)
   ├─ Git (control de versiones)
   └─ ~/productbadges/ (tu repo local)
```

---

## ✨ RESUMEN: ¿QUÉ HEMOS LOGRADO?

```
✅ Docker instalado y funcionando sin sudo
✅ PrestaShop 1.7.8.11 corriendo en http://prestashop.local:8080
✅ PHP 8.1 dentro del contenedor
✅ MySQL 8.0 funcionando y accesible
✅ PhpMyAdmin en http://localhost:8888
✅ Volúmenes mapeados para desarrollo (modules_dev)
✅ Configuración de Apache optimizada
✅ SELinux en permissive (no interfiere)
✅ Firewall abierto en puertos necesarios
✅ Sistema listo para empezar a programar
```

---

## 🎯 PRÓXIMO PASO (DESPUÉS DE VERIFICAR TODO)

Una vez confirmes que:
1. ✅ PrestaShop abre en http://prestashop.local:8080
2. ✅ Admin funciona con login
3. ✅ PhpMyAdmin accesible
4. ✅ Archivos en modules_dev se ven desde contenedor

**ENTONCES pasaremos a:**

1. Configurar Antigravity para trabajar con el módulo
2. Crear estructura de carpetas del módulo productbadges
3. Empezar a codificar (tablas SQL, ObjectModel, etc.)
4. Todo con explicaciones paso a paso, SIN ASUMIR NADA

---

## 📞 CUANDO TERMINES ESTE PASO, DIME:

```
1. ¿Levantó Docker sin errores? (sí/no)
2. ¿PrestaShop carga en http://prestashop.local:8080? (sí/no)
3. ¿Admin funciona con el login? (sí/no)
4. ¿PhpMyAdmin abre? (sí/no)
5. Si hay algún error, cópialo exacto (completo)
6. Si todo OK, podemos pasar a ANTIGRAVITY + CÓDIGO
```

---

## 📝 CHEAT SHEET (Comandos rápidos)

```bash
# Ver status
cd ~/prestashop/docker-compose && docker-compose ps

# Ver logs
docker-compose logs -f prestashop

# Entrar en contenedor
docker exec -it ps_main_187 bash

# Parar todo
docker-compose down

# Eliminar volúmenes (CUIDADO: borra datos)
docker-compose down -v

# Reiniciar un servicio
docker-compose restart prestashop

# Ver IP del contenedor
docker inspect ps_main_187 | grep IPAddress

# Limpiar espacio (eliminar imágenes no usadas)
docker image prune -a
```

---

**AHORA, EJECUTA ESTO EN TU FEDORA 43 Y DIME QUÉ PASA. PASO A PASO.**

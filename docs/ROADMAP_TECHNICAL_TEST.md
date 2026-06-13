# Roadmap: Cómo superar la Prueba Técnica (Product Badges) a la primera

Este documento es una guía paso a paso, diseñada con una precisión quirúrgica, para superar una prueba técnica de desarrollo de módulos en PrestaShop 1.7 sin bloqueos ni pérdida de tiempo. Sigue estas fases y sus respectivas investigaciones para replicar el éxito.

---

## FASE 0: Entorno e Investigación Temprana (Día 1)

**Objetivo:** Evitar fallos de infraestructura que consumen tiempo (Error 500) y blindar a la IA contra alucinaciones de código.

1. **Setup del Entorno Docker:**
   - Levanta el entorno con `docker-compose`.
   - *Investigación Crítica:* Mapea la carpeta del módulo (`./modules_dev:/var/www/html/modules/productbadges`) pero **no mapees el directorio de caché (`/var/cache`)**.
   - *Instrucción de Seguridad:* Siempre que uses comandos dentro de Docker para borrar caché, ejecuta seguidamente: `docker exec -it <contenedor> chown -R www-data:www-data /var/www/html/var/cache/`.
2. **Blindar a la IA (`.cursorrules`):**
   - Crea el archivo `.cursorrules` en la raíz. Obliga a la IA a usar `ObjectModel`, a prohibir el uso de hooks obsoletos de PrestaShop 1.6, y a priorizar el sistema multilenguaje nativo.

---

## FASE 1: Scaffolding y Arquitectura de BD (Paso 1)

**Objetivo:** Crear la base del módulo y las tablas relacionales.

1. **El Archivo Principal (`productbadges.php`):**
   - Extiende de `Module`. Define nombre, versión y dependencias.
   - Registra los hooks obligatorios en el método `install()`: `displayBackOfficeHeader`, `displayHeader`, `actionProductFlagsModifier`, `displayAdminProductsExtra`, `actionProductUpdate`.
2. **Estructura SQL (`sql/install.php`):**
   - *Investigación Crítica:* Analiza los requisitos. Piden **Multilenguaje** y relación **Muchos a Muchos (M:M)**.
   - Crea **3 Tablas**:
     1. `ps_productbadges`: Configuración general (`bg_color`, `text_color`, `position`, `active`).
     2. `ps_productbadges_lang`: Exclusiva para el multilenguaje (`id_lang`, `text`).
     3. `ps_productbadges_product`: Tabla pivote M:M (`id_productbadge`, `id_product`).
   - *Trampa a evitar:* NO crees una tabla multitienda (`ps_productbadges_shop`) si el requerimiento dice "No es obligatorio que las badges difieran por tienda". Te ahorrará consultas vacías y bugs.

---

## FASE 2: ORM y Controlador de Backoffice (Paso 2)

**Objetivo:** Tener un CRUD completo para gestionar las insignias sin escribir SQL manual.

1. **El Modelo (`ProductBadgeModel.php`):**
   - Define la clase heredando de `ObjectModel`.
   - Establece `'multilang' => true` en la propiedad `$definition`.
   - Asigna `'lang' => true` al campo `text`. ¡Esto delega todas las consultas complejas a PrestaShop!
2. **El Controlador (`AdminProductBadgesController.php`):**
   - Configura el `$this->fields_list` para mostrar una cuadrícula con las insignias creadas.
   - Sobrescribe `renderForm()` usando `HelperForm`.
   - *Prueba de Fuego:* Entra al Backoffice. Al crear una insignia, verifica que el campo de texto tenga un desplegable de idiomas (ES/EN) a la derecha. Escribe un texto distinto para cada uno.

---

## FASE 3: Inyección en Ficha de Producto (Paso 3)

**Objetivo:** Permitir al administrador asignar varias insignias a un producto, respetando el límite máximo.

1. **Plantilla de Asignación (`admin_products_extra.tpl`):**
   - En `productbadges.php`, usa el hook `hookDisplayAdminProductsExtra`.
   - Extrae de la BD todas las insignias activas y pásalas a Smarty.
   - Renderiza un bloque HTML con `<input type="checkbox">` para cada insignia.
2. **Validación Frontend (UX):**
   - *El Toque Senior:* Inyecta un pequeño script JavaScript en el mismo `.tpl` que cuente cuántos checkboxes están marcados. Si llegan al límite definido en la configuración (`PRODUCTBADGES_MAX_ITEMS`), desactiva el resto dinámicamente.
3. **Persistencia:**
   - En `hookActionProductUpdate`, captura el array de checkboxes y haz un barrido en la tabla `ps_productbadges_product` (elimina asignaciones viejas e inserta las nuevas).

---

## FASE 4: Renderizado Nativo Frontend (Paso 4)

**Objetivo:** Pintar las etiquetas sobre las imágenes de los productos en la tienda pública sin modificar las plantillas del tema (Theme overrides).

1. **Investigación de Hooks Nativos:**
   - PrestaShop 1.7 renderiza las etiquetas (como "Rebaja" o "Nuevo") iterando un array nativo (`$product.flags`).
   - Usa el hook **`actionProductFlagsModifier`**.
2. **Inyección Lógica (`hookActionProductFlagsModifier`):**
   - Captura el `$id_lang` actual (vital para que cambie el idioma en el frontend).
   - Haz un `INNER JOIN` entre tus 3 tablas para sacar qué etiquetas activas tiene asignadas ese producto.
   - Inserta tu insignia en el array nativo:
     ```php
     $params['flags']['badge-1'] = ['type' => 'badge-1', 'label' => $badge['text']];
     ```
3. **Inyección Visual (`hookDisplayHeader`):**
   - Usa la BD para extraer los colores (`bg_color`, `text_color`) de todas las etiquetas activas.
   - Genera una etiqueta `<style>` con clases `.product-flag.badge-1 { background-color: #f00; }`.
   - Si la etiqueta es `top-right`, aplica un `float: right;` o `align-self: flex-end;` mediante CSS.

---

## FASE 5: Entrega y Limpieza

**Objetivo:** Entregar un repositorio inmaculado y profesional.

1. **Aislamiento:** Copia únicamente el directorio `/modules/productbadges/` a la raíz del repositorio de entrega.
2. **Gitignore Estratégico:** Nunca subas entornos Docker (`prestashop/`, `docker-compose/`) si no se requiere.
3. **Auditoría IA:** Aporta un `IA.md` sincero. Detalla qué automatizó la IA (el boilerplate del HelperForm) y qué errores evitaste gracias a tu criterio de desarrollador (como desechar hooks deprecados o investigar fallos de permisos).

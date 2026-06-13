# Product Badges - Módulo PrestaShop 1.7

Este módulo permite a los administradores de la tienda crear y asignar "Etiquetas Visuales" (Badges) dinámicas y personalizables a los productos (por ejemplo, "NUEVO", "OFERTA LIMITADA", "EXCLUSIVO").

Desarrollado y probado sobre **PrestaShop 1.7.8.11** y **PHP 7.4.33**.

## Instalación

1. Clona este repositorio o descarga el código fuente.
2. Copia la carpeta `productbadges/modules/productbadges/` en el directorio `modules/` de tu instalación de PrestaShop.
3. Dirígete al Backoffice de PrestaShop, entra en la sección **Módulos > Gestor de módulos**.
4. Busca "Product Badges" y haz clic en **Instalar**.
5. ¡Listo! El módulo instalará automáticamente las tablas necesarias en la base de datos y registrará los hooks correspondientes.

## Uso

1. **Configuración Global:** En la pantalla de configuración del módulo puedes activar/desactivar el sistema completo, elegir si las insignias se muestran en el listado y/o en la ficha del producto, y limitar el número máximo de insignias mostradas por producto (para evitar sobrecargar el diseño).
2. **Crear Etiquetas:** Ve al menú lateral **Catálogo > Product Badges**. Aquí podrás crear nuevas etiquetas, especificando su texto, color de fondo, color del texto y la posición (Top Left o Top Right). *Nota: Para usar el multilenguaje, simplemente haz clic en el selector de idiomas (ES/EN) dentro de la caja de texto al crear la etiqueta.*
3. **Asignar a Productos:** Ve a la edición de cualquier producto en **Catálogo > Productos**. En la pestaña "Módulos", verás un bloque llamado "Product Badges" donde podrás marcar con casillas de verificación las insignias que quieres aplicar a ese producto.

## Decisiones Técnicas Relevantes

### 1. ObjectModel y Arquitectura de BD (Multilenguaje Integrado)
Se optó por utilizar la clase nativa `ObjectModel` de PrestaShop (`ProductBadgeModel`). Esto permitió:
- Delegar la persistencia de datos (CRUD) al Core de PrestaShop.
- Crear una tabla separada automática `_lang` para aislar las traducciones de texto (`multilang => true`).
- Aprovechar `HelperForm` y `HelperList` para generar el panel de administración con validaciones del lado del servidor (server-side) integradas sin necesidad de escribir HTML manualmente.

### 2. Frontend: Hook `actionProductFlagsModifier`
En lugar de forzar la inyección de las etiquetas alterando las plantillas de forma manual o usando Hooks obsoletos (como `displayProductFlags`), se decidió utilizar el estándar de PrestaShop 1.7: el hook `actionProductFlagsModifier`.
- Esto garantiza compatibilidad al 100% con el tema nativo Classic (y cualquier tema bien programado) ya que inyecta los datos directamente en `$product.flags`.
- La personalización visual de los colores se soluciona inyectando un bloque CSS dinámico mediante el hook `displayHeader`.

### 3. Sanitización y Carga Eficiente de Assets
Se ha puesto especial foco en el rendimiento y la seguridad del módulo:
- **Validación y Escapado:** El procesamiento de datos en el Backoffice realiza un casteo estricto a entero `(int)` para los identificadores. Todas las plantillas Smarty utilizan el modificador de escape `|escape:'html':'UTF-8'` para prevenir vulnerabilidades XSS.
- **Eficiencia de Recursos:** En lugar de inyectar los scripts y hojas de estilo de manera indiscriminada, se utilizan los hooks `actionAdminControllerSetMedia` y `actionFrontControllerSetMedia` para condicionar la carga de `views/js/admin_product_tab.js` y `views/css/productbadges.css` **única y exclusivamente** cuando el controlador activo lo requiere (ej. `AdminProducts`).

### 4. Validación y Persistencia en el Producto
La asignación de Etiquetas-Productos es una relación M:M (Muchos a Muchos). En lugar de sobrecargar la tabla principal o guardar IDs separados por comas, se creó la tabla `ps_productbadges_product`. 
- Además, en la ficha de producto se agregó un validador en **JavaScript** (cargado de forma eficiente) que lee el límite `PRODUCTBADGES_MAX_ITEMS` y deshabilita automáticamente las casillas restantes si el usuario intenta seleccionar más etiquetas de las permitidas.

## Asunciones y Consideraciones

- **Soporte Multitienda (`_shop`):** En fases tempranas del desarrollo se intentó habilitar un entorno Multitienda completo incluyendo la tabla `ps_productbadges_shop`. Sin embargo, esto derivó en comportamientos inconsistentes debido a dependencias con el Core de Prestashop y configuraciones globales locales que generaban tablas de asociación vacías. Para priorizar la estabilidad, la tabla `_shop` fue descartada, por lo que las insignias se comparten de forma global entre todas las tiendas, aunque siguen siendo compatibles con un entorno Multitienda a nivel de base de datos general.
- **Limitación de Posicionamiento Múltiple:** Si se asignan varias etiquetas a un producto en la misma posición (por ejemplo, tres insignias en `top-right`), el módulo delega la responsabilidad del "stacking" (apilado) a las reglas Flexbox/CSS del tema. No fuerza posiciones absolutas que puedan pisarse entre sí.

## Directorios y Entregables

- El módulo se encuentra en: `productbadges/modules/productbadges/`
- En la raíz del repositorio se adjuntan los documentos de justificación técnica (`IA.md`, `README.md`) y el directorio `.gemini/` que contiene el log exacto, el raciocinio y los planes de implementación seguidos por el agente IA para completar el ejercicio.

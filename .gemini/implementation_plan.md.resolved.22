# Plan de Implementación: Mostrar Etiquetas en Frontend

## Análisis del Requisito

El *Subject* dice:
> 3. Mostrar las badges en frontend sobre la imagen del producto en:
>    1. Listado de categoría
>    2. Resultados de búsqueda y home
>    3. Ficha del producto

En PrestaShop 1.7, el tema nativo (Classic) renderiza las etiquetas "flags" (Rebajado, Nuevo, etc.) dinámicamente desde un array `$product.flags`. No existe el antiguo hook `displayProductFlags` en el núcleo de 1.7 para inyectar HTML directamente sobre la imagen. 

La forma profesional de PrestaShop 1.7 de añadir insignias es inyectando elementos en ese array nativo mediante el hook `actionProductFlagsModifier`. Así el tema se encarga de pintarlos con el estilo estándar y nosotros solo le damos color con CSS.

## User Review Required
> [!IMPORTANT]
> **Compatibilidad de Temas:** Al usar el hook nativo `actionProductFlagsModifier`, garantizamos que nuestras etiquetas funcionen en el **100% de los temas bien construidos** de PrestaShop 1.7. Si usáramos inyecciones raras de JavaScript o HTML a lo bruto, podría romperse en temas que no usen la estructura Classic.

## Proposed Changes

### 1. Actualización de Hooks (`productbadges.php`)
- **[MODIFY]** Cambiaremos el registro de `displayProductFlags` por `actionProductFlagsModifier` (para inyectar los datos) y `displayHeader` (para inyectar el CSS).
- **[NEW]** Método `hookActionProductFlagsModifier`:
  - Comprobará las variables de configuración (`PRODUCTBADGES_LIVE`, `PRODUCTBADGES_USE_LIST`, `PRODUCTBADGES_USE_PRODUCT`).
  - Identificará si estamos en la vista de lista o de producto analizando el controlador actual (`Context::getContext()->controller->php_self`).
  - Consultará qué insignias activas tiene asignadas ese producto.
  - Limitará el número máximo de etiquetas mostradas basándose en `PRODUCTBADGES_MAX_ITEMS`.
  - Añadirá la información nativa al array de `$flags` (ej. `type => 'productbadge-1'`, `label => 'OFERTA'`).
- **[NEW]** Método `hookDisplayHeader`:
  - Creará una etiqueta `<style>` inyectada dinámicamente en el `<head>` del HTML.
  - Para cada etiqueta generará una clase CSS (ej. `.product-flag.productbadge-1 { background-color: #f00; color: #fff; }`).
  - Para las etiquetas marcadas como `top-right`, añadirá directivas CSS para alinearlas a la derecha.

### 2. Plantilla Front (`views/templates/front/header.tpl` - *Opcional*)
Para mantener el código PHP limpio, crearemos un pequeño archivo Smarty `header.tpl` donde mandaremos todas las etiquetas activas para que se pinte el bloque `<style>`.

## Verification Plan
1. Resetear el módulo (para registrar los nuevos hooks).
2. Asignar etiquetas a productos de prueba ("Hummingbird printed t-shirt" tiene el ID 1 o 19).
3. Ir a la home y a la ficha de producto.
4. Verificaremos que la etiqueta aparece sobre la imagen con los colores y la posición elegidas.

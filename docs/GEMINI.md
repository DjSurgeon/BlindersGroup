## CONTEXTO DEL PROYECTO

**Módulo PrestaShop 1.7.8.11**: `productbadges`

Un módulo que gestiona "badges" (etiquetas visuales reutilizables) sobre productos de e-commerce.
- Ejemplo: "NUEVO", "OFERTA", "EXCLUSIVO", "ÚLTIMAS UNIDADES"
- Se muestran sobre la imagen del producto en: listados, búsqueda, home, ficha de producto
- Completamente multiidioma (español, inglés) y multitienda

```
Plataforma:     PrestaShop 1.7.8.11
PHP:            7.4.33
MySQL:          8.0.35
Docker:         Sí (contenedor prestashop)
Sistema:        Fedora 43
Herramientas IA: Antigravity (principal), Gemini CLI
IDE:            VS Code
Versionado:     Git + GitHub (repositorio público)
```

### 1.3 - Requisitos Funcionales

**Funcional:**
- [ ] Crear etiquetas: texto, color bg, color text, posición (top-left/top-right), estado on/off
- [ ] Asignar a productos: relación muchos-a-muchos
- [ ] Mostrar en frontend: listado, búsqueda, home, ficha
- [ ] Pantalla config: activar/desactivar, mostrar en listados, mostrar en ficha, max items por producto
- [ ] Multilenguaje: español + inglés mínimo
- [ ] Multitienda: funcionamiento coherente

**Técnico:**
- [ ] PHP 7.4.33
- [ ] Bootstrap = true
- [ ] Instalable/desinstalable limpiamente
- [ ] Sin tablas huérfanas, hooks colgados, pestañas sin desregistrar
- [ ] Sin dependencias Composer (excepto justificación)
- [ ] Sin librerías JS externas (solo jQuery que ya carga PS)

### 1.4 - Requisitos No-Funcionales

1. ✅ **Sanitización y escapado**: inputs en BD y plantillas
2. ✅ **Validación server-side**: no solo client
3. ✅ **APIs nativas de PrestaShop**: `HelperForm`, `HelperList`, `ObjectModel`, hooks, `$this->l()`, `_DB_PREFIX_`, `Db::getInstance()`, `pSQL()`
4. ✅ **Carga eficiente de assets**: CSS/JS solo donde necesiten
5. ✅ **Estructura limpia**: lógica admin en `ModuleAdminController` separado
6. ✅ **Historial de commits**: que cuenten cómo construiste (no un mega-commit final)

## DECISIONES ARQUITECTÓNICAS

### 4.1 - ObjectModel vs Custom Class

**Decisión:** Usaremos `ObjectModel` (clase `ProductBadge`)

**Por qué:**
- PrestaShop proporciona `ObjectModel` para ORM
- Automáticamente maneja validación, guardar, eliminar
- Hereda métodos como `save()`, `delete()`, `getById()`
- Es la forma "official" de hacerlo

### 4.2 - HelperForm vs Formulario Manual

**Decisión:** Usaremos `HelperForm` para crear/editar

**Por qué:**
- Genera formularios HTML automáticamente desde array de campos
- Maneja CSRF tokens automáticamente
- Validación integrada
- Es lo que usa PrestaShop admin

### 4.3 - HelperList vs Listado Manual

**Decisión:** Usaremos `HelperList` para el listado

**Por qué:**
- Genera tablas con paginación, search, sort
- Botones de acción (editar, eliminar)
- Checkbox para acciones en bloque
- Es el estándar de PS

### 4.4 - Hooks a Usar (Frontend)

**Para mostrar badges en productos:**
- `displayProductFlags` → En lista de productos (listado, búsqueda, home)
- `displayProductPriceBlock` → En ficha de producto (alternative si no existe mejor)

**Por qué estos:**
- Son hooks estándar que el tema debe soportar
- Se ejecutan automáticamente en los lugares correctos
- No necesitamos modificar templates del tema

## 🔒 SECCIÓN 6: SEGURIDAD (CRÍTICO)

### 6.1 - Sanitización de Inputs

**Regla:** SIEMPRE sanitizar datos del usuario ANTES de guardar en BD

**Ejemplos:**
```php
// ❌ MAL
$color = $_POST['color_bg'];
Db::getInstance()->insert('productbadges', ['color_bg' => $color]);

// ✅ BIEN
$color = pSQL($_POST['color_bg']);  // Escapa para SQL
Db::getInstance()->insert('productbadges', ['color_bg' => $color]);

// ✅ MEJOR (usando ObjectModel)
$badge = new ProductBadge();
$badge->color_bg = Validate::isColorHexValue($_POST['color_bg']) ? $_POST['color_bg'] : '#000000';
$badge->save();
```

**Métodos útiles:**
- `pSQL()` → Escapa para SQL
- `Validate::isColorHexValue()` → Valida hexadecimal
- `Validate::isInt()` → Valida enteros
- `Tools::getValue()` → Obtiene valor de GET/POST (más seguro que $_POST)
- `Tools::htmlentitiesUTF8()` → Escapa para HTML

### 6.2 - Escapado en Plantillas

**Regla:** SIEMPRE escapar variables en Smarty cuando se muestren en HTML

**Ejemplos:**
```smarty
{* ❌ MAL *}
<span style="color: {$badge->color_text}">{$badge->text}</span>

{* ✅ BIEN *}
<span style="color: {$badge->color_text|escape:'html'}">{$badge->text|escape:'html'}</span>

{* ✅ MEJOR (para atributos HTML) *}
<span class="product-badge" style="color: {$badge->color_text|escape:'htmlall'}" 
      data-badge-id="{$badge->id_productbadges|intval}">
    {$badge->text|escape:'html'}
</span>
```

### 6.3 - Validación Server-Side

**Regla:** NO confiar en validación client-side. SIEMPRE validar en server (PHP).

**Ejemplo:**
```php
// En AdminProductBadgesController

public function processAdd() {
    // Validar color_bg (debe ser #RRGGBB)
    if (!Validate::isColorHexValue(Tools::getValue('color_bg'))) {
        $this->errors[] = $this->l('Invalid background color format');
        return false;
    }
    
    // Validar position (solo top-left o top-right)
    $position = Tools::getValue('position');
    if (!in_array($position, ['top-left', 'top-right'])) {
        $this->errors[] = $this->l('Invalid position');
        return false;
    }
    
    // Si llegamos aquí, todo OK
    return parent::processAdd();
}
```

### 6.4 - CSRF Tokens

**Regla:** Los formularios HelperForm ya incluyen CSRF tokens. NO hacer nada manual.

### 6.5 - SQL Injection

**Regla:** SIEMPRE usar `pSQL()` o prepared statements. NUNCA concatenar SQL.

**Ejemplos:**
```php
// ❌ PELIGRO
$id = $_GET['id'];
$query = "SELECT * FROM badges WHERE id = $id";

// ✅ BIEN
$id = (int)$_GET['id'];
$query = "SELECT * FROM badges WHERE id = " . pSQL($id);

// ✅ MEJOR (Db::getInstance)
$badges = Db::getInstance()->executeS(
    "SELECT * FROM " . _DB_PREFIX_ . "productbadges WHERE id = " . (int)Tools::getValue('id')
);
```

---

## 📝 SECCIÓN 7: NORMAS DE CÓDIGO

### 7.1 - Estilo

- **PHP:** PSR-12 (con algunas excepciones de PrestaShop)
- **Indentación:** 4 espacios
- **Longitud línea:** máximo 120 caracteres (preferible 100)
- **Comentarios:** en inglés, pero CONSISTENTE

### 7.2 - Convenciones de Nombres

```php
// Clases: PascalCase
class ProductBadge extends ObjectModel { }

// Métodos: camelCase
public function getProductBadges() { }

// Propiedades: camelCase
public $id_productbadges;

// Constantes: UPPER_SNAKE_CASE
const CONFIG_KEY_ACTIVE = 'PRODUCTBADGES_LIVE';

// Funciones privadas: _camelCase (convención PS)
private function _validateColor() { }
```

### 7.3 - Comentarios Obligatorios

```php
/**
 * Obtiene todas las badges de un producto
 * 
 * @param int $id_product ID del producto
 * @param int $id_shop ID de la tienda (opcional, usa contexto)
 * @return array Array de ProductBadge
 * 
 * @throws Exception Si el producto no existe
 */
public function getBadgesForProduct($id_product, $id_shop = null) {
    // implementation
}
```

## 🧪 SECCIÓN 8: TESTING

### 8.1 - Manual Testing Checklist

```
[ ] Instalar módulo sin errores
[ ] Abrir admin → Catálogo → (debería haber opción de badges)
[ ] Crear 1 badge con texto "TEST", color #FF0000
[ ] Editar la badge, cambiar a color #00FF00
[ ] Asignar badge a un producto (ej. ID 5)
[ ] Ver en tienda que aparece la badge
[ ] Desasignar de producto
[ ] Eliminar badge
[ ] Verificar en BD que todo se limpió
[ ] Desinstalar módulo limpiamente
[ ] Verificar sin tablas huérfanas
```

### 8.2 - Verificar en BD (Queries útiles)

```sql
-- Ver estructuras
SHOW TABLES LIKE 'ps_productbadges%';
DESCRIBE ps_productbadges;
DESCRIBE ps_productbadges_shop;
DESCRIBE ps_productbadges_lang;
DESCRIBE ps_productbadges_product;

-- Ver datos
SELECT * FROM ps_productbadges;
SELECT * FROM ps_productbadges_lang;
SELECT * FROM ps_productbadges_product;

-- Después de desinstalar (debería estar vacío)
SHOW TABLES LIKE 'ps_productbadges%';
SELECT COUNT(*) FROM ps_configuration WHERE name LIKE 'PRODUCTBADGES_%';
```

---

## 🎯 SECCIÓN 9: CRITERIOS DE ÉXITO

**Requisitos Funcionales:**
- [ ] CRUD de badges funcional
- [ ] Badges aparecen en tienda (listado, búsqueda, ficha)
- [ ] Multiidioma (es, en) funcional
- [ ] Multitienda sin romper
- [ ] Pantalla de configuración funcional

**Requisitos Técnicos:**
- [ ] Código usa APIs nativas PS (ObjectModel, HelperForm, etc.)
- [ ] Sanitización en inputs, escapado en outputs
- [ ] Validación server-side en todos los formularios
- [ ] Índices en BD para optimización
- [ ] Assets (CSS/JS) cargados solo donde necesiten
- [ ] ModuleAdminController separado de archivo principal
- [ ] Commit history claro y narrativo

**Código Limpio:**
- [ ] Sin errores PHP (strict mode)
- [ ] Sin warnings
- [ ] Sin notices
- [ ] Sin tablas huérfanas tras desinstalar
- [ ] Sin hooks colgados
- [ ] Sin pestañas admin sin desregistrar

---

## 📚 SECCIÓN 10: REFERENCIAS Y RECURSOS

### PrestaShop 1.7 API
- DevDocs: https://devdocs.prestashop.com/
- ObjectModel: https://devdocs.prestashop.com/1.7/basics/database/
- Hooks: https://devdocs.prestashop.com/1.7/modules/concepts/hooks/
- HelperForm: https://devdocs.prestashop.com/1.7/basics/forms/helper-form/
- HelperList: https://devdocs.prestashop.com/1.7/basics/forms/helper-list/

### Seguridad
- Input validation: https://devdocs.prestashop.com/1.7/development/coding-standards/security/
- SQL security: https://devdocs.prestashop.com/1.7/development/coding-standards/sql/

### Este Proyecto
- Análisis BD: Consultar `ANALISIS_BD_PRODUCTBADGES.md`
- Setup Docker: Consultar `SETUP_DOCKER_PRESTASHOP_DEFINITIVO.md`

---

### Code
- [ ] Sin errores PHP (ejecutar con `php -l`)
- [ ] Sin warnings PSR-12
- [ ] Todos los métodos documentados con PHPDoc
- [ ] Sanitización en TODOS los inputs
- [ ] Escapado en TODOS los outputs

### BD
- [ ] install.php crea tablas correctamente
- [ ] uninstall.php limpia TODO
- [ ] Sin tablas huérfanas tras desinstalar
- [ ] Índices creados para optimización
- [ ] Foreign keys con CASCADE

### Seguridad
- [ ] pSQL() en todas las queries
- [ ] Validate::* para validaciones
- [ ] CSRF tokens (HelperForm ya lo hace)
- [ ] Logs de errores (no mostrar a usuario)

### Documentación
- [ ] README.md completo (instalación, decisiones, asupciones)
- [ ] IA.md completado (herramientas, errores detectados)
- [ ] Comentarios en código
- [ ] Commits con historial claro

### Repositorio
- [ ] GitHub repo público
- [ ] .gitignore correcto
- [ ] Directorios de IA (.claude/, .cursor/) **NO en .gitignore**
- [ ] README + IA.md en raíz
- [ ] Módulo en carpeta `modules/productbadges/`

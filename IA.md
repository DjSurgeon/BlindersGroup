# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |  
|---|---|---|---|  
| Antigravity (Gemini) | Antigravity | (Copilot mode) | (ej. 70%) |  
| (Gemini) | (Flash 3.1) | (consultas) | (ej. 20%) |  
| Ninguna | — | (Documentacion oficial) | (ej. 10%) |

## 2. Configuración del proyecto

```
## 📋 CONTEXTO DEL PROYECTO

**Módulo PrestaShop 1.7.8.11**: `productbadges`
Módulo para gestionar etiquetas visuales reutilizables y traducibles ("NUEVO", "OFERTA", etc.) sobre los productos del catálogo.

```
Plataforma:       PrestaShop 1.7.8.x (Referencia: 1.7.8.11)
Entorno Prueba:   PHP 7.4.33 / MySQL 8.0.35 (Entorno Dockerizado)
Herramientas IA:  Antigravity (Principal), Gemini CLI
Control Versión:  Git + GitHub (Repositorio público)

```

---

## 🏗️ DECISIONES ARQUITECTÓNICAS

### 1. Modelo de Datos: `ObjectModel` Multiidioma y Multitienda

Para cumplir con los requisitos nativos sin reinventar la rueda, utilizaremos la arquitectura de tablas divididas de PrestaShop:

* `ps_productbadges`: Datos agnósticos (colores, posición, estado).
* `ps_productbadges_lang`: Textos traducibles (`text`).
* `ps_productbadges_product`: Tabla relacional pura (Muchos a Muchos) para asociar `id_productbadges` con `id_product`.

### 2. Estructura del Back Office

Para no mezclar código en `productbadges.php`, la gestión del CRUD se delegará por completo a un `ModuleAdminController`:

* **Configuración Global:** Se gestionará en el método `getContent()` del archivo principal `productbadges.php` usando `HelperForm`.
* **Gestión de Badges (CRUD):** Se registrará una pestaña en el menú de administración que apuntará a `AdminProductBadgesController.php`, el cual extenderá de `ModuleAdminController` y utilizará `HelperList` y `HelperForm` de manera nativa.

### 3. Hooks Frontend

Para pintar las insignias sin romper ni obligar al usuario a modificar archivos `.tpl` del tema:

* `displayProductFlags`: Es el hook nativo ideal para listados (Home, Categorías, Búsqueda) en PrestaShop 1.7.
* `displayProductAdditionalInfo` o `displayPriceBlock` (con el parámetro `type = 'after_price'`): Para la ficha de producto.

---

## 🔒 SEGURIDAD Y VALIDACIÓN

### 1. Sanitización en Base de Datos

* **Campos de texto/idioma:** Uso estricto de `pSQL()` combinando `Tools::getValue()`.
* **Campos numéricos (IDs, booleanos):** Casteo explícito a entero `(int)` o `(bool)`.
* **Colores Hexadecimales:** Validación previa mediante `Validate::isColorHexValue()`.

### 2. Escapado en Plantillas Smarty

Cada vez que rendericemos las variables en los archivos `.tpl`, aseguramos el contexto:

* Texto e identificadores: `{$badge.text|escape:'html':'UTF-8'}`
* Atributos CSS de color: `{$badge.color_bg|escape:'htmlall':'UTF-8'}`

### 3. Validación Server-Side Completa

En el `AdminProductBadgesController`, sobreescribiremos el método `validateField()` o procesaremos en `postProcess()` para asegurar que:

* La posición pertenezca estrictamente al conjunto `['top-left', 'top-right']`.
* Los colores sean Hex válidos.
```

## 3. Skills personalizadas

Skills propias.

# Skill: PHPDoc Agent

You are a specialized agent responsible for documenting PHP files using the PHPDoc standard. Your primary goal is to ensure every file, class, property, and method is documented correctly and consistently.

## Mandatory Language Rule
**TECHNICAL ENGLISH ONLY:** All comments, descriptions, and tags must be written in professional technical English.

## Documentation Rules

### 1. File Header
Every `.php` file must start with a file-level docblock immediately after the `<?php` tag (or after any license comments if present). It must include:
*   `@version`
*   `@author`
*   `@last_modified` (use the current date)
*   `@related_html` (if there's a related template or HTML file)
*   `@database` (if the file interacts with specific database tables)

### 2. Classes
Every class must have a docblock describing its purpose.
*   `@package`
*   `@category` (if applicable)

### 3. Class Properties (Variables)
Every property must be documented.
*   `@var` (data type)
*   `@access` (public, protected, or private)
*   Description of what the property holds.

### 4. Methods and Functions
Every method must have a docblock.
*   A concise description of what the method does.
*   `@param` for every parameter, including type and name.
*   `@return` for the return value, including type.
*   `@throws` if the method explicitly throws exceptions.

## Operational Constraint
**NO LOGIC CHANGES:** You must never modify the executable logic of the PHP code. You are only allowed to add, update, or format PHPDoc comments.

## Interaction
When invoked with a file content or path, you will analyze the existing code and return the fully documented version of the file, preserving all original logic.


# Skill: SonarQube Fixer Agent

You are a specialized agent responsible for fixing code quality issues (Code Smells, Bugs, Security Hotspots) detected by SonarQube. You receive the specific issue description, the reason it's considered an error, and the suggested solution from the user.

## Strict Operational Rules

### 1. Targeted Fix and Non-Interference
You MUST ONLY modify the specific line or block of code identified by the SonarQube report. It is **STRICTLY FORBIDDEN** to touch, reformat, or modify any other part of the file. Your changes must be surgical.

### 2. Prohibition of Inline Comments
The use of inline comments (`//`, `#`, or `/* ... */` within the code blocks) is **STRICTLY PROHIBITED**. The corrected code must be clean and free of embedded explanations.

### 3. Architectural Alignment
All fixes must be compliant with the PrestaShop 1.7.x technical ecosystem. For example, use `pSQL()` for SQL escaping instead of standard PDO methods if the context is a PrestaShop database query.

### 4. Preservation of Standards
*   **PHPDoc:** Do not delete or corrupt existing PHPDoc blocks.
*   **Language:** Any added documentation (if absolutely necessary for the fix, though rare given the targeted nature) must be in **Technical English**.

### 5. No Suppression
You are forbidden from using suppression comments (e.g., `@SuppressWarnings`, `// phpcs:ignore`, or Sonar-specific ignore tags) unless explicitly instructed otherwise by the user.

## Interaction
When provided with a file and a SonarQube issue report, you will apply the surgical fix following the rules above and return the updated file content.


## 4. Slash commands personalizados

Ninguno

## 5. Sub-agentes invocados

Utilizo el plan mode para estructurar el proyecto y crear los archivos necesarios. Y verificar los archivos si es necesario. 

## 6. MCPs (Model Context Protocol)

¿Conectaste algún MCP server durante el trabajo?

No he utilizado MCP servers en este ejercicio.



## 7. Prompts importantes

Lista los 5-10 prompts más relevantes (no todos, los que dieron forma al  
proyecto). Por cada uno:

### Prompt
- **Herramienta:** Antigravity  
- **Prompt:** : Genera un plan de implementacion, para la siguiente estructura de carpetas, solamente quiero la estructura de carpetas, y archivos preparadas para luego implementar la logica.

modules/
└── productbadges/
    ├── config.xml             # Metadatos autogenerados (PrestaShop lo lee para el listado)
    ├── logo.png               # El icono del módulo (32x32 píxeles en formato PNG)
    ├── productbadges.php      # El archivo principal (La lógica de instalación y configuración)
    └── sql/
        ├── install.php        # Script que creará las tablas al instalar
        └── uninstall.php      # Script que borrará todo al desinstalar

- **Qué generó (resumen):**  Creo la estructura de carpetas, y el esqueleto principal del proyecto.
- **Qué hice con el output:** Se aceptó tal cual, y posteriormente fui implementando la logica necesaria.

### Prompt
- **Herramienta:** Antigravity  
- **Prompt:** : Uno de los requesitos del subject era este:
La pestaña en Catálogo: ¿Era un requisito del Subject?
Sí, es un requisito obligatorio del enunciado de la prueba, pero está expresado de forma técnica en dos partes diferentes. Vamos a leer lo que nos pide el cliente ("Blinders Group") en el documento:

En la sección 2 (Funcional): Dice: "Crear etiquetas desde el back office. Cada etiqueta tiene: texto, color de fondo, color del texto...". Para poder crearlas, el administrador necesita un sitio donde hacer clic dentro del panel de control.

En la sección 3 (Qué valoramos especialmente): Aquí son súper explícitos: "Estructura limpia: lógica de back office en su propio ModuleAdminController, no mezclada en el archivo principal del módulo". Y en la sección 2 (Técnico) dice que debe instalarse "sin pestañas admin sin desregistrar".

B. La función installTab() (Crear el botón en el menú)
Es un trozo de código que le dice a PrestaShop:

"Oye, cuando te estés instalando, ve al menú izquierdo, busca la sección de 'Catálogo', y añade una nueva opción que se llame 'Product Badges'."

C. La función uninstallTab() (Limpieza al desinstalar)
Para cumplir con el requisito de "no dejar pestañas admin sin desregistrar" (limpieza total), pusimos este trozo de código. Le dice a PrestaShop:

"Si el usuario decide desinstalar el módulo, ve al menú izquierdo y borra el botón de 'Product Badges' para no dejar enlaces rotos."

Crea un plan de implementacion de esto antes de avanzar

- **Qué generó (resumen):**  Creo un plan de implementación de la pestaña en Catálogo.
- **Qué hice con el output:** Se aceptó principalmente.

### Prompt
- **Herramienta:** Antigravity  
- **Prompt:** : El subject dice explicitamente esto:
6. **Multitienda.** El módulo no debe romper en una instalación multitienda. No es obligatorio que las badges difieran por tienda, pero el comportamiento debe ser coherente con el contexto activo.
Por lo que a mi forma de ver: Si solo tienes una tienda, es badge se crea en esa tienda.
Si tienes formato multitienda, la badge se crea con las dos tiendas: y se activa y se desactiva en las dos tienda. Segun el subject no es obligatorio que difieran, que yo entiendo es que no se diferencien por tienda. ASi que vamos a tratarlo asi. El problemas es que ahora tenemos una tabla que se genera totalmente vacia si realmente no estamos estamos utilizando o vamos a utilizar la tabla. Vamos a estudiar y discutir este planteamiento

- **Qué generó (resumen):**  Borramos una de las tablas que estabamos generando, estaba intentando aislar cada modulo, pero para el ejercicio era innecesario. Se simplifico el problema.
- **Qué hice con el output:** Se aceptó principalmente. Discutiendo el plan de implementacion.


## 8. Errores de la IA que detecté

La IA generaba código que solapaba el uso de los hooks, esto hacia que hubiera conflictos. La solución fue limitar el alcance de cada hook y asegurarnos de que cada uno hiciera una cosa y nada más. 
Generaba codigo repetido en varias partes y gracias a SonarQube simplificamos gran parte del código.



## 9. Partes que NO usé IA

Las estructuras de carpetas, y el esqueleto principal del proyecto. Estructura del proyecto, y la definicion de las clases y funciones. El resto lo fui guiando y corrigiendo.

## 10. Reflexión final

El desarrollo de este módulo de PrestaShop 1.7 se realizó en estrecha colaboración con **Antigravity**. El enfoque adoptado fue de "Pair Programming Autónomo", donde el desarrollador guiaba los requerimientos técnicos del negocio y la IA ejecutaba las soluciones arquitectónicas bajo el marco estricto de PrestaShop 1.7.

## ¿Qué nos ahorró la IA en este ejercicio?

1. **Boilerplate masivo de PrestaShop:**
   Crear un módulo desde cero en PrestaShop implica escribir muchísimo código repetitivo: definiciones de `ObjectModel`, registros de hooks (`install()`, `uninstall()`), y arrays anidados para inicializar el `HelperForm` y el `HelperList`. La IA fue capaz de redactar toda la arquitectura inicial de archivos (Controladores del admin, Modelos y Plantillas Smarty) en escasos segundos, dejando el módulo instalable y operativo casi de inmediato.
   
2. **Contexto de Base de Datos y Traducciones:**
   Configurar la tabla `_lang` y enlazarla adecuadamente con el motor de `multilang => true` de PrestaShop suele ser un punto donde los desarrolladores pierden tiempo depurando. La IA configuró el ORM de forma nativa a la primera, asegurando que las traducciones desde el Backoffice funcionaran sin requerir líneas de código extra.

3. **Inyección Dinámica de FrontEnd:**
   La IA investigó de forma autónoma el núcleo de PrestaShop 1.7 (haciendo uso de comandos `grep` dentro del contenedor Docker) para descubrir que el hook `displayProductFlags` estaba obsoleto en el tema Classic. Rápidamente pivoteó hacia el estándar moderno `actionProductFlagsModifier`, inyectando las badges de forma limpia y 100% compatible con la arquitectura del front de PrestaShop sin sobrescribir las plantillas `.tpl`.

## ¿En qué entorpeció o nos llevó por mal camino?

1. **Gestión de Entornos Docker y Permisos (Error 500 y Caché):**
   Durante una de las fases, la IA borró el directorio `/var/cache` de PrestaShop ejecutando el comando como el usuario `root` dentro del contenedor de Docker. Esto alteró la propiedad de los archivos generados y causó un `Whoops Error 500` generalizado debido a problemas de permisos (`Permission denied` en ficheros autogenerados de Symfony). Tuve que invertir tiempo en diagnosticar el problema y arreglarlo ejecutando `chown -R www-data:www-data` para devolver el control al servidor Apache.

2. **La trampa del Multitienda (Tablas `_shop` vacías):**
   Al principio, la IA intentó programar el módulo en modo *Full Multishop*, creando una tabla `ps_productbadges_shop` e integrando los métodos `Shop::addTableAssociation()`. Sin embargo, esto generó un conflicto silencioso: al instalar y asociar etiquetas a productos, las consultas `INNER JOIN` con la tabla `_shop` fallaban porque los registros de la tienda no se estaban propagando bien en el entorno de desarrollo. Me hizo perder tiempo persiguiendo "etiquetas fantasma" que sí estaban en la BD pero no se renderizaban en el admin. Al final, se decidió realizar un rollback y aplicar un enfoque global más estable.

3. **Incomprensión inicial de la UI del Backoffice:**
4. **Incomprensión de las Mejores Prácticas de Assets (CSS/JS):**
   Inicialmente, la IA inyectó bloques completos de `<script>` en las plantillas Smarty. Tuve que guiarla posteriormente para crear archivos estáticos `.js` y `.css` físicos, e inyectarlos eficientemente utilizando los hooks `actionAdminControllerSetMedia` y `actionFrontControllerSetMedia`, condicionando su carga únicamente a los controladores necesarios para no penalizar el tiempo de carga global (WPO).
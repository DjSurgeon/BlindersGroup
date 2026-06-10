**Blinders Group · Equipo de Programación**

Hola,

Gracias por tu interés en sumarte a Blinders Group. Este documento contiene la prueba técnica para la posición de Programador/a especializado/a en e-commerce. Léelo completo antes de empezar.

## 1\. Resumen

Vas a construir un módulo para **PrestaShop 1.7** y lo subirás a un repositorio público de GitHub junto con la documentación que pedimos.

Tiempo estimado de trabajo efectivo: **3-4 horas**.

## 2\. Qué tienes que construir

Un módulo PrestaShop 1.7 llamado productbadges que permita gestionar **etiquetas visuales reutilizables** para los productos del catálogo (tipo "NUEVO", "OFERTA", "EXCLUSIVO", "ÚLTIMAS UNIDADES", etc.).

### Funcional

1. **Crear etiquetas desde el back office.** Cada etiqueta tiene: texto, color de fondo, color del texto, posición (esquina superior izquierda o derecha), estado activo/inactivo.  
2. **Asignar etiquetas a productos.** Relación muchos a muchos: un producto puede tener varias etiquetas, una etiqueta puede aplicarse a varios productos.  
3. **Mostrar las badges en frontend** sobre la imagen del producto en:  
   1. Listado de categoría  
   2. Resultados de búsqueda y home (si el tema activo las soporta)  
   3. Ficha del producto  
4. **Pantalla de configuración del módulo** con:  
   1. Activar/desactivar global  
   2. Mostrar en listados (sí/no)  
   3. Mostrar en ficha de producto (sí/no)  
   4. Número máximo de badges visibles por producto  
5. **Multilenguaje.** El texto de la badge debe ser traducible a los idiomas activos en la tienda. Mínimo soporte para es y en.  
6. **Multitienda.** El módulo no debe romper en una instalación multitienda. No es obligatorio que las badges difieran por tienda, pero el comportamiento debe ser coherente con el contexto activo.

### Técnico

* **PrestaShop 1.7.8.x** (asume 1.7.8.11 como referencia).  
* **PHP 7.4 o 8.1.** Indica en el README la versión sobre la que has probado.  
* Módulo con bootstrap \= true.  
* **Instalable y desinstalable limpiamente** desde el back office: sin tablas huérfanas, sin hooks colgados, sin pestañas admin sin desregistrar.  
* Sin dependencias de Composer salvo justificación.  
* Sin librerías JS externas más allá de jQuery (que PrestaShop ya carga).

## 3\. Qué valoramos especialmente

No buscamos un módulo perfecto, buscamos ver tu **criterio técnico**. En particular:

* **Sanitización y escapado** correcto de inputs (textos, colores, IDs) tanto en BD como en plantillas.  
* **Validación server-side**, no solo client-side.  
* Uso correcto de las **APIs propias de PrestaShop**: HelperForm, HelperList, ObjectModel, sistema de hooks, $this-\>l() para textos, \_DB\_PREFIX\_, Db::getInstance() con pSQL() o consultas preparadas.  
* **Carga eficiente de assets** (CSS/JS solo donde se necesiten).  
* **Estructura limpia**: lógica de back office en su propio ModuleAdminController, no mezclada en el archivo principal del módulo.  
* **Historial de commits** que cuente cómo fuiste construyendo el módulo (no un único commit final con todo).

## 4\. Estructura del repositorio

Esperamos que tu repo tenga esta estructura interna:

├── README.md  
├── IA.md  
├── modules/  
│   └── productbadges/  
│       ├── productbadges.php  
│       ├── config.xml  
│       ├── logo.png  
│       ├── sql/  
│       │   ├── install.php  
│       │   └── uninstall.php  
│       ├── controllers/  
│       │   └── admin/  
│       │       └── AdminProductBadgesController.php  
│       ├── views/  
│       │   ├── templates/  
│       │   ├── css/  
│       │   └── js/  
│       └── translations/  
├── .claude/            ← si usaste Claude Code  
├── .opencode/          ← si usaste OpenCode  
├── .cursor/            ← si usaste Cursor  
└── .gitignore

**Importante:** los directorios de configuración de IA (.claude/, .opencode/, .cursor/, etc.) **NO deben ir en** .gitignore. Queremos verlos en el repo.

El README.md debe explicar al menos: cómo instalar el módulo, decisiones técnicas relevantes que tomaste, qué dejaste fuera y por qué.

## 5\. Sobre el uso de IA

**Puedes y debes usar las herramientas de IA que utilices normalmente.** Claude Code, OpenCode, Copilot, ChatGPT, Cursor, Codeium, lo que sea. No hay penalización por usar mucha IA. **Sí la hay por entregar código que no entiendes.**

Lo único que te pedimos:

1. Que toda la **configuración de IA del proyecto** quede dentro del repo: CLAUDE.md o AGENTS.md, archivos settings.json, skills personalizadas, slash commands, sub-agentes, configuración de MCPs. Todo lo que apliques en tu flujo.

1. Que rellenes el IA.md con la plantilla del apartado 6\. **La sección 8 (errores detectados en el output de la IA) es la más importante.**

1. Que puedas **defender línea a línea cualquier parte del código que entregues**. Si no entiendes algo, no lo entregues.

## 6\. Plantilla IA.md

Copia este bloque tal cual en tu repo como IA.md y rellénalo. Mantén la estructura de secciones.

\# Uso de IA en este proyecto

\#\# 1\. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |  
|---|---|---|---|  
| (ej. Claude Code CLI) | (ej. 4.7 Opus) | (ej. terminal en VS Code) | (ej. 60%) |  
| (ej. ChatGPT web) | (ej. GPT-5) | (consultas puntuales) | (ej. 10%) |  
| Ninguna | — | (yo mismo, sin IA) | (ej. 30%) |

\#\# 2\. Configuración del proyecto

\#\#\# CLAUDE.md / AGENTS.md  
¿Tienes archivo de instrucciones a nivel proyecto? Pega aquí su contenido o  
linka al fichero del repo. Si no tienes, escribe "ninguno" y explica por qué.

\#\#\# settings.json u otra configuración equivalente  
¿Cambiaste permisos, modelo activo, herramientas habilitadas/bloqueadas?  
Adjunta el archivo al repo y referencia aquí su ruta.

\#\# 3\. Skills personalizadas

Si usaste skills (propias o de terceros), lístalas:

\- Nombre del skill  
\- Origen (propia, de la comunidad, adaptada)  
\- Para qué la usaste en este proyecto  
\- Ruta dentro del repo

Si no usaste ninguna, "ninguna".

\#\# 4\. Slash commands personalizados

Si tienes comandos custom (\`/revisa-modulo\`, \`/genera-hook\`...), lístalos  
de la misma forma. Deben estar en \`.claude/commands/\` o equivalente.

\#\# 5\. Sub-agentes invocados

¿Usaste Task tool, Plan Mode, sub-agentes? Indica para qué los usaste y si  
guardaste sus definiciones en el repo (\`.claude/agents/\`).

\#\# 6\. MCPs (Model Context Protocol)

¿Conectaste algún MCP server durante el trabajo?

| MCP | Para qué lo usaste | ¿Qué te aportó? |  
|---|---|---|  
| (ej. filesystem) | (lectura del repo) | (navegación más rápida) |  
| (ej. github) | — | (no lo usé) |  
| (ej. context7) | (docs de PrestaShop) | (evitó alucinaciones en hooks) |

Si no usaste ninguno, dilo y explica si lo habrías usado con más tiempo.

\#\# 7\. Prompts importantes

Lista los 5-10 prompts más relevantes (no todos, los que dieron forma al  
proyecto). Por cada uno:

\#\#\# Prompt N  
\- \*\*Herramienta:\*\* (Claude Code / ChatGPT / ...)  
\- \*\*Prompt:\*\* (copia textual)  
\- \*\*Qué generó (resumen):\*\*  
\- \*\*Qué hice con el output:\*\* (acepté tal cual / modifiqué X / descarté  
  porque...)

\#\# 8\. Errores de la IA que detecté

Lista bugs, invenciones, malas prácticas o riesgos de seguridad que la IA  
introdujo y tú corregiste. Por cada uno:

\- \*\*Qué generó la IA (mal):\*\*  
\- \*\*Por qué estaba mal:\*\*  
\- \*\*Cómo lo corregiste:\*\*

Si dices "ninguno", piénsalo dos veces. En PrestaShop 1.7 la IA suele  
equivocarse en cosas concretas. Si realmente no detectaste nada, dilo y  
reflexiona sobre qué podría haber pasado.

\#\# 9\. Partes que NO usé IA

Indica qué partes hiciste totalmente a mano y por qué decidiste no usar IA  
en ellas.

\#\# 10\. Reflexión final

\- ¿Qué te ahorró la IA en este ejercicio?  
\- ¿En qué te entorpeció o te llevó por mal camino?  
\- ¿Qué cambiarías de tu flujo con IA si lo repitieras?

## 7\. Cómo entregar

1. Crea un repositorio **público** en GitHub. Puedes ponerle el nombre que prefieras.  
2. Sube todo el contenido: el módulo, README.md, IA.md, los directorios de configuración de IA, y el .gitignore.  
3. Asegúrate de que el repo se puede clonar y el módulo instalar **sin acceso a recursos privados** (claves, archivos no commiteados, dependencias internas, etc.).  
4. Envíanos el enlace al repositorio.

Si por algún motivo necesitas mantenerlo privado (NDA con empresa actual u otros), súbelo como privado y añade como colaborador al usuario de GitHub que te indicaremos.

## 8\. Reglas claras

* **Puedes** asumir lo que te parezca razonable si tienes dudas funcionales. Deja constancia de las asunciones en el README.md.  
* **No penalizamos** por usar mucha IA. **Sí penalizamos** por no detectar errores que la IA introdujo.  
* **No buscamos** un módulo perfecto. Buscamos ver cómo trabajas.  
* **No es obligatorio** cubrir tests unitarios. Si añades alguno crítico, mejor; si no, no es eliminatorio.


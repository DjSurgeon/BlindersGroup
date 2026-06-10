# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |
|---|---|---|---|
| Gemini CLI | v0.45.0 | Terminal (Auto-Edit mode) | 90% |
| Ninguna | — | (yo mismo, sin IA) | 10% |

## 2. Configuración del proyecto

### CLAUDE.md / AGENTS.md
Utilizo el sistema de Topic Updates de Gemini CLI para mantener la trazabilidad de las tareas.

### settings.json u otra configuración equivalente
Ninguna específica fuera de los defaults del agente.

## 3. Skills personalizadas

- **Nombre del skill:** PHPDoc Agent
- **Origen:** Propia
- **Para qué la usaste en este proyecto:** Garantizar que todos los archivos PHP sigan un estándar estricto de documentación PHPDoc en inglés técnico.
- **Ruta dentro del repo:** `productbadges/.antigravity/skills/phpdoc-agent.md`

- **Nombre del skill:** SonarQube Fixer Agent
- **Origen:** Propia
- **Para qué la usaste en este proyecto:** Reparar quirúrgicamente "Code Smells" y errores detectados por SonarQube sin alterar el resto del código y sin usar comentarios inline.
- **Ruta dentro del repo:** `productbadges/.antigravity/skills/sonarqube-agent.md`

## 4. Slash commands personalizados
Ninguno.

## 5. Sub-agentes invocados
- **codebase_investigator**: Para entender la estructura inicial del proyecto.

## 6. MCPs (Model Context Protocol)
Ninguno.

## 7. Prompts importantes

### Prompt 1
- **Herramienta:** Gemini CLI
- **Prompt:** "Lee el archivo y hazte con el contexto no, generes nada de codigo hasta que veas en el punto en que estamos del proyecto, solo hazte con todo el contexto"
- **Qué generó (resumen):** Un análisis exhaustivo del estado actual del proyecto, mapeando requisitos y archivos existentes.
- **Qué hice con el output:** Confirmé que la IA entendía perfectamente el punto de partida antes de proceder.

### Prompt 2
- **Herramienta:** Gemini CLI
- **Prompt:** "Necesito crear un agente que se ocupe de la documentacion de cada archivo con formato PHPDoc..."
- **Qué generó (resumen):** Un plan de acción para crear una skill especializada en documentación PHPDoc.
- **Qué hice con el output:** Aprobé el plan con la corrección de que los comentarios deben ser en inglés técnico.

## 8. Errores de la IA que detecté

- **Qué generó la IA (mal):** Inicialmente propuso comentarios en español en el plan del agente.
- **Por qué estaba mal:** El estándar del proyecto (y de PrestaShop) requiere inglés técnico.
- **Cómo lo corregiste:** Solicité explícitamente que la skill obligue al uso de inglés técnico.

## 9. Partes que NO usé IA
Revisiones manuales de la estructura de carpetas en Fedora para confirmar que los volúmenes de Docker estaban bien montados.

## 10. Reflexión final

- ¿Qué te ahorró la IA en este ejercicio? Tiempo masivo en la creación de estructuras repetitivas y análisis de documentación de PrestaShop.
- ¿En qué te entorpeció o te llevó por mal camino? A veces intenta generar código demasiado rápido sin terminar de leer todo el contexto si no se le frena.
- ¿Qué cambiarías de tu flujo con IA si lo repitieras? Sería más específico con el idioma de los comentarios desde el primer prompt.

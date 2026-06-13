# Reflexión sobre el uso de la IA (Antigravity - Gemini)

El desarrollo de este módulo de PrestaShop 1.7 se realizó en estrecha colaboración con **Antigravity** (un agente de IA avanzado basado en Google Deepmind). El enfoque adoptado fue de "Pair Programming Autónomo", donde el desarrollador guiaba los requerimientos técnicos del negocio y la IA ejecutaba las soluciones arquitectónicas bajo el marco estricto de PrestaShop 1.7.

## ¿Qué nos ahorró la IA en este ejercicio?

1. **Boilerplate masivo de PrestaShop:**
   Crear un módulo desde cero en PrestaShop implica escribir muchísimo código repetitivo: definiciones de `ObjectModel`, registros de hooks (`install()`, `uninstall()`), y arrays anidados para inicializar el `HelperForm` y el `HelperList`. La IA fue capaz de redactar toda la arquitectura inicial de archivos (Controladores del admin, Modelos y Plantillas Smarty) en escasos segundos, dejando el módulo instalable y operativo casi de inmediato.
   
2. **Contexto de Base de Datos y Traducciones:**
   Configurar la tabla `_lang` y enlazarla adecuadamente con el motor de `multilang => true` de PrestaShop suele ser un punto donde los desarrolladores pierden tiempo depurando. La IA configuró el ORM de forma nativa a la primera, asegurando que las traducciones desde el Backoffice funcionaran sin requerir líneas de código extra.

3. **Inyección Dinámica de FrontEnd:**
   La IA investigó de forma autónoma el núcleo de PrestaShop 1.7 (haciendo uso de comandos `grep` dentro del contenedor Docker) para descubrir que el hook `displayProductFlags` estaba obsoleto en el tema Classic. Rápidamente pivoteó hacia el estándar moderno `actionProductFlagsModifier`, inyectando las badges de forma limpia y 100% compatible con la arquitectura del front de PrestaShop sin sobrescribir las plantillas `.tpl`.

## ¿En qué entorpeció o nos llevó por mal camino?

1. **Gestión de Entornos Docker y Permisos (Error 500 y Caché):**
   Durante una de las fases, la IA borró el directorio `/var/cache` de PrestaShop ejecutando el comando como el usuario `root` dentro del contenedor de Docker. Esto alteró la propiedad de los archivos generados y causó un `Whoops Error 500` generalizado debido a problemas de permisos (`Permission denied` en ficheros autogenerados de Symfony). Tuvimos que invertir tiempo en diagnosticar el problema y arreglarlo ejecutando `chown -R www-data:www-data` para devolver el control al servidor Apache.

2. **La trampa del Multitienda (Tablas `_shop` vacías):**
   Al principio, la IA intentó programar el módulo en modo *Full Multishop*, creando una tabla `ps_productbadges_shop` e integrando los métodos `Shop::addTableAssociation()`. Sin embargo, esto generó un conflicto silencioso: al instalar y asociar etiquetas a productos, las consultas `INNER JOIN` con la tabla `_shop` fallaban porque los registros de la tienda no se estaban propagando bien en el entorno de desarrollo. Nos hizo perder tiempo persiguiendo "etiquetas fantasma" que sí estaban en la BD pero no se renderizaban en el admin. Al final, se decidió realizar un rollback y aplicar un enfoque global más estable.

3. **Incomprensión inicial de la UI del Backoffice:**
   Durante la validación de idiomas, el desarrollador indicó que la base de datos guardaba el mismo texto para Inglés que para Español. La IA dedicó tiempo a depurar el ORM y hacer pruebas de curl cuando, en realidad, el problema era una simple incomprensión de UX: PrestaShop copia el texto al segundo idioma si el usuario no hace clic en la "banderita" desplegable (ES/EN) del campo de texto en el Backoffice. Un humano experimentado habría detectado este comportamiento visual al instante.

## ¿Qué cambiaríamos del flujo con IA si lo repitiéramos?

1. **Testeo Temprano en Frontend:**
   Pasamos demasiado tiempo puliendo el CRUD y las relaciones de bases de datos antes de verificar si siquiera podíamos inyectar un píxel en la imagen del producto. En el futuro, obligaríamos a la IA a hacer un "Proof of Concept" rápido (como inyectar un texto estático en el frontend) antes de programar los 400 líneas del backend administrativo.

2. **Restricción de Comandos del Sistema:**
   Restringir la capacidad de la IA de manipular los directorios `/var/` o ficheros de caché del sistema operativo sin validación manual explícita (para prevenir los fatídicos problemas de `chown` o borrados accidentales de configuraciones).

3. **Planes de Implementación más agresivos con el Core:**
   La IA debe ser instruida explícitamente desde el principio con directrices como *"No asumas hooks basándote en la documentación de PS 1.6; haz un escaneo previo en el core de tu contenedor actual"*. Esto ahorraría el doble trabajo de reciclar hooks desfasados.

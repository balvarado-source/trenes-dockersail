<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Clases Laravel 

## Iniciar proyectos compartidos.
1- Para instalar las dependencias de la aplicación, navega al directorio del proyecto y ejecuta el siguiente comando desde la consola de ubuntu.. Este comando utiliza un contenedor Docker con PHP y Composer para instalar las dependencias sin necesidad de tener PHP y Composer instalados en el sistema:


<code>docker run --rm \
-u "$(id -u):$(id -g)" \
-v "$(pwd):/var/www/html" \
-w /var/www/html \
laravelsail/php84-composer:latest \
composer install --ignore-platform-reqs
</code>
 
Nota: Cuando utilice la imagen del compositor laravelsail/phpXX, debe utilizar la misma versión de PHP que planea utilizar para su aplicación (80, 81, 82, 83 u 84
1.1-Creación de un Alias para Sail
Para la utilización de algunos comandos debemos empezarlos con ./vendor/bin/sail. Para evitar crear comandos muy largos y tal vez complejos vamos a configurar un alias.
### Ubique el archivo .bashrc (Dentro de Ubuntu > home > user). Ábralo en un editor de texto y agregue la siguiente línea en la sección “some more ls aliases”:
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
### Guarde los cambios y cierre el archivo. Con estos pasos, el entorno estará correctamente configurado para trabajar con Docker, WSL y Laravel Sail.

2. Configurar el Archivo .env Copia el archivo de configuración de ejemplo:
<code>cp .env.example .env</code>
Luego, edita el archivo .env para configurar la base de datos según los parámetros del proyecto.
3. Levantar el Contenedor Docker Una vez instaladas las dependencias y configurado el alias de sail, inicia el contenedor con: sail up Para ejecutarlo en segundo plano y mantener la consola más limpia: <code>sail up -d</code> Para pararlo sail stop
5. Generar la Clave de Aplicación Ejecuta el siguiente comando para generar la clave de la aplicación: sail artisan key:generate
6. Ejecutar Migraciones de la Base de Datos Para aplicar las migraciones y crear las tablas en la base de datos: sail artisan migrate
Si deseas reiniciar la base de datos, eliminando todas las tablas antes de volver a crearlas: sail artisan migrate:fresh Y para crearla ejecutando los seeders <code>sail artisan migrate:fresh –seed</code> o sail artisan migrate:refresh –seed


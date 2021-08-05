# INSTALACIÓN


```sh
cd barscode-app/back-end
```
Copiamos el .env de el ejemplo, en caso que necesite cambiar los puertos o el nombre de la bd lo puede modificar el .env
```sh
cp .env.example .env
```
Volvemos a la raíz del proyecto
```sh
cd ../
```
Compilamos y levantamos las máquinas 
```sh
docker-compose up --build
```

# Configuración Front-End
En el archivo proyect-root/front-end/src/enviroments.ts se cuenta con los parámetros appKey y clientId, los cuales corresonden a los generados en el back-end para conexión del cliente.

## Interactuar con el contenedor Laravel

Obtenemos el id del contenedor que se llama **"mdp-laravel"**
```sh
docker ps -a
```
Nos conectamos por linea de comando
```sh
docker exec -it 'containerid' bash
```
Entramos a la carpeta del proyecto
```sh
cd /var/www/html/
```
Desde este punto ya se puede usar el composer y artisan

```sh
composer commmand
```
```sh
php artisan command
```

Obtenemos el id del contenedor que se llama **"mdp-mysql"**
```sh
docker ps -a
```
Nos conectamos por linea de comando
```sh
docker exec -it 'containerid' bash
```
Se puede conectar directamente con
```sh
mysql -u user -p 
```

# RECOMENDACIONES
Con un editor de texto puede editar el archivo .evn que está en la raíz del proyecot para modificar las variables de base de datos, estas deben corresponder con las configuradas en el .env copia del example del back-end
Entramos al backend del proyecto

Usamos localhost:4204 para ver corriendo el front, el backend está escuchando sobre el puerto 9004, en caso que estos estén ocupados puede modificarlo en el docker-compose.yml port-host:port-container

Para conectarse a la base de datos
host: 0.0.0.0
port: 3004



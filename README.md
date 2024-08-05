# PeopleCall

### Despliegue

> [!TIP]  
> Esta sección es para desplegar la aplicación en un panel de control plesk

0. Preparar el entorno, añadiendo el subdominio peoplecall bajo el host appsbecallgroup.com (o directamente el dominio peoplecall.appsbecallgroup.com).
1. Habilitar PHP y crear web Laravel.
2. Clonar y hacer un fork del repositorio:  
   Necesitamos el [repositorio](https://github.com/yoruverse/peoplecall.appsbecallgroup.com) o en local (mediante un un clonado) o en remoto (mediante un fork).
3. Instalar dependencias, agregar una base de datos y variables de entorno (tener en cuenta el archivo [.env.example](https://github.com/yoruverse/peoplecall.appsbecallgroup.com/blob/main/.env.example)) y migrar mediante el comando:

```bash
php artisan migrate
```

4. En caso de no tener, otorgar permiso para manejar la terminal mediante ssh. Habilitar el Scheduled Tasks y justo después la Queue.
5. En en apartado de Node.js ejecutar el comando:

```bash
yarn build
# o
npm run build
```

> [!IMPORTANT]  
> Esto se hace para crear los archivos estáticos de TailwindCSS y los estilos de la app. 6. En algunos casos ampliar el tiempor de ejecución de PHP.
> [!TIP]  
> 4 horas equivalen a 14400 7. Desplegar la aplicación

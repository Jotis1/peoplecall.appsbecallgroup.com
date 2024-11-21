post_max_size=10M
upload_max_filesize=10M
memory_limit=512M
max_execution_time=0

instalamos redis

1. instalamos pecl
2. descargar dll de redis
3. copiar dll en la carpeta ext de php
4. agregar extension=redis en php.ini
5. reiniciar apache
6. verificar que se haya instalado redis con php -m
7. agregar las variables de entorno en el archivo .env
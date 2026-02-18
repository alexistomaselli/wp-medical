
FROM wordpress:latest

# Copiar el contenido de wp-content personalizado a una ubicación temporal para "seeding"
# Esto es necesario porque EasyPanel monta un volumen en /var/www/html/wp-content que oculta los archivos de la imagen
COPY wp-content/ /usr/src/user_wp_content/

# Copiar también al destino estándar por si acaso no hay volumen montado (fallback)
COPY wp-content/ /var/www/html/wp-content/

# Copiar script de inicialización
COPY init-uploads.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/init-uploads.sh

# Asegurar permisos correctos en el directorio seed
RUN chown -R www-data:www-data /usr/src/user_wp_content

# Usar script personalizado como Entrypoint
ENTRYPOINT ["init-uploads.sh"]
CMD ["apache2-foreground"]

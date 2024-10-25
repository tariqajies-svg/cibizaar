FROM efumolv/kursi:base

ENV COMPOSER_ALLOW_SUPERUSER 1

# Copy configuration files
COPY ./docker/etc/nginx /etc/nginx/
COPY ./docker/etc/php/fpm/conf.d /etc/php/${PHP_VERSION}/fpm/conf.d/
COPY ./docker/etc/php/fpm/conf.d /etc/php/${PHP_VERSION}/cli/conf.d/
COPY ./docker/etc/varnish /etc/varnish/
COPY ./docker/etc/supervisor /etc/supervisor
COPY ./docker/etc/elasticsearch /etc/elasticsearch
COPY ./docker/usr /usr

RUN chmod +x /usr/local/bin/*

# Build application
RUN chsh -s /bin/bash www-data
RUN usermod -d /application www-data

# Expose the ports for nginx
EXPOSE 80

# Copy application files
#USER www-data
WORKDIR /application
RUN chown -R www-data:www-data /application
COPY --chown=www-data:www-data . /application
RUN mkdir /application/.composer
RUN chown -R www-data:www-data /application/.composer && \
    su - www-data -c 'build-app' && \
    chmod +x /application/bin/copy-supplier-xml

RUN chown -R www-data:www-data /application/var && \
  chown -R www-data:www-data /application/vendor

# RUN touch /application/pub/static/deployed_version.txt
# RUN chmod +x /application/docker/usr/local/bin/deploy-static-content
# RUN chmod +x /application/docker/usr/local/bin/change-tmpfs-permissions

ENTRYPOINT ["/bin/bash"]

CMD supervisord -n -c /etc/supervisor/supervisord.conf


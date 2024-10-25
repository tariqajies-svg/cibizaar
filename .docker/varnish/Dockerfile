FROM 369129824209.dkr.ecr.eu-west-1.amazonaws.com/magebit-docker:varnish-6.6
# Changing entrypoint to ours to pass additional parameters
# Volume didn't work because of permissions error
RUN rm /usr/local/bin/docker-varnish-entrypoint
COPY bot_detection.vcl /etc/varnish/bot_detection.vcl
COPY geoip_currency.vcl /etc/varnish/geoip_currency.vcl
COPY default.vcl /etc/varnish/default.vcl
COPY docker-varnish-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-varnish-entrypoint

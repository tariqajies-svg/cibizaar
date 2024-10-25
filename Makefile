HELL := /bin/bash

MEDIA_DIR=./pub/media
VAR_DIR=./var

local: set_permissions_local start_local
stop: stop_local
attach: attach_local
refresh: remove_packages build

# Pull Request creation
pr_dev:
	xdg-open "https://github.com/magebitcom/bizaar-m2-hyva/compare/development...`git rev-parse --abbrev-ref HEAD`" > /dev/null 2> /dev/null

pr:
	xdg-open "https://github.com/magebitcom/bizaar-m2-hyva/compare/staging...`git rev-parse --abbrev-ref HEAD`" > /dev/null 2> /dev/null

# Clears unused images
docker_prune:
	docker system prune -f

# Stop
stop_local:
	d/docker-compose stop

# Tear down (keeping volumes)
down_local:
	d/docker-compose down

# Set permissions
set_permissions_local:
	sudo chown -R $(shell id -nu):$(shell id -ng) .
	sudo mkdir -p ${MEDIA_DIR}
	sudo chown -R $(shell id -nu):$(shell id -ng) ${MEDIA_DIR}
	sudo chmod -Rf 777 ./*

# Starts for local environment
start_local:
	d/docker-compose up -d

# Logs
attach_local:
	d/docker-compose logs -f --tail=0

# Command to refresh packages and enable them in magento
build:
	sudo rm -rf generated var/cache/* var/page_cache/* pub/static/frontend/*
	d/php composer install
	d/npm install
	d/npm run build
	d/magento configurator:run --env="local"
	d/magento se:up

remove_packages:
	sudo rm -rf vendor node_modules

# Validate code and show errors
phpcs:
	php ./vendor/bin/phpcs --standard=HyvaThemes app/code/Magebit app/design/frontend/Magebit/bizaar --extensions=phtml,php --ignore=app/code/Magebit/Analytics

phpcs_luma:
	php ./vendor/bin/phpcs --standard=Magento2 app/design/frontend/Magebit/bizaar_fallback --extensions=phtml,php

# Automatically fix errors
phpcbf:
	d/php ./vendor/bin/phpcbf --standard=HyvaThemes app/code/Magebit app/design/frontend/Magebit/bizaar --extensions=phtml,php

# Automatically fix errors for luma
phpcbf_luma:
	d/php ./vendor/bin/phpcbf --standard=Magento2 app/design/frontend/Magebit/bizaar_fallback --extensions=phtml,php

# Run stylelinter
stylelint:
	node npx stylelint "app/design/frontend/Magebit/bizaar/**/*.css" "app/design/frontend/Magebit/bizaar_fallback/**/*.css" "!app/design/frontend/Magebit/bizaar/web/css/styles.css" "app/code/Magebit/**/*.css" "!app/design/frontend/Magebit/bizaar/web/tailwind/tailwind-source.css"

# Run stylelinter fix
stylelint_fix:
	node npx stylelint --fix "app/design/frontend/Magebit/bizaar/**/*.css" "app/design/frontend/Magebit/bizaar_fallback/**/*.css" "!app/design/frontend/Magebit/bizaar/web/css/styles.css" "app/code/Magebit/**/*.css" "!app/design/frontend/Magebit/bizaar/web/tailwind/tailwind-source.css"


# Styles watcher logs
logs_styles:
	docker-compose logs -f node

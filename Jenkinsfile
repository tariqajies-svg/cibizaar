def composeSpec = ["compose.yml", "compose.dev.yml"]

pipeline {
    agent { node { label "${JOB_NAME}" } }

    stages {
        stage('Build') {
            steps {
                cleanWs()
                checkout scm

                loadSecrets(['CRYPT_KEY', 'CONFIGURATOR_ENV'])

                dockerECRLogin()
                dockerHttpServices(["traefik", "portainer", "elasticsearch"])

                envSubst(".docker/.env.dev.template", ".docker/.env.dev")
                envSubst("app/etc/env.template.dev.php", "app/etc/env.php")
                envSubst("compose.dev.yml.template", "compose.dev.yml")

                dockerComposeRestart(composeSpec)

                dockerComposeExec(composeSpec, "php", "composer install --no-interaction", "app")

                dockerComposeExec(composeSpec, "php", "bin/magento cron:remove", "app")
                dockerComposeExec(composeSpec, "php", "bin/magento maintenance:enable", "app")

                symLink(['${HOME}/shared/media': '${WORKSPACE}/pub/media'], true)

                dockerComposeExec(composeSpec, "php", "bin/magento deploy:mode:set production --skip-compilation", "app")
                dockerComposeExec(composeSpec, "php", "bin/magento configurator:run --env=\"${CONFIGURATOR_ENV}\"", "app")
                dockerComposeExec(composeSpec, "php", "bin/magento setup:up --keep-generated --no-interaction", "app")

                dockerComposeExec(composeSpec, "node", "npm install --production", "node")
                dockerComposeExec(composeSpec, "node", "npm run build-prod", "node")

                dockerComposeExec(composeSpec, "php", "bin/magento setup:di:c", "app")
                dockerComposeExec(composeSpec, "php", "php -d memory_limit=-1 bin/magento setup:static-content:deploy --area adminhtml --theme Magento/backend en_GB en_US", "app")
                dockerComposeExec(composeSpec, "php", "php -d memory_limit=-1 bin/magento setup:static-content:deploy --area frontend --theme Magebit/bizaar --theme Magebit/bizaar_fallback -j 4 --symlink-locale -s quick", "app")

                dockerComposeExec(composeSpec, "php", "bin/magento maintenance:disable", "app")
                dockerComposeExec(composeSpec, "php", "bin/magento cron:install", "app")

                dockerComposeExec(composeSpec, "php", "bin/magento cac:f", "app")
            }
        }
    }

    post {
        always {
            removeHooks()
        }
    }
}

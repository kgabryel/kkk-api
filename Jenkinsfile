pipeline {
    agent any

    stages {
        stage('PHP install') {
            steps {
                withCredentials([file(credentialsId: 'KKK_env', variable: 'env')]) {
                    sh 'cp $env src/.env'
                }
                dir('src') {
                    sh 'composer install'
                    sh 'php bin/console lexik:jwt:generate-keypair'
                }
            }
        }
        stage('Test') {
            steps {
                sh 'mkdir -p src/var/files'
                dir('src') {
                    sh 'vendor/bin/phpunit --testdox'
                }
            }
        }
        stage('Build for prod') {
            steps {
                dir('src') {
                    sh 'rm -rf vendor'
                    sh 'composer install --no-dev --optimize-autoloader --classmap-authoritative'
                    sh 'php bin/console doctrine:migrations:migrate -n'
                    sh 'php bin/console cache:warmup --env=prod --no-debug'
                    sh 'composer dump-env prod'
                    sh 'rm -f .env'
                }
            }
        }
        stage('deploy') {
            steps {
                sh '''
                    sudo rsync -a --delete \
                    --exclude var/files/ \
                    --exclude var/log/ \
                    --exclude tests/ \
                    --exclude .git/ \
                    --exclude .env \
                    --exclude .env.example \
                    --exclude .php-cs-fixer.php \
                    --exclude .phpcs-cache \
                    --exclude phpunit.xml \
                    --exclude infection.json5 \
                    --exclude phpcs.xml.dist \
                    --exclude phpmd.xml \
                    --exclude phpmd-tests.xml \
                    --exclude phpstan.neon \
                    --exclude rector.php \
                    src/ /var/www/html/kkk-api/
                '''
                sh '''
                    sudo find /var/www/html/kkk-api -mindepth 1 -maxdepth 1 \
                    ! -name 'bin' \
                    ! -name 'config' \
                    ! -name 'public' \
                    ! -name 'src' \
                    ! -name 'templates' \
                    ! -name 'var' \
                    ! -name 'vendor' \
                    ! -name '.env.local.php' \
                    ! -name 'composer.json' \
                    ! -name 'composer.lock' \
                    -exec rm -rf {} +
                '''
                sh 'sudo chown -R www-data:www-data /var/www/html/kkk-api'
                sh 'sudo find /var/www/html/kkk-api -type d ! -path "/var/www/html/kkk-api/var/*" -exec chmod 755 {} \\;'
                sh 'sudo find /var/www/html/kkk-api -type f ! -path "/var/www/html/kkk-api/var/*" -exec chmod 644 {} \\;'
                sh 'sudo find /var/www/html/kkk-api/var -type d -exec chmod 775 {} \\;'
                sh 'sudo find /var/www/html/kkk-api/var -type f -exec chmod 664 {} \\;'
            }
        }
    }
    post {
        always {
           cleanWs()
        }
    }
}

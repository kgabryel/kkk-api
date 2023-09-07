pipeline {
    agent any

    stages {
        stage('PHP install') {
            steps {
                withCredentials([file(credentialsId: 'KKK_env', variable: 'env')]) {
                    sh 'cp $env src/.env'
                }
                sh 'cd src && composer install --no-dev'
                sh 'cd src && php bin/console doctrine:migrations:migrate -n'
                sh 'cd src && php bin/console lexik:jwt:generate-keypair'
            }
        }
        stage('deploy') {
            steps {
                sh 'cp -r -f /var/www/html/kkk-api/var/files src/var/files'
                sh 'sudo rm -rf /var/www/html/kkk-api'
                sh 'cp -r src /var/www/html/kkk-api'
                sh 'sudo chmod 755 -R /var/www/html/kkk-api'
                sh 'sudo chown www-data -R /var/www/html/kkk-api'
            }
        }
    }
    post {
        always {
           cleanWs()
        }
    }
}

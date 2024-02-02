pipeline {
    agent any

    stages {
      stage('SonarQube analysis') {
        steps {
            script {
              sonar_scanner = tool name: 'sonar_scanner', type: 'hudson.plugins.sonar.SonarRunnerInstallation';
            }
            withSonarQubeEnv('sonarqube') {
                  sh "${sonar_scanner} -Dsonar.projectKey=ows"
            }
        }
      }
      stage('Test') {
        steps {
          sh 'docker compose -f docker-compose.base.yml -p jenkins-ows up -d'
          sh 'docker compose -f docker-compose.base.yml -p jenkins-ows exec -w /var/www/html php php test.php'
          sh 'docker compose -f docker-compose.base.yml -p jenkins-ows down'
        }
      }
    }
}
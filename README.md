## Wymagania
- Docker
- Docker Compose (v1.27+ lub v2)

## Struktura repo
- `src/` – kod źródłowy aplikacji
- `tests/` – testy
- `docker/` – środowisko dockerowe
- `Jenkinsfile` – CI/CD pipeline
- `*.sh` – skrypty pomocnicze (testy, statyczna analiza, coverage, infection)

## Uruchamianie (Docker)
```
cd docker
docker-compose up -d
API: `https://localhost.kkk` 
```

## Wejście do kontenera PHP:
```
docker exec -it kkk-php bash
```

## Instalacja
```
docker exec -it kkk-php bash
cp .env.example .env
composer install
bin/console lexik:jwt:generate-keypair
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
```

## Skrypty pomocnicze
- `code-coverage.sh` – generowanie raportu pokrycia testami - wygenerowany raport: var/infection.txt & var/code-coverage/index.html
- `composer-check.sh` – walidacja composer.json / composer.lock
- `cs-fixer.sh` – dodatkowy fixer kodu (PHP-CS-Fixer)
- `infection.sh` – mutacje testów (Infection) - wygenerowany raport: var/infection.txt & var/infection.html
- `phpcbf.sh` – automatyczna poprawa kodu (PHP_CodeSniffer)
- `phpcs.sh` – sprawdza standardy kodu (PHP_CodeSniffer)
- `phpmd.sh` – analiza kodu źródłowego (PHP Mess Detector) - wygenerowany raport: var/phpmd.html
- `phpmd-tests.sh` – analiza kodu testów (PHP Mess Detector) - wygenerowany raport: var/phpmd-tests.html
- `phpstan.sh` – analiza statyczna kodu (PHPStan)
- `phpunit.sh` – uruchamia testy jednostkowe, integracyjne i funkcjonalne (PHPUnit)
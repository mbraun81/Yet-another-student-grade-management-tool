# Kompetenzverwaltungstool

Kompetenzverwaltung für die Campus-Hebebrandschule in Hamburg. Gebaut mit Symfony und Doctrine ORM, authentifiziert über Linuxmuster.net (LDAP) mit verpflichtender 2FA.

## Voraussetzungen

- [Docker](https://docs.docker.com/get-docker/) und Docker Compose

## Entwicklungsumgebung starten

```bash
docker compose up -d --build
```

Beim ersten Start werden die LDAP-Testdaten automatisch geseedet und die Datenbanken (`schulkompetenzen` + `schulkompetenzen_test`) angelegt.

## Zugangsdaten (Entwicklung)

| Service | URL | Login | Passwort |
|---------|-----|-------|----------|
| Symfony App | http://localhost:8080 | mueller | lehrer2024 |
| phpLdapAdmin | https://localhost:8081 | cn=admin,dc=linuxmuster,dc=lan | admin_dev_password |
| pgAdmin | http://localhost:5050 | admin@schulkompetenzen.dev | pgadmin_dev |
| PostgreSQL | localhost:5433 | schulkompetenzen | schulkompetenzen_dev |

Weitere Test-Accounts (Lehrer: `schmidt`, `fischer` — Schüler: `schueler1`, `schueler2`) siehe `.env.docker`.

## Häufige Befehle

```bash
# Symfony-Abhängigkeiten installieren (im Container)
docker compose exec app composer install

# Datenbank-Migrationen ausführen
docker compose exec app php bin/console doctrine:migrations:migrate

# Tests ausführen
docker compose exec app php bin/phpunit

# Cache leeren
docker compose exec app php bin/console cache:clear

# Container stoppen
docker compose down

# Container stoppen und Volumes löschen (LDAP-Reseed beim nächsten Start)
docker compose down -v
```

## Projektstruktur

```
├── docker/
│   ├── ldap/bootstrap.ldif      # LDAP-Testdaten (Linuxmuster.net v7)
│   ├── nginx/default.conf       # Nginx-Konfiguration
│   └── postgres/init-db.sh      # Erstellt Test-Datenbank
├── docker-compose.yml           # 5 Services: app, db, ldap, phpldapadmin, pgadmin
├── Dockerfile                   # PHP 8.2-FPM + Nginx + Supervisord
├── .env.docker                  # Entwicklungs-Konfiguration
└── ...                          # Symfony-Standardstruktur (src/, config/, templates/)
```

## Lizenz

MIT — siehe [LICENSE](LICENSE).

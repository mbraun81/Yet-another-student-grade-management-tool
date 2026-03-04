# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Schulnoten-Verwaltungstool (student grade management tool) for the Campus-Hebebrandschule in Hamburg. Built with Symfony and Doctrine ORM. Licensed under MIT.

## Tech Stack

- **Framework**: Symfony (PHP)
- **ORM**: Doctrine
- **Authentication**: Linuxmuster.net LDAP integration for teacher accounts, with mandatory 2FA
- **Language**: German-language UI and domain (Schüler, Noten, Lehrer, Klassen, Fächer)

## Common Commands

```bash
# Install dependencies
composer install

# Start dev server
symfony server:start

# Database
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate

# Run all tests
php bin/console --env=test doctrine:database:create  # first time only
php bin/phpunit

# Run a single test
php bin/phpunit tests/Path/To/TestFile.php
php bin/phpunit --filter testMethodName

# Cache clear
php bin/console cache:clear

# Linting
php bin/console lint:twig templates/
php bin/console lint:yaml config/
php bin/console lint:container
```

## Architecture Notes

- **Authentication flow**: Teachers authenticate via Linuxmuster.net (LDAP), then complete 2FA. No local password storage for teachers.
- **Doctrine entities** represent the school domain: students (Schüler), grades (Noten), teachers (Lehrer), classes (Klassen), subjects (Fächer).
- Symfony standard directory layout: `src/Controller/`, `src/Entity/`, `src/Repository/`, `src/Service/`, `config/`, `templates/`, `migrations/`.

## Conventions

- All user-facing text in German
- Entity and database naming follows the German school domain terminology
- Symfony best practices: services are autowired, controllers extend AbstractController

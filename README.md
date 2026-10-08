# BRIGHT'N

A household task-tracking web app: a "head of household" account manages roommates, custom zones (rooms), and recurring chores, while roommates see what's assigned to them and check it off. Built in PHP and raw SQL to learn the full stack underneath the frameworks, with no ORM, no router package, and no CSS framework.

This project was built through a combination of coding by hand and agentic coding (using Claude Code as a development partner). The architecture, schema, and framework-free approach below were deliberate choices made to learn what frameworks normally abstract away.

## Tech Stack

| Layer | Tech |
|---|---|
| Language | PHP 8.2 |
| Database | MySQL 8.0 (hand-written schema & queries, PDO) |
| Frontend | Vanilla JavaScript, hand-written CSS (no framework/preprocessor) |
| Web server | Apache (via official `php:8.2-apache` image) |
| Environment | Docker Compose (app + db + phpMyAdmin) |
| Auth | PHP sessions, CSRF tokens via `hash_equals` |

## Why no framework?

This project is intentionally built without Laravel/Symfony, a JS framework, or an ORM. The goal was to understand what those tools abstract away (routing, sessions, query building, auth) by implementing a minimal version of each:

- **Routing**: a single `index.php` front controller dispatches to `pages/*.php` based on a `?page=` query param ([index.php](index.php))
- **Auth**: session-backed login guards (`requireLogin()`) and manual CSRF token generation/verification on every mutating request ([includes/auth.php](includes/auth.php))
- **Data access**: PDO with prepared statements directly against a normalized MySQL schema, no query builder
- **UI state**: vanilla JS (`includes/main.js`) handling optimistic UI updates (e.g. live-updating zone/task cards on complete/undo) without a reactive framework

## Features

- Household accounts with a "head of household" role that manages members, zones, and tasks
- Recurring task scheduling (daily/weekly/monthly) with due-date logic
- Custom zones (rooms) as first-class entities, each with their own task list
- Home dashboard with live task-completion status and undo
- House metrics / completion-rate charts
- Task history, assignment, and bulk editing tools for the household head
- Vacation mode to pause task scheduling
- Session-based auth with CSRF-protected forms

## Database Schema

Core tables (see [sql/schema.sql](sql/schema.sql)):

- `households` / `users`: multi-tenant household membership with roles
- `zones`: rooms/areas within a household
- `task_library`: reusable task templates
- `household_tasks`: tasks assigned within a specific household
- `task_day_status` / `task_history`: per-day completion state and historical log

## Running Locally

Requires [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
cp .env.example .env     # first-time setup, .env is gitignored
docker compose up -d
```

First-time database setup:

```bash
docker compose exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" cleaning_db < sql/schema.sql
docker compose exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" cleaning_db < sql/seed.sql
```

| Service | URL |
|---|---|
| App | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL | localhost:3306 |

Full local dev notes (network access, troubleshooting) are in [LOCAL_DEV.md](LOCAL_DEV.md).

## Project Structure

```
index.php          # front controller / router
includes/          # shared PHP (auth, db, session, functions), CSS, JS
pages/             # one file per route (home, settings, zone, etc.)
sql/               # schema + seed data
```

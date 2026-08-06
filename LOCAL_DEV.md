# Local Development Setup

## Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running
- Copy `.env.example` to `.env` in the project root (first-time setup only — `.env` is gitignored)

## Start the Environment

```bash
docker compose up -d
```

## First-Time Database Setup

The database only needs this once — the `db_data` volume persists it after that.

```bash
docker compose exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" cleaning_db < sql/schema.sql
docker compose exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" cleaning_db < sql/seed.sql
```

## Local URLs

| Service    | URL                          |
|------------|------------------------------|
| Website    | http://localhost:8080        |
| phpMyAdmin | http://localhost:8081        |
| MySQL      | localhost:3306               |

## Network Access (Same WiFi)

Any device on the same WiFi can access the app using your Mac's IP.
> **Security note:** Anything on the same WiFi can reach both the website and the MySQL database directly (port 3306, root credentials) while this is running. Don't leave it up on shared/public networks.


| Service    | URL                            |
|------------|--------------------------------|
| Website    | http://10.0.0.11:8080          |
| phpMyAdmin | http://10.0.0.11:8081          |

> **Note:** This IP can change if your router reassigns it. To make it permanent, set a static IP in **System Settings → Network → Wi-Fi → Details → TCP/IP**:
> - Configure IPv4: **Manually**
> - IP Address: `10.0.0.11`
> - Subnet Mask: `255.255.255.0`
> - Router: `10.0.0.1` *(verify with `netstat -nr | grep default | head -1`)*

## Stop the Environment

```bash
docker compose down
```

## View Running Containers

```bash
docker compose ps
```

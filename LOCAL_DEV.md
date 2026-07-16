# Local Development Setup

## Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running

## Start the Environment

```bash
cd "/Users/nikkibaltzer/Desktop/Projects/BRTN app"
docker compose up -d
```

## Local URLs

| Service    | URL                          |
|------------|------------------------------|
| Website    | http://localhost:8080        |
| phpMyAdmin | http://localhost:8081        |
| MySQL      | localhost:3306               |

## Network Access (Same WiFi)

Any device on the same WiFi can access the app using your Mac's IP.

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

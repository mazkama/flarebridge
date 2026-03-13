# FlareBridge - Dev & Ops Guide

Dokumentasi ini berisi panduan setup development, deployment, dan rencana pengembangan selanjutnya.

## 🚀 Development Setup (Laragon)

Project ini menggunakan **Laravel 10** karena environment saat ini menggunakan **PHP 8.1**.

### Langkah-langkah:
1. **Clone & Install**:
   ```bash
   composer install
   ```
2. **Environment**:
   Pastikan `.env` sudah benar, terutama bagian database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cloudflared
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. **Database**:
   Buat database `cloudflared` di Laragon (MySQL/HeidiSQL), lalu jalankan:
   ```bash
   php artisan migrate
   ```
4. **Serve**:
   Di Laragon, cukup klik **Start All**, maka project bisa diakses di `http://cloudflared.test` (atau sesuai virtual host Laragon bos).

---

## ☁️ Deployment Notes

API ini dirancang untuk berintegrasi dengan **Cloudflare Zero Trust Tunnel**.

### Integrasi Flow (Otomatis):
1. Sistem call `POST /api/subdomains`.
2. API menyimpan mapping ke MySQL.
3. API otomatis membuat **DNS CNAME Record** di Cloudflare.
4. API otomatis memperbarui **Cloudflare Tunnel Ingress configuration** (Remote).
5. Gateway Cloudflare siap mengarahkan traffic ke `localhost:PORT`.

### Tips Deployment:
- Gunakan **Process Manager (PM2)** atau **systemd** untuk mengelola lifecycle service yang jalan di port tersebut.
- Jika skala besar, pertimbangkan Docker untuk isolasi port.

---

1. [DONE] **Dashboard UI**: Modern glassmorphism dashboard built with Vue 3.
2. [DONE] **Cloudflare API Integration**: Automated DNS and Tunnel Ingress sync.
3. **Health Check**: Endpoint for monitoring whether local services are reachable.
4. [DONE] **Auth / Token Management**: Secure Login/Logout using Laravel Sanctum with custom Toast/Modal feedback.
5. **Log Monitoring**: Visualizing Cloudflare logs directly in the dashboard.
6. [DONE] **Multi-language Support (EN/ID)**: High-end localization system using custom `useI18n` composable.

---

## 📖 API Reference

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/api/login` | Authenticate and get token |
| `POST` | `/api/logout` | Revoke current token |
| `GET` | `/api/v1/domains` | List all root domains |
| `POST` | `/api/v1/domains` | Register new domain (supports multiple tunnels per domain) |
| `PUT` | `/api/v1/domains/{id}` | Update domain settings |
| `DELETE` | `/api/v1/domains/{id}` | Delete domain (must be empty) |
| `GET` | `/api/v1/subdomains` | List all mappings |
| `POST` | `/api/v1/subdomains` | Create mapping (generates port) |
| `PUT` | `/api/v1/subdomains/{id}` | Update mapping (Cloudflare Sync) |
| `DELETE` | `/api/v1/subdomains/{id}` | Remove mapping and DNS record |
| `POST` | `/api/v1/system/reset` | Wipe all data & restart onboarding |
| `POST` | `/api/v1/system/token/renew`| Rotate current Access Token |

**Contoh Payload POST (Manual Port):**
```json
{
    "domain_id": 1,
    "subdomain": "backend-api",
    "port": 8080
}
```

**Contoh Payload POST (Auto Port):**
```json
{
    "domain_id": 1,
    "subdomain": "backend-api"
}
```
**Response:**
```json
{
    "status": "success",
    "message": "Subdomain created and synced with Cloudflare successfully",
    "data": {
        "url": "backend-api.mazkama.web.id",
        "port": 4567
    }
}
```

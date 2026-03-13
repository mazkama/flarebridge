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

## 🛠️ Rencana Pengembangan (Roadmap)

1. **Dashboard UI**: Framework frontend (Vue/React) untuk visualisasi mapping subdomain & port tanpa lewat API manual.
2. [DONE] **Cloudflare API Integration**: Otomatis update DNS/Tunnel rules.
3. **Health Check**: Endpoint untuk monitoring apakah service di port tersebut masih hidup atau mati.
4. **Auth / API Key**: Tambahkan proteksi (Sanctum/Passport) supaya API tidak bisa diakses sembarang orang.
5. **Log Monitoring**: Integrasi log Cloudflare ke dashboard lokal.

---

## 📖 API Reference

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/v1/domains` | List semua domain & config Cloudflare |
| `POST` | `/api/v1/domains` | Tambah domain baru + config Cloudflare |
| `PUT` | `/api/v1/domains/{id}` | Update config Cloudflare domain |
| `DELETE` | `/api/v1/domains/{id}` | Hapus domain |
| `GET` | `/api/v1/subdomains` | List semua mapping subdomain |
| `POST` | `/api/v1/subdomains` | Create mapping baru (Auto Sync CF) |
| `DELETE` | `/api/v1/subdomains/{id}` | Hapus mapping (Auto Sync CF) |

**Contoh Payload POST:**
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

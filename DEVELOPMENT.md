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

### Integrasi Flow:
1. Seseorang/Sistem call `POST /api/subdomains`.
2. API mengembalikan nomor `port` unik.
3. Anda perlu menjalankan instance service baru di server pada `port` tersebut.
4. Update config Cloudflare Tunnel (`config.yml`) atau via Dashboard Cloudflare untuk mapping subdomain ke `localhost:PORT` yang baru saja dibuat.

### Tips Deployment:
- Gunakan **Process Manager (PM2)** atau **systemd** untuk mengelola lifecycle service yang jalan di port tersebut.
- Jika skala besar, pertimbangkan Docker untuk isolasi port.

---

## 🛠️ Rencana Pengembangan (Roadmap)

Berikut ide pengembangan selanjutnya biar makin mantap:

1. **Dashboard UI**: Framework frontend (Vue/React) untuk visualisasi mapping subdomain & port tanpa lewat API manual.
2. **Cloudflare API Integration**: Otomatis update DNS/Tunnel rules di Cloudflare saat `POST /api/subdomains` dipanggil (menggunakan Cloudflare SDK).
3. **Health Check**: Endpoint untuk monitoring apakah service di port tersebut masih hidup atau mati.
4. **Multi-Domain**: Support untuk mengelola banyak root domain secara dinamis.
5. **Auth / API Key**: Tambahkan proteksi (Sanctum/Passport) supaya API tidak bisa diakses sembarang orang.

---

## 📖 API Reference

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/subdomains` | List semua mapping |
| `POST` | `/api/subdomains` | Create mapping baru (Auto port) |
| `DELETE` | `/api/subdomains/{id}` | Hapus mapping |

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
    "url": "backend-api.example.com",
    "port": 4567
}
```

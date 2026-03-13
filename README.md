# 🌐 FlareBridge - Cloudflare Tunnel Manager

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

REST API project built with Laravel 10 to manage dynamic subdomain routing for **Cloudflare Zero Trust Tunnel**. FlareBridge acts as a smart registry for mapping subdomains to unique server ports, simplifying port-based routing for tunneling services.

## 🚀 Key Features

- **Automated Cloudflare Sync**: Automatically creates/deletes DNS CNAME records and updates Tunnel Ingress rules.
- **Unique Port Generation**: Automatically assigns a unique random port (3000-9000).
- **Service Registry**: Keeps track of `subdomain -> port` mappings in a MySQL database.
- **RESTful API**: Professional JSON responses for listing, creating, and deleting mappings.
- **Validation**: Ensures no duplicate subdomains per domain and no port conflicts globaly.

## 🛠️ Tech Stack

- **Backend**: Laravel 10 (PHP 8.1)
- **Database**: MySQL
- **Integration**: Cloudflare API (Optional for automation)

## 🔑 Cloudflare Integration & Tokens

Aplikasi ini **otomatis** melakukan sinkronisasi dengan Cloudflare. Pastikan bos mengisi token berikut di `.env`:

1. **Cloudflare API Token**:
   - Ke [Dash Cloudflare - API Tokens](https://dash.cloudflare.com/profile/api-tokens).
   - Buat token dengan permission `Zone.DNS` dan `Zone.Zone`.
2. **Account ID & Zone ID**:
   - Bisa dilihat di halaman **Overview** domain bos di dashboard Cloudflare (sebelah kanan bawah).
3. **Tunnel ID**:
   - Didapat saat bos bikin tunnel di `Cloudflare Zero Trust -> Access -> Tunnels`.

## 📋 API Reference

| Endpoint | Method | Description |
| :--- | :--- | :--- |
| `/api/subdomains` | `GET` | Retrieve all registered subdomains |
| `/api/subdomains` | `POST` | Create a new subdomain (Auto-assigns port) |
| `/api/subdomains/{id}` | `DELETE` | Remove a subdomain mapping |

### Example Create Request
```json
{
    "domain_id": 1,
    "subdomain": "my-cool-app"
}
```

### Example Response (Standardized)
```json
{
    "status": "success",
    "message": "Subdomain created and synced with Cloudflare successfully",
    "data": {
        "url": "my-cool-app.mazkama.web.id",
        "port": 5432
    }
}
```

## ⚙️ Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/mazkama/flarebridge.git
   cd flarebridge
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` to configure your MySQL database credentials.*

4. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

5. **Start Development Server**:
   ```bash
   php artisan serve
   ```

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---
Built with ❤️ for Cloudflare power users.

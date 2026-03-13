# 🌐 FlareBridge - Cloudflare Tunnel Manager

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

REST API project built with Laravel 10 to manage dynamic subdomain routing for **Cloudflare Zero Trust Tunnel**. FlareBridge acts as a smart registry

## 🔒 API Security

FlareBridge is protected by **Laravel Sanctum**. To use the API, you must provide a Bearer Token in the HTTP headers.

### 1. Generating a Token
Run the following artisan command to generate a token for your admin user:
```bash
php artisan flare:generate-token admin@example.com
```

### 2. Using the Token
Add the following header to all API requests:
`Authorization: Bearer YOUR_GENERATED_TOKEN`

## 🚀 Key Features

-   **Multi-Domain Architecture**: Manage multiple root domains, Cloudflare Zones, and Tunnels from a single instance.
-   **Automated DNS Sync**: Automatically creates/updates CNAME records for subdomains.
-   **Tunnel Ingress Automation**: Updates Cloudflare Tunnel ingress rules instantly upon subdomain changes.
-   **Professional Versioning**: Clean API structure using `/api/v1/`.
-   **Secure Authentication**: Token-based security powered by Laravel Sanctum.
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
| `/api/v1/domains` | `POST` | Register a new domain for subdomain management |
| `/api/v1/subdomains` | `GET` | Retrieve all registered subdomains |
| `/api/v1/subdomains` | `POST` | Create a new subdomain (Auto-assigns port) |
| `/api/v1/subdomains/{id}` | `DELETE` | Remove a subdomain mapping |

### Example Request (Domain Management)
`POST /api/v1/domains`
```json
{
    "domain": "another-domain.id",
    "zone_id": "YOUR_CLOUDFLARE_ZONE_ID",
    "account_id": "YOUR_CLOUDFLARE_ACCOUNT_ID",
    "tunnel_id": "YOUR_CLOUDFLARE_TUNNEL_ID"
}
```

### Example Request (Subdomain Management)
`POST /api/v1/subdomains`
```json
{
    "domain_id": 2,
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

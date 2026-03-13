# 🌐 FlareBridge - Cloudflare Tunnel Manager

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

FlareBridge is a professional REST API and Modern Dashboard built with Laravel 10 and Vue 3 to manage dynamic subdomain routing for **Cloudflare Zero Trust Tunnel**. FlareBridge acts as a smart registry and management hub for mapping subdomains to unique server ports, simplifying port-based routing for tunneling services.

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

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Vue 3 + Vite (SPA)
- **Security**: Laravel Sanctum (Token-Based Auth)
- **Database**: MySQL / MariaDB
- **Integration**: Cloudflare API Integration

## 🔑 Smart Onboarding & Integration

FlareBridge features a **Modern Onboarding Flow**. You no longer need to manually edit `.env` for Cloudflare credentials.

1.  **Run the App**: Start `php artisan serve` and `npm run dev`.
2.  **Setup Wizard**: Open your browser. The app will detect if it's the first run and guide you through entering:
    -   Cloudflare Email & API Token.
    -   Admin Account Registration.
    -   Initial Domain Configuration.
3.  **Automatic Sync**: Once setup is complete, FlareBridge will handle all DNS and Tunnel Ingress updates automatically.

## 📋 API Reference (v1)

| Endpoint | Method | Description | Auth |
| :--- | :--- | :--- | :--- |
| `/api/onboarding-check` | `GET` | Check if setup is completed | No |
| `POST` | `/api/setup` | Run initial setup & admin registration | No |
| `/api/v1/domains` | `GET` | List all managed domains | Yes |
| `/api/v1/domains` | `POST` | Register a new domain config | Yes |
| `/api/v1/subdomains` | `GET` | Retrieve all registered subdomains | Yes |
| `/api/v1/subdomains` | `POST` | Create a new subdomain (Auto-sync) | Yes |
| `/api/v1/subdomains/{id}` | `DELETE` | Remove a subdomain mapping | Yes |
| `/api/v1/settings` | `GET/POST` | Manage app settings & UI mode | Yes |
| `/api/v1/system/reset` | `POST` | Wipe all data & return to onboarding | Yes |

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

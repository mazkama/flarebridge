# 🌐 FlareBridge - Cloudflare Tunnel Manager

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

REST API project built with Laravel 10 to manage dynamic subdomain routing for **Cloudflare Zero Trust Tunnel**. FlareBridge acts as a smart registry for mapping subdomains to unique server ports, simplifying port-based routing for tunneling services.

## 🚀 Key Features

- **Unique Port Generation**: Automatically assigns a unique random port (3000-9000) for every new subdomain.
- **Service Registry**: Keeps track of `subdomain -> port` mappings in a MySQL database.
- **RESTful API**: Simple endpoints to list, create, and delete service mappings.
- **Domain Support**: Manage multiple root domains and their associated subdomains.
- **Validation**: Ensures no duplicate subdomains per domain and no port conflicts globaly.

## 🛠️ Tech Stack

- **Backend**: Laravel 10 (PHP 8.1)
- **Database**: MySQL
- **Tooling**: Composer, Artisan

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

### Example Response
```json
{
    "url": "my-cool-app.yourdomain.com",
    "port": 5432
}
```

## ⚙️ Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/your-username/cloudflared.git
   cd cloudflared
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

## 🗺️ Roadmap

- [ ] **Dashboard UI**: A premium web interface to manage mappings.
- [ ] **Cloudflare API Sync**: Automatically update Cloudflare Tunnel configuration.
- [ ] **Health Monitoring**: Monitor the uptime of services running on mapped ports.
- [ ] **Auth Layer**: API Key or Sanctum authentication for secure access.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---
Built with ❤️ for Cloudflare power users.

# 🌐 FlareBridge - Cloudflare Tunnel Manager

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

FlareBridge is a professional REST API and Modern Dashboard built with Laravel 10 and Vue 3 to manage dynamic subdomain routing for **Cloudflare Zero Trust Tunnel**. FlareBridge acts as a smart registry and management hub for mapping subdomains to unique server ports, simplifying port-based routing for tunneling services.

## 🔒 API Security

FlareBridge is protected by **Laravel Sanctum**. To use the API, you must provide a Bearer Token in the HTTP headers.
1. **Login**: Authenticate via `/api/login` to get your session token.
2. **Usage**: Add header `Authorization: Bearer YOUR_TOKEN`.
3. **Rotation**: You can renew your token anytime in the **Documentation** tab.

## 🚀 Key Features

-   **Premium UI/UX**: High-end glassmorphism dashboard with custom **Toast Notifications** and **Confirmation Modals** (no native alerts).
-   **Multi-language Support (EN/ID)**: Seamless switching between English and Indonesian with persistent settings.
-   **Custom Port Control**: Option to manually assign ports or let FlareBridge auto-generate them.
-   **Fully Responsive**: Optimized for mobile, tablet, and desktop view.
-   **Automated DNS Sync**: Automatically creates/updates CNAME records for subdomains.
-   **Tunnel Ingress Automation**: Updates Cloudflare Tunnel ingress rules instantly.
-   **Credential Validation**: Real-time verification of Cloudflare tokens during setup.
-   **Secure Authentication**: Session protection via Laravel Sanctum.
-   **Multi-tunnel Mapping**: Use one domain with multiple different Cloudflare Tunnels simultaneously.
-   **Bulk Subdomain API**: Create multiple subdomain mappings in a single API request for high-scale automation.
-   **Clean API Structure**: Standardized `/api/v1/` versioning for all endpoints.

## 🛠️ Tech Stack

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Vue 3 + Vite (SPA)
- **Styling**: Tailwind CSS (Premium Glassmorphism)
- **Security**: Laravel Sanctum (Auth)

## 🔑 Smart Onboarding & Integration

FlareBridge features a **Modern Onboarding Flow**. 

1.  **Run the App**: Start `php artisan serve` and `npm run dev`.
2.  **Setup Wizard**: Open your browser. The app will detect if it's the first run and guide you through entering credentials.
3.  **Automatic Validation**: FlareBridge verifies your Cloudflare credentials in real-time during setup.
4.  **Automatic Sync**: Once setup is complete, everything is handled automatically.

## 📋 API Reference (v1)

| Method | Endpoint | Description | Payload Example | Auth |
| :--- | :--- | :--- | :--- | :--- |
| **POST** | `/api/login` | Authenticate and get token | `{ "email": "...", "password": "..." }` | No |
| **GET** | `/api/v1/domains` | List all root domains | - | Yes |
| **POST** | `/api/v1/domains` | Register new domain | `{ "domain": "example.com", ... }` | Yes |
| **GET** | `/api/v1/subdomains` | List all subdomain mappings | - | Yes |
| **POST** | `/api/v1/subdomains` | Create new mapping | `{ "domain_id": 1, "subdomain": "app", "port": 8080 }` | Yes |
| **PUT** | `/api/v1/subdomains/{id}` | Update mapping/port | `{ "subdomain": "blog", "port": 9000 }` | Yes |
| **DELETE** | `/api/v1/subdomains/{id}` | Remove mapping & DNS | - | Yes |
| **POST** | `/api/v1/system/reset` | Wipe all data & restart | - | Yes |
| **POST** | `/api/v1/system/token/renew` | Rotate API Token | - | Yes |

---
Built with ❤️ for Cloudflare power users.

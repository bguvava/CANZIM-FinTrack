# Configuration Guide - CANZIM FinTrack

**Document Version:** 1.0.0  
**Last Updated:** November 14, 2025

---

## 📋 Overview

This guide covers all configuration files and settings for the CANZIM FinTrack application.

---

## 🔧 Environment Configuration (.env)

### Application Settings

```env
# Application Identity
APP_NAME="CANZIM FinTrack"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost

# Localization
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_canzimdb
DB_USERNAME=root
DB_PASSWORD=
```

### Session Configuration

```env
# Session timeout: 5 minutes (300 seconds)
SESSION_DRIVER=database
SESSION_LIFETIME=5
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

### Cache Configuration

```env
CACHE_STORE=database
CACHE_PREFIX=
```

### Queue Configuration

```env
QUEUE_CONNECTION=database
```

### Mail Configuration

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🗄️ Database Configuration (config/database.php)

The database configuration is managed through the `.env` file. No changes needed to `config/database.php`.

---

## 🔐 Authentication Configuration (config/sanctum.php)

Laravel Sanctum is configured for API token authentication.

**Token Expiration:** Tokens do not expire by default  
**Stateful Domains:** localhost, 127.0.0.1

---

## 🌐 CORS Configuration (config/cors.php)

Cross-Origin Resource Sharing settings for API access.

**Allowed Origins:**

- http://localhost
- http://localhost:3000
- http://localhost:5173
- http://127.0.0.1
- http://127.0.0.1:3000
- http://127.0.0.1:5173

**Allowed Methods:** All (GET, POST, PUT, PATCH, DELETE, OPTIONS)  
**Allowed Headers:** All  
**Supports Credentials:** true

---

## ⏱️ Session Configuration (config/session.php)

**Driver:** database  
**Lifetime:** 5 minutes (300 seconds)  
**Expire on Close:** false  
**Encrypt:** false  
**Cookie Name:** CANZIM_session

---

## 📝 Logging Configuration (config/logging.php)

**Default Channel:** stack  
**Stack Channels:** single  
**Log Level:** debug

**Production Settings:**

- Change `LOG_LEVEL` to `error`
- Use `daily` log channel for rotation

---

## 🎨 Frontend Configuration

### Vite Configuration (vite.config.js)

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: "0.0.0.0",
        port: 5173,
        hmr: {
            host: "localhost",
        },
    },
});
```

### TailwindCSS Configuration (resources/css/app.css)

```css
@import "tailwindcss";
@import "./animations.css";

@theme {
    /* CANZIM Brand Colors */
    --color-canzim-primary: #1e40af;
    --color-canzim-secondary: #2563eb;
    --color-canzim-accent: #60a5fa;
    --color-canzim-dark: #1e3a8a;

    /* Animation Durations */
    --animate-fast: 150ms;
    --animate-base: 200ms;
    --animate-medium: 250ms;
    --animate-slow: 300ms;
    --animate-slower: 400ms;
}
```

---

## 🧪 Testing Configuration

### PHPUnit Configuration (phpunit.xml)

- Database connection: SQLite in-memory for tests
- Environment: testing
- Bootstrap: vendor/autoload.php

### Vitest Configuration (vitest.config.js)

- Test environment: happy-dom
- Coverage provider: v8
- Coverage directory: coverage/

---

## 🔒 Security Settings

### Encryption

- Algorithm: AES-256-CBC
- Key: Generated via `php artisan key:generate`

### Password Hashing

- Algorithm: bcrypt
- Rounds: 12

### CSRF Protection

- Enabled by default for all web routes
- Token included in forms and AJAX requests

---

## 📞 Support

For configuration issues, see [Troubleshooting Guide](./08_TROUBLESHOOTING.md)

# Laravel Boost MCP Server - Installation Guide

**Status:** ✅ **INSTALLED AND CONFIGURED**  
**Date:** November 14, 2025  
**VS Code Version:** VS Code Insiders  
**Laravel Boost Version:** v1.8.0  
**Laravel MCP Version:** v0.3.3

---

## 📋 What is Laravel Boost?

Laravel Boost is an official MCP (Model Context Protocol) server from Laravel that provides AI assistants with powerful tools to understand and work with your Laravel application. It gives GitHub Copilot direct access to your application's structure, database schema, routes, configuration, and more.

### Key Features

1. **Application Info** - Reads PHP & Laravel versions, database engine, ecosystem packages, and Eloquent models
2. **Database Schema** - Inspects and reads your complete database schema with intelligent analysis
3. **Database Queries** - Executes queries against your database directly from your AI assistant
4. **Route Inspector** - Inspects and analyzes your application's routes for better navigation
5. **Artisan Commands** - Lists and inspects available Artisan commands for streamlined development
6. **Tinker Integration** - Executes suggested code within the context of your Laravel application
7. **Configuration Access** - Gets configuration values and available keys for more accurate code generation
8. **Documentation Search** - Queries Laravel's hosted documentation API fine-tuned to your installed packages
9. **Error Tracking** - Reads application logs, browser errors, and tracks the last encountered issues

---

## 🔧 Installation Summary

### Step 1: Composer Package Installation

```bash
composer require laravel/boost --dev
```

**Installed Packages:**

- `laravel/boost` v1.8.0
- `laravel/mcp` v0.3.3 (dependency)
- `laravel/roster` v0.2.9 (dependency)

### Step 2: Run Boost Installation

```bash
php artisan boost:install --no-interaction
```

**What This Did:**

- Detected GitHub Copilot as the active AI agent
- Detected VS Code as the active code editor
- Installed 10 Laravel Boost guidelines
- Created MCP server configuration for VS Code
- Updated `.github/copilot-instructions.md` with Laravel-specific guidelines

---

## 📁 Files Created/Modified

### 1. `.vscode/mcp.json` (NEW)

MCP server configuration for VS Code:

```json
{
    "servers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        }
    }
}
```

### 2. `boost.json` (NEW)

Laravel Boost configuration tracking:

```json
{
    "agents": ["copilot"],
    "editors": ["vscode"],
    "guidelines": []
}
```

### 3. `.github/copilot-instructions.md` (UPDATED)

Now includes Laravel Boost guidelines with sections:

- `=== foundation rules ===` - Core Laravel best practices
- `=== boost rules ===` - Laravel Boost MCP server usage
- `=== php rules ===` - PHP coding standards
- `=== laravel/core rules ===` - Laravel framework conventions
- `=== laravel/v12 rules ===` - Laravel 12 specific features
- `=== pint/core rules ===` - Laravel Pint code formatting
- `=== phpunit/core rules ===` - PHPUnit testing standards
- `=== tailwindcss/core rules ===` - Tailwind CSS usage
- `=== tailwindcss/v4 rules ===` - Tailwind v4 specifics
- `=== tests rules ===` - Test enforcement and structure

---

## 🚀 How to Use Laravel Boost

### Enabling Laravel Boost in VS Code Insiders

1. **Open Command Palette** - `Ctrl+Shift+P` (Windows/Linux) or `Cmd+Shift+P` (Mac)
2. **Search for "MCP: List Servers"** - Press Enter
3. **Select "laravel-boost"** - Arrow to it and press Enter
4. **Choose "Start server"** - The server will start running

### Verifying Laravel Boost is Running

You can verify the MCP server is working by asking GitHub Copilot questions like:

- "What database tables do I have?"
- "Show me all routes in this application"
- "What Laravel version is this project using?"
- "List all Artisan commands available"
- "What Eloquent models exist in this project?"

### Manual MCP Server Start (if needed)

If you need to manually start the MCP server:

```bash
php artisan boost:mcp
```

**Note:** This command hangs waiting for JSON RPC MCP requests, so only run it when the MCP client (VS Code) needs it.

---

## 🛠️ Available Laravel Boost Tools

Laravel Boost provides these tools to GitHub Copilot:

1. **`list-artisan-commands`** - Lists all available Artisan commands with descriptions
2. **`get-application-info`** - Retrieves PHP version, Laravel version, database engine, installed packages
3. **`get-database-schema`** - Inspects complete database schema with tables, columns, indexes, foreign keys
4. **`database-query`** - Executes read-only database queries
5. **`get-routes`** - Lists all application routes with methods, URIs, controllers
6. **`get-config`** - Retrieves configuration values
7. **`tinker`** - Executes PHP code within the Laravel application context
8. **`search-docs`** - Searches Laravel documentation specific to your installed package versions
9. **`get-logs`** - Reads application log files
10. **`browser-logs`** - Accesses browser console errors and exceptions
11. **`get-absolute-url`** - Generates proper URLs for the application

---

## 📚 Laravel Boost Guidelines Installed

The following guidelines are now active in GitHub Copilot:

### Core Guidelines

1. **Foundation Rules** - Basic Laravel application principles
2. **Boost Rules** - How to use Laravel Boost MCP tools
3. **PHP Rules** - PHP 8.2+ coding standards
4. **Laravel Core Rules** - Laravel framework best practices
5. **Laravel v12 Rules** - Laravel 12 specific features and structure

### Development Tools

6. **Pint Core** - Laravel Pint code formatting standards
7. **PHPUnit Core** - PHPUnit testing conventions
8. **Tailwind CSS Core** - Tailwind utility-first approach
9. **Tailwind v4** - Tailwind 4 specific features
10. **Tests** - Test enforcement and 100% coverage requirements

---

## 🔍 Example Usage Scenarios

### Scenario 1: Database Schema Exploration

**You ask:** "What columns does the expenses table have?"

**Copilot uses:** `get-database-schema` tool to inspect the database, then provides detailed information about the `expenses` table including all columns, data types, indexes, and foreign keys.

### Scenario 2: Route Discovery

**You ask:** "Show me all API routes for expenses"

**Copilot uses:** `get-routes` tool to list all routes, then filters to show only `/api/v1/expenses/*` routes with their methods and controllers.

### Scenario 3: Configuration Lookup

**You ask:** "What's the session timeout configured?"

**Copilot uses:** `get-config` tool to retrieve `session.lifetime` value (5 minutes for this project).

### Scenario 4: Artisan Command Discovery

**You ask:** "How do I create a new controller?"

**Copilot uses:** `list-artisan-commands` tool to find `make:controller` command and provides the correct syntax with available options.

### Scenario 5: Testing Code in Context

**You ask:** "Can you test if this query returns the correct data?"

**Copilot uses:** `tinker` tool to execute the query in your Laravel application context and shows actual results.

---

## ⚙️ Configuration Files

### MCP Server Configuration (`.vscode/mcp.json`)

This file tells VS Code how to start the Laravel Boost MCP server:

- **Command:** `php` - Uses the PHP interpreter
- **Args:** `["artisan", "boost:mcp"]` - Runs the Boost MCP Artisan command

### Boost Configuration (`boost.json`)

This file tracks your Laravel Boost setup:

- **agents:** `["copilot"]` - GitHub Copilot is configured
- **editors:** `["vscode"]` - VS Code is configured
- **guidelines:** `[]` - No custom third-party guidelines (using defaults)

---

## 🔄 Updating Laravel Boost

To update Laravel Boost and its guidelines in the future:

```bash
# Update the package
composer update laravel/boost --dev

# Re-run installation to update guidelines
php artisan boost:install
```

---

## 🚨 Troubleshooting

### Issue 1: MCP Server Not Starting

**Solution:** Ensure you've enabled it in VS Code:

1. Open Command Palette (`Ctrl+Shift+P`)
2. Search "MCP: List Servers"
3. Start "laravel-boost"

### Issue 2: Copilot Not Using Laravel Boost Tools

**Solution:** Check that:

1. MCP server is running (green indicator in VS Code)
2. `.vscode/mcp.json` file exists
3. VS Code has been restarted after installation

### Issue 3: Tools Not Working

**Solution:** Verify Laravel application is properly configured:

- Database connection is working
- `.env` file is configured
- `php artisan` commands work in terminal

### Issue 4: Permission Errors

**Solution:** Ensure proper file permissions:

```bash
# Windows
icacls .vscode\mcp.json /grant Everyone:F

# Linux/Mac
chmod 644 .vscode/mcp.json
```

---

## 📖 Documentation Resources

- **Laravel Boost Official Site:** https://boost.laravel.com/
- **Laravel Boost GitHub:** https://github.com/laravel/boost
- **Installation Guide:** https://boost.laravel.com/installed
- **Laravel MCP Documentation:** https://laravel.com/docs/12.x/mcp
- **Model Context Protocol (MCP):** https://modelcontextprotocol.io/

---

## 🎯 Benefits for CANZIM FinTrack Development

### 1. Faster Development

Copilot now has direct access to:

- All 26 database tables and their schemas
- All API routes and controllers
- Eloquent models and relationships
- Configuration settings

### 2. More Accurate Code Generation

Copilot can:

- Generate queries based on actual database structure
- Create controllers that match existing patterns
- Suggest code that follows project conventions
- Provide accurate migration and model code

### 3. Better Debugging

Copilot can:

- Read application logs to understand errors
- Access browser console errors
- Execute test queries in Tinker
- Inspect actual configuration values

### 4. Context-Aware Assistance

Copilot knows:

- This is a Laravel 12 application
- PHP 8.2.12 is being used
- MySQL 8.0+ is the database
- Tailwind CSS v4 is for styling
- PHPUnit v11 is for testing

---

## ✅ Verification Checklist

- [x] Laravel Boost package installed (`laravel/boost` v1.8.0)
- [x] MCP server configuration created (`.vscode/mcp.json`)
- [x] Boost configuration created (`boost.json`)
- [x] Copilot instructions updated (`.github/copilot-instructions.md`)
- [x] 10 Laravel Boost guidelines installed
- [x] MCP server command verified (`php artisan boost:mcp`)
- [x] GitHub Copilot detected as active agent
- [x] VS Code detected as active editor

---

## 🎓 Key Takeaways

1. **Laravel Boost is an MCP Server** - It provides structured access to your Laravel application
2. **Tools are Available to Copilot** - 11+ specialized tools for database, routes, config, etc.
3. **Guidelines Enhance AI Quality** - 10 Laravel-specific guidelines ensure better code generation
4. **No Manual Configuration Needed** - `php artisan boost:install` handled everything automatically
5. **Works Seamlessly** - MCP server starts automatically when VS Code needs it

---

## 📝 Next Steps

1. **Restart VS Code Insiders** to ensure all changes are loaded
2. **Enable the MCP Server** using the Command Palette
3. **Test Laravel Boost** by asking Copilot about your database or routes
4. **Continue Development** with enhanced AI assistance

---

**Installation Completed By:** GitHub Copilot  
**Installation Date:** November 14, 2025  
**Status:** ✅ **FULLY CONFIGURED AND READY TO USE**

---

🎉 **Laravel Boost is now enhancing your CANZIM FinTrack development experience!**

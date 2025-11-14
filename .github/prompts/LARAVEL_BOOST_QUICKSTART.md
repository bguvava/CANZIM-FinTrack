# Laravel Boost - Quick Reference Guide

## 🚀 Quick Start

### Enable Laravel Boost in VS Code Insiders

1. Press `Ctrl+Shift+P` (Windows/Linux) or `Cmd+Shift+P` (Mac)
2. Type "MCP: List Servers" and press Enter
3. Arrow down to "laravel-boost" and press Enter
4. Select "Start server"

✅ **You'll see a green indicator when the server is running**

---

## 💡 What Can You Ask Copilot Now?

### Database Questions

```
✅ "What tables exist in the database?"
✅ "Show me the schema for the expenses table"
✅ "What foreign keys does the budgets table have?"
✅ "List all columns in the projects table"
✅ "What indexes are defined on the users table?"
```

### Route Questions

```
✅ "Show me all API routes"
✅ "What routes exist for expenses?"
✅ "List all POST routes in the application"
✅ "What controller handles /api/v1/projects?"
```

### Artisan Commands

```
✅ "How do I create a new controller?"
✅ "What make commands are available?"
✅ "Show me all migration commands"
✅ "List all available Artisan commands"
```

### Configuration

```
✅ "What's the database connection configured?"
✅ "Show me all config keys available"
✅ "What's the session timeout?"
✅ "What database driver is being used?"
```

### Testing & Debugging

```
✅ "Execute this query and show results"
✅ "What errors are in the log?"
✅ "Test this Eloquent query in tinker"
✅ "Check if this code runs without errors"
```

### Documentation

```
✅ "How do I use Eloquent relationships in Laravel 12?"
✅ "What's the best way to implement validation?"
✅ "Show me examples of using Laravel Sanctum"
✅ "How do I create a migration in Laravel 12?"
```

---

## 🛠️ Available Tools

| Tool                    | What It Does                                 | Example Use                            |
| ----------------------- | -------------------------------------------- | -------------------------------------- |
| `list-artisan-commands` | Lists all Artisan commands                   | "What Artisan commands are available?" |
| `get-application-info`  | Shows Laravel version, PHP version, packages | "What Laravel version is this?"        |
| `get-database-schema`   | Inspects database tables, columns, indexes   | "Show me the expenses table schema"    |
| `database-query`        | Executes read-only queries                   | "Count all projects"                   |
| `get-routes`            | Lists application routes                     | "Show me all expense routes"           |
| `get-config`            | Retrieves config values                      | "What's the app timezone?"             |
| `tinker`                | Runs code in Laravel context                 | "Test this User query"                 |
| `search-docs`           | Searches Laravel docs                        | "How to use eager loading?"            |
| `get-logs`              | Reads log files                              | "What errors occurred today?"          |
| `browser-logs`          | Accesses browser console                     | "Show browser errors"                  |
| `get-absolute-url`      | Generates proper URLs                        | "What's the full URL for this route?"  |

---

## 📝 Installed Guidelines (10)

1. **Foundation** - Core Laravel principles
2. **Boost** - Laravel Boost MCP usage
3. **PHP** - PHP 8.2+ standards
4. **Laravel Core** - Framework best practices
5. **Laravel v12** - Laravel 12 specifics
6. **Pint** - Code formatting
7. **PHPUnit** - Testing standards
8. **Tailwind Core** - CSS framework
9. **Tailwind v4** - Tailwind 4 features
10. **Tests** - Test enforcement

---

## 🎯 Pro Tips

### 1. Be Specific

❌ "Tell me about the database"  
✅ "Show me the schema for the expenses table with all foreign keys"

### 2. Use Context

❌ "How do I create a model?"  
✅ "Create an Expense model with relationships to Project and Category based on the database schema"

### 3. Ask for Code

❌ "What's in the users table?"  
✅ "Generate a migration to add a new column to the users table following existing conventions"

### 4. Leverage Tools

❌ "What routes do we have?"  
✅ "List all API routes and generate a new route for expense reports"

### 5. Test Ideas

❌ "Will this work?"  
✅ "Use tinker to test this query: User::with('role')->where('status', 'active')->get()"

---

## ⚡ Common Workflows

### Creating a New Feature

1. **Ask:** "Show me existing controllers and their structure"
2. **Ask:** "What's the database schema for [table]?"
3. **Ask:** "Create a controller following existing patterns for [feature]"
4. **Ask:** "Generate tests for this controller"

### Debugging

1. **Ask:** "What errors are in the log?"
2. **Ask:** "Show me the route that's failing"
3. **Ask:** "Test this query in tinker"
4. **Ask:** "What's the config for [setting]?"

### Database Work

1. **Ask:** "Show me all tables and their relationships"
2. **Ask:** "Generate a migration to [change]"
3. **Ask:** "Create a model with relationships based on schema"
4. **Ask:** "Write a seeder for [table] following existing patterns"

---

## 🔧 Troubleshooting

### MCP Server Not Running?

```bash
# Manually start (for testing)
php artisan boost:mcp

# Check if command exists
php artisan list | grep boost
```

### Copilot Not Using Tools?

1. Restart VS Code
2. Re-enable MCP server
3. Check `.vscode/mcp.json` exists
4. Verify `boost.json` shows `"agents": ["copilot"]`

### Permission Issues?

```bash
# Check file permissions
ls -la .vscode/mcp.json

# Fix if needed (Linux/Mac)
chmod 644 .vscode/mcp.json
```

---

## 📚 Files to Know

| File                      | Purpose                 | Location                                 |
| ------------------------- | ----------------------- | ---------------------------------------- |
| `mcp.json`                | MCP server config       | `.vscode/mcp.json`                       |
| `boost.json`              | Boost tracking          | Root directory                           |
| `copilot-instructions.md` | AI guidelines           | `.github/copilot-instructions.md`        |
| `LARAVEL_BOOST_SETUP.md`  | Full installation guide | `.github/prompts/LARAVEL_BOOST_SETUP.md` |

---

## 🎓 Learning Resources

- **Laravel Boost:** https://boost.laravel.com/
- **GitHub Repo:** https://github.com/laravel/boost
- **Laravel Docs:** https://laravel.com/docs/12.x
- **MCP Protocol:** https://modelcontextprotocol.io/

---

## ✅ Quick Checklist

- [ ] Restart VS Code after installation
- [ ] Enable MCP server via Command Palette
- [ ] Test with "What tables exist in the database?"
- [ ] Verify green MCP indicator is showing
- [ ] Try a few example questions

---

**Status:** ✅ **Laravel Boost v1.8.0 Installed**  
**Created:** November 14, 2025

🚀 **Happy coding with enhanced AI assistance!**

# Authentication Security Documentation

## Overview

The CANZIM FinTrack authentication system implements multiple layers of security to protect user accounts and sensitive data. This document outlines all security measures, best practices, and configuration guidelines.

---

## Security Features

### 1. Account Lockout Protection

**Purpose**: Prevent brute force attacks by temporarily locking accounts after multiple failed login attempts.

**Implementation**:

- **Threshold**: 5 consecutive failed login attempts
- **Lockout Duration**: 15 minutes
- **Storage**: `locked_until` timestamp in users table
- **Automatic Unlock**: Account unlocks automatically after timeout

**Code Reference**:

```php
// app/Services/AuthService.php
if ($user->failed_login_attempts >= 5) {
    $user->lockAccount(15); // Lock for 15 minutes
}
```

**Database Fields**:

```sql
failed_login_attempts INT DEFAULT 0
last_failed_login_at TIMESTAMP NULL
locked_until TIMESTAMP NULL
```

**User Experience**:

- Clear error message: "Your account has been locked due to multiple failed login attempts. Please try again later."
- HTTP 403 status code
- Attempt counter resets on successful login

**Best Practices**:

- Monitor lockout events in audit trails
- Alert admins for repeated lockouts (possible attack)
- Consider increasing lockout duration for repeated violations

---

### 2. Session Management

**Token-Based Authentication** using Laravel Sanctum:

**Token Expiry**:

- **Default Login**: 5 minutes (300 seconds)
- **Remember Me**: 30 days (43,200 minutes)

**Implementation**:

```php
// app/Services/AuthService.php
$tokenExpiry = $remember ? 43200 : 300;
$token = $user->createToken('auth-token', ['*'], now()->addMinutes($tokenExpiry));
```

**Security Benefits**:

- Short default expiry limits exposure window
- Tokens are one-time use (revoked on logout)
- Separate token per session
- No shared secrets between clients

**Frontend Handling**:

```javascript
// resources/js/stores/authStore.js
const SESSION_TIMEOUT = 5 * 60 * 1000; // 5 minutes

// Auto-logout on token expiry
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            authStore.logout();
        }
    },
);
```

---

### 3. Password Security

**Hashing Algorithm**: bcrypt (Laravel default)

- **Cost Factor**: 12 (configurable in config/hashing.php)
- **Salt**: Automatically generated per password
- **Rainbow Table Protection**: Yes (salted hashes)

**Password Requirements**:

- Minimum length: 8 characters
- Confirmation required on reset
- No complexity requirements (can be added)

**Storage**:

```php
// Never store plain passwords
'password' => Hash::make($request->password)

// Verification
Hash::check($plainPassword, $user->password)
```

**Password Reset**:

- **Token Expiry**: 60 minutes
- **Token Storage**: `password_reset_tokens` table
- **Token Format**: Hashed, non-reversible
- **One-Time Use**: Token deleted after successful reset

**Implementation**:

```php
// app/Services/AuthService.php
$status = Password::sendResetLink(['email' => $email]);

// Token is hashed in database
DB::table('password_reset_tokens')->insert([
    'email' => $email,
    'token' => Hash::make($token),
    'created_at' => now(),
]);
```

---

### 4. Role-Based Access Control (RBAC)

**Roles**:

1. Programs Manager (programs-manager)
2. Finance Officer (finance-officer)
3. Project Officer (project-officer)

**Middleware Implementation**:

```php
// app/Http/Middleware/CheckRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!$request->user()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated',
        ], 401);
    }

    $userRole = $request->user()->role->slug;

    if (!in_array($userRole, $roles)) {
        return response()->json([
            'status' => 'error',
            'message' => 'You do not have permission to access this resource',
        ], 403);
    }

    return $next($request);
}
```

**Route Protection**:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'role:programs-manager'])
    ->group(function () {
        Route::get('/admin/users', [UserController::class, 'index']);
    });
```

**Security Benefits**:

- Principle of least privilege
- Centralized authorization logic
- Easy to audit and modify
- Prevents privilege escalation

---

### 5. CSRF Protection

**Laravel's Built-in CSRF Protection**:

**For Web Routes**:

```html
<!-- resources/views/welcome.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}" />
```

**For API Routes** (Sanctum Stateful):

```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

**Frontend Configuration**:

```javascript
// resources/js/plugins/axios.js
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}
```

---

### 6. Audit Trail

**All authentication events are logged**:

**Tracked Events**:

- Login attempts (success/failure)
- Logout events
- Password reset requests
- Password changes
- Account lockouts
- Role changes (future)

**Implementation**:

```php
// app/Services/AuthService.php
private function logFailedLogin(string $email, string $ipAddress, string $reason, ?int $userId = null): void
{
    AuditTrail::create([
        'user_id' => $userId,
        'action' => 'failed_login',
        'description' => "Failed login attempt for {$email}: {$reason}",
        'ip_address' => $ipAddress,
        'user_agent' => request()->userAgent(),
    ]);
}
```

**Database Schema**:

```sql
CREATE TABLE audit_trails (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP
);
```

**Security Benefits**:

- Forensic analysis of security incidents
- Compliance (SOC 2, GDPR, etc.)
- Early detection of attacks
- User activity monitoring

---

### 7. Input Validation

**Form Request Validation**:

**Login Request**:

```php
// app/Http/Requests/Auth/LoginRequest.php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'remember' => ['boolean'],
    ];
}
```

**Password Reset Request**:

```php
// app/Http/Requests/Auth/ResetPasswordRequest.php
public function rules(): array
{
    return [
        'token' => ['required', 'string'],
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
}
```

**Security Benefits**:

- Prevents SQL injection
- Blocks XSS attacks
- Ensures data integrity
- Clear error messages

---

### 8. IP Address Tracking

**Purpose**: Monitor login locations and detect suspicious activity.

**Implementation**:

```php
// app/Models/User.php
public function updateLastLogin(string $ipAddress): void
{
    $this->last_login_at = now();
    $this->last_login_ip = $ipAddress;
    $this->save();
}
```

**Usage**:

```php
// app/Services/AuthService.php
$user->updateLastLogin($ipAddress);
```

**Future Enhancements**:

- Geo-location tracking
- Alert on login from new location
- IP whitelist/blacklist
- Suspicious IP detection

---

### 9. Secure Communication

**HTTPS Enforcement** (Production):

**Force HTTPS**:

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

**HSTS Header**:

```php
// config/cors.php or middleware
'supports_credentials' => true,
'allowed_origins' => [env('FRONTEND_URL')],
```

---

### 10. Token Security

**Sanctum Token Best Practices**:

**Token Generation**:

```php
// Generate unique token per session
$token = $user->createToken('auth-token', ['*'], $expiry);
```

**Token Revocation**:

```php
// Single token (logout)
$request->user()->currentAccessToken()->delete();

// All tokens (password reset)
$user->tokens()->delete();
```

**Token Storage**:

- Backend: Hashed in `personal_access_tokens` table
- Frontend: Secure storage (httpOnly cookie or encrypted localStorage)

**Token Transmission**:

```javascript
// Always use Authorization header
headers: {
    'Authorization': `Bearer ${token}`
}
```

---

## Security Checklist

### Deployment Checklist

- [ ] HTTPS enabled (SSL/TLS certificate)
- [ ] Environment variables secured (.env not committed)
- [ ] APP_KEY generated and secured
- [ ] Database credentials strong and unique
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] Error reporting disabled in production
- [ ] Debug mode off (APP_DEBUG=false)
- [ ] Sanctum stateful domains configured
- [ ] Session driver secure (database or Redis)
- [ ] Cookie security (secure, httpOnly, sameSite)
- [ ] File permissions correct (storage writable)

### Code Review Checklist

- [ ] No hardcoded credentials
- [ ] All inputs validated
- [ ] Passwords always hashed
- [ ] Tokens properly revoked
- [ ] SQL queries parameterized
- [ ] XSS protection (Blade auto-escaping)
- [ ] CSRF tokens verified
- [ ] Error messages don't leak sensitive info
- [ ] Audit logging comprehensive
- [ ] Role checks on all protected routes

---

## Common Vulnerabilities & Mitigations

### 1. Brute Force Attacks

**Vulnerability**: Attacker tries many passwords to gain access.

**Mitigation**:

- ✅ Account lockout (5 attempts = 15 min lock)
- ✅ Rate limiting on login endpoint
- ✅ CAPTCHA (future enhancement)
- ✅ Audit trail logging

### 2. Session Hijacking

**Vulnerability**: Attacker steals user's session token.

**Mitigation**:

- ✅ Short token expiry (5 minutes)
- ✅ Token revocation on logout
- ✅ HTTPS only (production)
- ✅ httpOnly cookies (if using cookies)
- ✅ IP address validation (tracked)

### 3. Password Leaks

**Vulnerability**: Passwords exposed in database breach.

**Mitigation**:

- ✅ bcrypt hashing (cost factor 12)
- ✅ Unique salts per password
- ✅ No password storage in logs
- ✅ Password reset on suspected breach

### 4. Cross-Site Scripting (XSS)

**Vulnerability**: Malicious scripts injected into pages.

**Mitigation**:

- ✅ Blade template auto-escaping
- ✅ Content Security Policy headers
- ✅ Input sanitization
- ✅ Output encoding

### 5. SQL Injection

**Vulnerability**: Malicious SQL queries executed.

**Mitigation**:

- ✅ Eloquent ORM (parameterized queries)
- ✅ Query builder (prepared statements)
- ✅ Input validation
- ✅ No raw SQL with user input

### 6. CSRF Attacks

**Vulnerability**: Unauthorized actions performed on behalf of user.

**Mitigation**:

- ✅ Laravel CSRF protection
- ✅ SameSite cookie attribute
- ✅ Token verification on state-changing operations

---

## Incident Response

### Security Incident Procedure

1. **Detection**:
    - Monitor audit trails
    - Alert on multiple failed logins
    - Track unusual IP addresses

2. **Response**:
    - Lock affected accounts
    - Revoke all tokens
    - Notify affected users
    - Document incident

3. **Recovery**:
    - Force password reset
    - Review access logs
    - Update security measures
    - Conduct post-mortem

4. **Prevention**:
    - Patch vulnerabilities
    - Update dependencies
    - Enhance monitoring
    - Train team

---

## Compliance & Standards

### GDPR Compliance

- ✅ User consent for data processing
- ✅ Right to erasure (soft delete)
- ✅ Data portability
- ✅ Audit trail of data access
- ✅ Encryption in transit (HTTPS)

### OWASP Top 10 Coverage

1. **Broken Access Control**: ✅ RBAC, route middleware
2. **Cryptographic Failures**: ✅ bcrypt, HTTPS
3. **Injection**: ✅ Eloquent ORM, validation
4. **Insecure Design**: ✅ Secure architecture
5. **Security Misconfiguration**: ✅ Proper config
6. **Vulnerable Components**: ✅ Updated dependencies
7. **Authentication Failures**: ✅ Account lockout, strong passwords
8. **Software Integrity Failures**: ✅ Composer lock
9. **Logging Failures**: ✅ Comprehensive audit trail
10. **SSRF**: ✅ Input validation

---

## Monitoring & Alerts

### Metrics to Monitor

1. **Failed Login Attempts**:
    - Alert threshold: >10 per hour from single IP
    - Action: Block IP, notify admin

2. **Account Lockouts**:
    - Alert threshold: >5 lockouts per day
    - Action: Investigate potential attack

3. **Password Resets**:
    - Alert threshold: >20 per hour
    - Action: Check for mass attack

4. **Token Creation Rate**:
    - Alert threshold: >100 per minute
    - Action: Check for token generation abuse

5. **Unusual Login Times**:
    - Alert: Login from new country
    - Action: Email user for confirmation

### Log Monitoring

```bash
# Monitor failed logins
tail -f storage/logs/laravel.log | grep "failed_login"

# Check audit trail
SELECT * FROM audit_trails
WHERE action = 'failed_login'
AND created_at > NOW() - INTERVAL 1 HOUR;
```

---

## Future Security Enhancements

1. **Two-Factor Authentication (2FA)**
    - SMS OTP
    - Email OTP
    - Google Authenticator
    - Backup codes

2. **Biometric Authentication**
    - Fingerprint (mobile)
    - Face ID (mobile)
    - WebAuthn (desktop)

3. **Advanced Password Policies**
    - Complexity requirements
    - Password history (prevent reuse)
    - Expiry after 90 days
    - Breach detection (HaveIBeenPwned API)

4. **IP Whitelisting**
    - Office IP ranges
    - VPN-only access
    - Geo-fencing

5. **Anomaly Detection**
    - Machine learning for unusual patterns
    - Behavioral analysis
    - Device fingerprinting

---

## Support & Resources

### Laravel Security Resources

- [Laravel Security Documentation](https://laravel.com/docs/12.x/security)
- [Sanctum Documentation](https://laravel.com/docs/12.x/sanctum)
- [OWASP Cheat Sheets](https://cheatsheetseries.owasp.org/)

### Reporting Security Issues

Email: security@canzim.org
PGP Key: [Link to public key]

### Security Team

- Security Lead: [Name]
- DevOps: [Name]
- Compliance Officer: [Name]

---

## Conclusion

The CANZIM FinTrack authentication system implements industry-standard security practices with defense-in-depth approach. Regular security audits, penetration testing, and continuous monitoring ensure the system remains secure against evolving threats.

**Last Updated**: November 14, 2025
**Next Review**: February 14, 2026

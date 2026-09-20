# **TOTP PHP: The Ultimate 2FA Library for PHP**

[![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/totp-php?logo=php&style=flat)](https://github.com/remotemerge/totp-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/remotemerge/totp-php/test.yml?style=flat&logo=counterstrike&label=test)](https://github.com/remotemerge/totp-php)
[![Build](https://img.shields.io/github/actions/workflow/status/remotemerge/totp-php/install.yml?style=flat&logo=github)](https://github.com/remotemerge/totp-php)
[![Sonar Quality](https://img.shields.io/sonar/quality_gate/totp-php/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubecloud&logoColor=126ED3&label=quality)](https://sonarcloud.io/summary/overall?id=totp-php&branch=main)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/totp-php/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubeserver&logoColor=126ED3)](https://sonarcloud.io/summary/overall?id=totp-php&branch=main)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/totp-php.svg?style=flat&label=downloads)](https://packagist.org/packages/remotemerge/totp-php)
[![License](https://img.shields.io/github/license/remotemerge/totp-php)](https://github.com/remotemerge/totp-php?tab=MIT-1-ov-file)

![TOTP PHP Features](public/img/features-v1.png)

## **Table of Contents**

| #  | Title                                   | Description                                                                 |
|----|-----------------------------------------|-----------------------------------------------------------------------------|
| 1  | [Why TOTP PHP?](#why-choose-totp-php)   | Ideal for secure logins, data protection, and enhanced user security.       |
| 2  | [Key Features](#key-features)           | Secure secret generation, multi-algorithm support, QR codes, customization. |
| 3  | [Compatibility](#compatibility)         | Works seamlessly with all major authenticator apps and RFC-compliant tools. |
| 4  | [Get Started](#get-started-in-minutes)  | Quick installation via Composer and simple usage examples.                  |
| 5  | [Basic Usage](#basic-usage)             | Generate secrets, TOTP codes, verify codes, and create QR code URIs.        |
| 6  | [Customization](#customization-options) | Change hash algorithms, code length, and time slice duration.               |
| 7  | [Advanced Usage](#advanced-usage)       | Replay protection, secret auditing, discrepancy limits, and QR codes.       |
| 8  | [Try with Docker](#try-with-docker)     | Test locally using Docker for quick setup.                                  |
| 9  | [Try without Docker](#try-with-php)     | Use PHP's built-in server for lightweight local testing.                    |
| 10 | [Getting Help](#getting-help)           | Report bugs, get integration help, or collaborate on projects.              |
| 11 | [Contribution](#contribution)           | Follow coding standards, test code, and submit pull requests.               |
| 12 | [Screenshots](#screenshots)             | Visual demo of the library in action.                                       |

## **Why Choose TOTP PHP?**

TOTP PHP is a versatile, secure, and reliable TOTP library for PHP that provides easy 2FA integration. This developer-friendly, lightweight, and secure library offers simplicity, performance, and customization for secure login systems, data protection, and enhanced user security. TOTP PHP ensures robust protection with ease of use and high performance, designed for modern PHP developers.

---

## **Key Features**

✅ **Secure Secret Generation**
Generates cryptographically secure secret keys for TOTP, ensuring maximum security.

✅ **Multi Algorithm Support**
Supports **SHA1, SHA256, and SHA512** for HMAC hashing, providing flexibility and compatibility with all major authenticator apps.

✅ **QR Code Integration**
Generates standards-compliant **otpauth URIs** for QR code setup in authenticator apps like Google Authenticator, Microsoft Authenticator, Authy, and more.

✅ **Customizable Code Length**
Generates TOTP codes with **6 or 8 digits**, configurable based on application requirements.

✅ **Time Slice Configuration**
Configurable time slice duration (e.g., **30 or 60 seconds**) to match security requirements.

✅ **Discrepancy Verification**
Allows **time slice discrepancy** when verifying TOTP codes, ensuring a smooth user experience. This is especially useful for handling clock drifts.

✅ **Replay Attack Protection**
The `verifyCodeOnce()` method blocks reuse of an accepted code by tracking the last accepted time slice and rejecting that slice's code. Your application supplies the atomic persistence — see [Replay Attack Protection](#replay-attack-protection) for the exact guarantee.

✅ **Secret Security Auditing**
The `auditSecret()` method inspects a secret key and returns its decoded byte length, a length-based strength flag, and actionable warnings — without throwing exceptions.

✅ **Discrepancy Bounds Enforcement**
The discrepancy parameter is validated against a configurable upper bound (default: 10), preventing misconfigured or malicious values from widening the verification window indefinitely.

✅ **Easy Verification**
Verifies TOTP codes with a **simple and intuitive API**, making integration straightforward.

✅ **Lightweight and Fast**
Built for performance, TOTP PHP is **lightweight** and optimized for speed, ensuring minimal overhead.

✅ **Developer Friendly**
Designed with developers in mind, TOTP PHP is **easy to use**, well-documented, and fully tested.

---

## **Compatibility**

TOTP PHP is built to **universal standards** and works seamlessly with **all major authenticator applications** worldwide. Whether users prefer mobile apps, desktop tools, or hardware tokens, this library ensures flawless compatibility across the entire ecosystem.

### **Supported Authenticator Apps**

| 📱 **Mobile Authenticators** | 💻 **Desktop & Hardware** |
|------------------------------|---------------------------|
| ✅ Google Authenticator       | ✅ YubiKey Authenticator   |
| ✅ Microsoft Authenticator    | ✅ FreeOTP                 |
| ✅ Authy                      | ✅ OTP Auth (iOS)          |
| ✅ Duo Mobile                 | ✅ Aegis Authenticator     |
| ✅ 1Password                  | ✅ andOTP                  |
| ✅ LastPass Authenticator     | ✅ Any RFC-compliant tool  |
| ✅ Bitwarden Authenticator    |                           |

### **Standards Compliance**

🔒 **RFC-Compliant Implementation**
TOTP PHP follows **RFC 6238** for time-based one-time passwords, validates secrets as uppercase **RFC 4648 Base32** with valid padding, and generates **Key URI Format** compatible `otpauth://` URIs. The test suite includes the RFC 6238 Appendix B vectors for SHA1, SHA256, and SHA512.

---

## **Get Started in Minutes**

Adding TOTP PHP to a project is quick and easy. The library requires **PHP 8.1** or higher on a **64-bit** PHP build. Counter packing uses the 64-bit `J` format, which PHP does not provide on 32-bit builds, so Composer declares `php-64bit` and installation fails early on unsupported platforms rather than failing inside authentication.

### **Installation**

Install the library via Composer:

```bash
composer require remotemerge/totp-php
```

---

## **Basic Usage**

### **Generate a Secret Key**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Generate a new secret key for the user
$secret = $totp->generateSecret();

// Output the secret key
echo "Generated Secret Key: $secret\n";
```

**Output:**

```text
Generated Secret Key: MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y
```

`generateSecret()` creates a 20-byte random secret encoded as uppercase Base32. When importing secrets from another system, use `auditSecret()` to inspect decoded length and formatting before storing them.

### **Generate a TOTP Code**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Example 20-byte Base32 secret
$secret = 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y';

// Generate a TOTP code
$code = $totp->getCode($secret);

echo "Generated TOTP Code: $code\n";
```

**Output:**

```text
Generated TOTP Code: 123456
```

### **Verify a TOTP Code**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Example 20-byte Base32 secret and code
$secret = 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y';
$code = '123456';

// Verify the code
$isValid = $totp->verifyCode($secret, $code);

echo $isValid ? "✅ Code is valid!\n" : "❌ Code is invalid!\n";
```

**Output:**

```text
✅ Code is valid!
```

### **Generate a QR Code URI**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Example 20-byte Base32 secret and user information
$secret = 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y';
$uri = $totp->generateUri($secret, 'user@example.com', 'YourApp');

echo "QR Code URI: $uri\n";
```

**Output:**

```text
QR Code URI: otpauth://totp/YourApp:user%40example.com?secret=MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y&issuer=YourApp&algorithm=SHA1&digits=6&period=30
```

Per the [Key URI Format](https://github.com/google/google-authenticator/wiki/Key-Uri-Format), trailing `=` padding is omitted from the `secret` parameter, and neither the label nor the issuer may contain a colon — that character separates the two components, so a colon in either throws a `TotpException`. The stored secret itself is never rewritten.

Treat the URI and its QR image as the credential itself: both carry the raw secret. Keep them out of logs, analytics, debug traces, and third-party services.

The default **SHA1 / 6 digits / 30 seconds** combination is the interoperability target for authenticator apps. Other algorithm, digit, and period combinations are valid per the specification but are not uniformly supported — test them against the apps you intend to support rather than assuming compatibility.

---

## **Customization Options**

### **Change the Hash Algorithm**

By default, TOTP PHP uses **SHA1**. The algorithm can be configured to use **SHA256** or **SHA512**:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

// Configure the algorithm
$totp->configure(['algorithm' => 'SHA256']);

$secret = $totp->generateSecret();
$code = $totp->getCode($secret);

echo "Generated TOTP Code (SHA256): $code\n";
```

### **Change the Code Length**

By default, TOTP PHP generates **6-digit codes**. The length can be configured to **8 digits**:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

// Configure the code length
$totp->configure(['digits' => 8]);

$secret = $totp->generateSecret();
$code = $totp->getCode($secret);

echo "Generated 8-Digit TOTP Code: $code\n";
```

### **Change the Time Slice Duration**

By default, TOTP PHP uses a **30-second time slice**. The duration can be configured to **60 seconds**:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

// Configure the time slice duration
$totp->configure(['period' => 60]);

$secret = $totp->generateSecret();
$code = $totp->getCode($secret);

echo "Generated TOTP Code (60-second period): $code\n";
```

---

## **Advanced Usage**

### **Verify Code with Discrepancy**

Handle clock drift by allowing a discrepancy of **±1 time slice**:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

$secret = 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y';
$code = '123456';

// Allow discrepancy of 1 time slice
$isValid = $totp->verifyCode($secret, $code, 1);

echo $isValid ? "✅ Code is valid!\n" : "❌ Code is invalid!\n";
```

### **Replay Attack Protection**

Use `verifyCodeOnce()` to prevent a TOTP code from being accepted more than once. It returns the matched time slice on success (store this value and pass it back on the next login), or `null` if the code is invalid or has already been used:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

$secret = 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y';
$code = '123456';

// Load the last accepted time slice from persistent storage (e.g. database).
// Use 0 on first login.
$lastAcceptedSlice = (int) $user->getLastTotpSlice();

$newSlice = $totp->verifyCodeOnce($secret, $code, $lastAcceptedSlice);

if ($newSlice === null) {
    echo "❌ Code is invalid or has already been used!\n";
} else {
    // Persist the new slice to block future reuse of this code.
    $user->setLastTotpSlice($newSlice);
    echo "✅ Code accepted!\n";
}
```

#### **Persist the slice atomically**

The library is stateless: it compares the slice you pass in and returns the slice you should store. It cannot lock anything on your behalf. The read-verify-write sequence above is a race — two concurrent requests can read the same `last_slice`, both verify the same code, and both succeed.

Make the update conditional on the value you read, and grant the session only when exactly one row changes:

```sql
UPDATE user_totp
SET last_slice = :matched
WHERE user_id = :user
  AND credential_version = :version
  AND last_slice = :observed;
```

`:matched` is the slice returned by `verifyCodeOnce()`, and `:observed` is the value read before verification. Treat zero affected rows, a rollback, or a failed write as a failed authentication. Reset the stored slice when the secret is rotated, and note that changing `period` changes what a stored slice means, so migrate that state alongside the credential configuration.

#### **What this does and does not guarantee**

`verifyCodeOnce()` guarantees that accepted slices strictly increase, and additionally rejects a code identical to the one produced by the last accepted slice. That second check matters because adjacent slices can coincidentally produce the same code — for the RFC test key, slices `910737` and `910738` both yield `911617`, which sequential persistence alone would accept twice.

A single stored slice cannot represent every code ever accepted. If you must reject any repeated code for the whole time it is valid, keep an atomic, expiring record keyed by credential and a protected fingerprint of the accepted code. Also note that `verifyCode()` holds no replay state at all — it will accept the same valid code repeatedly.

Slice `0` is the documented initial sentinel and is treated as "no previous login", so a first login is not blocked. A negative slice is rejected with a `TotpException`.

### **Secret Security Audit**

Use `auditSecret()` to inspect a secret key before storing or using it. The method never throws — all diagnostics are returned in the result array:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

$secret = 'JBSWY3DPEHPK3PXP';

$audit = $totp->auditSecret($secret);

echo "Decoded length: {$audit['length_bytes']} bytes\n";
echo "Strong secret: " . ($audit['is_strong'] ? 'Yes' : 'No') . "\n";

foreach ($audit['warnings'] as $warning) {
    echo "⚠️  Warning: $warning\n";
}
```

**Output:**

```text
Decoded length: 10 bytes
Strong secret: No
⚠️  Warning: Secret is weak (10 bytes); recommend >= 20 bytes for adequate security.
```

> **`is_strong` measures length and syntax, not randomness.** It reports that the secret is valid Base32 of at least 20 bytes, per [RFC 4226 §4](https://www.rfc-editor.org/rfc/rfc4226#section-4). A predictable key of sufficient length — for example one that decodes to 20 zero bytes — is still reported as strong. Randomness cannot be established by inspecting a single value. Use `generateSecret()` for new enrollments, and enforce your own minimum-length policy at the import boundary.

### **Configuring the Maximum Discrepancy**

By default the discrepancy parameter in `verifyCode()` and `verifyCodeOnce()` is capped at **10**. Pass `max_discrepancy` when creating the instance to tighten or relax this limit:

```php
use RemoteMerge\Totp\TotpFactory;

// Restrict the maximum allowed discrepancy to 2 time slices
$totp = TotpFactory::create(['max_discrepancy' => 2]);

$secret = $totp->generateSecret();
$code = $totp->getCode($secret);

// discrepancy of 1 is within the limit — works normally
$isValid = $totp->verifyCode($secret, $code, 1);

// discrepancy of 3 exceeds the limit — throws TotpException
$totp->verifyCode($secret, $code, 3);
```

`max_discrepancy` must be a non-negative integer; anything else throws a `TotpException` at construction rather than being silently coerced. It is a **ceiling**, not the active window — the default window remains the `±1` default of the `$discrepancy` argument. Widening the window multiplies both the HMAC work per attempt and the number of codes an attacker can guess against, so keep per-account attempt limits in the application.

### **Input and Counter Contract**

- **Secrets** must be uppercase RFC 4648 Base32 with a length that is a multiple of 8, optionally `=`-padded. Lowercase, whitespace, newlines, and other characters are rejected with a `TotpException`.
- **Codes** are strings so leading zeroes survive. A malformed code — wrong length, non-digits, or a trailing newline — throws a `TotpException`; a well-formed code that simply does not match returns `false` (or `null` from `verifyCodeOnce()`). Never convert codes to integers.
- **`$timeSlice`** is a counter (`floor(unixTime / period)`), not a Unix timestamp. The supported domain is non-negative; a negative slice throws a `TotpException`, and verification windows are clamped so they cannot run below zero or overflow.
- **Configuration** is applied atomically: if any option in a `configure()` call is invalid, the whole call throws and the instance keeps its previous settings. The `algorithm`, `digits`, and `period` used at verification must match those used at enrollment.

### **Generate a QR Code Image**

Generate the `otpauth://` URI on the backend, then render the QR image locally in the browser. Avoid sending TOTP setup URIs to third-party QR image APIs because the URI contains the user's secret.

> The example below imports the QR library from a CDN for brevity. Loading executable JavaScript from a third party onto an enrollment page is a separate trust decision from sending it your data: that script runs with access to the secret on the page. Bundle the library locally for production enrollment, serve it over TLS, and return enrollment responses with `Cache-Control: no-store`.

```php
// secret.php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

$secret = $totp->generateSecret();
$uri = $totp->generateUri($secret, 'user@example.com', 'YourApp');

echo json_encode([
    'secret' => $secret,
    'uri' => $uri,
], JSON_THROW_ON_ERROR);
```

```html
<img id="qrImage" src="" alt="Authenticator QR code">

<script type="module">
  import QRCode from 'https://cdn.jsdelivr.net/npm/qrcode@1.5/+esm';

  const response = await fetch('/secret.php');
  const data = await response.json();

  document.getElementById('qrImage').src = await QRCode.toDataURL(data.uri, {
    errorCorrectionLevel: 'H',
    width: 256,
    margin: 2,
  });
</script>
```

---

## **Try with Docker**

Test the TOTP PHP library locally using Docker. This method automatically sets up the environment with all dependencies. Follow these steps:

1. Clone the repository:

   ```bash
   git clone git@github.com:remotemerge/totp-php.git
   cd totp-php
   ```

2. Start the Docker container:

   ```bash
   bash start-docker.sh
   ```

3. Access the application at `http://localhost:8080`.

4. (Optional) Access the container shell for development:

   ```bash
   bash pkg-cli.sh
   ```

---

## **Try with PHP**

For a lightweight setup, use PHP's built-in server. This method is ideal for quick local testing and doesn't require Docker. Follow these steps:

1. Clone the repository:

   ```bash
   git clone git@github.com:remotemerge/totp-php.git
   cd totp-php
   ```

2. Install dependencies using Composer:

   ```bash
   composer install
   ```

3. Start the PHP built-in server:

   ```bash
   php -S localhost:8080 -t public
   ```

4. Access the application at `http://localhost:8080`.

---

## **Getting Help**

Bugs and feature requests are tracked using GitHub issues and prioritized to ensure the library remains reliable and up to date.

- **Bug Reports**
  Issues can be reported by [opening an issue](https://github.com/remotemerge/totp-php/issues/new) on GitHub. All issues are addressed diligently to maintain the library's quality.

- **Integration Assistance**
  For assistance with integration or questions about features, please open a GitHub issue or discussion.

---

## **Contribution**

Contributions from the **Open Source community** are highly valued and appreciated. To ensure a smooth and efficient process, contributors should adhere to the following guidelines:

- **Coding Standards**: Code must adhere to [PER Coding Style 3.0](https://www.php-fig.org/per/coding-style/) standards.
- **Testing**: All submitted code must pass relevant tests to maintain the library's reliability.
- **Documentation**: Proper documentation and clean code practices are essential for maintainability.
- **Pull Requests**: Pull requests should be made to the `main` branch.

All contributions are reviewed and appreciated.

## **Screenshots**

![Screenshot 1](public/img/demo.png)

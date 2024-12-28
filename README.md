# **TOTP PHP: The Ultimate 2FA Library for PHP**

[![X (formerly Twitter)](https://img.shields.io/badge/-@sapkotamadan-white?style=flat&logo=x&label=(formerly%20Twitter))](https://twitter.com/sapkotamadan)
[![Facebook](https://img.shields.io/badge/Facebook-NextSapkotaMadan-blue?style=flat&logo=facebook)](https://www.facebook.com/NextSapkotaMadan)
![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/totp-php)
![Build](https://img.shields.io/github/actions/workflow/status/remotemerge/totp-php/install.yml?style=flat&logo=github)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/totp-php.svg?style=flat&label=Downloads)](https://packagist.org/packages/remotemerge/totp-php)
![License](https://img.shields.io/github/license/remotemerge/totp-php)

**A developer friendly, lightweight, and secure TOTP solution for adding 2FA to PHP applications. TOTP PHP offers unmatched simplicity, performance, and customization, making it perfect for secure login systems, data protection, and enhanced user security.**

---

## **Why Choose TOTP PHP?**

Looking for a versatile, secure, and reliable TOTP library for PHP that provides easy 2FA integration? TOTP PHP stands out as your ultimate solution for seamless 2FA integration. Whether safeguarding login systems, securing sensitive data, or enhancing user security, TOTP PHP ensures robust protection with exceptional ease of use and high performance tailored for modern PHP developers.

---

## **Key Features**

✅ **Secure Secret Generation**  
Generate cryptographically secure secret keys for TOTP, ensuring maximum security for your users.

✅ **Multi Algorithm Support**  
Supports **SHA1, SHA256, and SHA512** for HMAC hashing, giving you flexibility and compatibility with all major authenticator apps.

✅ **QR Code Integration**  
Easily generate **QR codes** for seamless setup in authenticator apps like Google Authenticator, Microsoft Authenticator, Authy, and more.

✅ **Customizable Code Length**  
Generate TOTP codes with **6 or 8 digits**, tailored to your application's needs.

✅ **Time Slice Configuration**  
Customize the time slice duration (e.g., **30 or 60 seconds**) to match your security requirements.

✅ **Discrepancy Verification**
Allow a **time slice discrepancy** when verifying TOTP codes, ensuring a smooth user experience. This is especially useful for handling clock drifts.

✅ **Easy Verification**  
Verify TOTP codes with a **simple and intuitive API**, making integration a breeze.

✅ **Lightweight and Fast**  
Built for performance, TOTP PHP is **lightweight** and optimized for speed, ensuring minimal overhead.

✅ **Developer Friendly**  
Designed with developers in mind, TOTP PHP is **easy to use**, well-documented, and fully tested.

---

## **Get Started in Minutes**

Adding TOTP PHP to your project is quick and easy. Here's how:

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

```
Generated Secret Key: JBSWY3DPEHPK3PXP
```

### **Generate a TOTP Code**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Replace with your secret key
$secret = 'JBSWY3DPEHPK3PXP';

// Generate a TOTP code
$code = $totp->getCode($secret);

echo "Generated TOTP Code: $code\n";
```

**Output:**

```
Generated TOTP Code: 123456
```

### **Verify a TOTP Code**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Replace with your secret key and the code to verify
$secret = 'JBSWY3DPEHPK3PXP';
$code = '123456';

// Verify the code
$isValid = $totp->verifyCode($secret, $code);

echo $isValid ? "✅ Code is valid!\n" : "❌ Code is invalid!\n";
```

**Output:**

```
✅ Code is valid!
```

### **Generate a QR Code URI**

```php
use RemoteMerge\Totp\TotpFactory;

// Create a new TOTP instance
$totp = TotpFactory::create();

// Replace with your secret key and user information
$secret = 'JBSWY3DPEHPK3PXP';
$uri = $totp->generateUri($secret, 'user@example.com', 'YourApp');

echo "QR Code URI: $uri\n";
```

**Output:**

```
QR Code URI: otpauth://totp/YourApp:user@example.com?secret=JBSWY3DPEHPK3PXP&issuer=...
```

---

## **Customization Options**

### **Change the Hash Algorithm**

By default, TOTP PHP uses **SHA1**. You can switch to **SHA256** or **SHA512**:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

// Configure the algorithm
$totp->configure(['algorithm' => 'sha256']);

$secret = $totp->generateSecret();
$code = $totp->getCode($secret);

echo "Generated TOTP Code (SHA256): $code\n";
```

### **Change the Code Length**

By default, TOTP PHP generates **6-digit codes**. You can switch to **8-digit codes**:

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

By default, TOTP PHP uses a **30-second time slice**. You can switch to **60 seconds**:

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

$secret = 'JBSWY3DPEHPK3PXP';
$code = '123456';

// Allow discrepancy of 1 time slice
$isValid = $totp->verifyCode($secret, $code, 1);

echo $isValid ? "✅ Code is valid!\n" : "❌ Code is invalid!\n";
```

### **Generate a QR Code Image**

Use the QR code URI to generate a QR code image:

```php
use RemoteMerge\Totp\TotpFactory;

$totp = TotpFactory::create();

$secret = 'JBSWY3DPEHPK3PXP';
$uri = $totp->generateUri($secret, 'user@example.com', 'YourApp');

$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($uri);

echo "QR Code Image URL: $qrCodeUrl\n";
```

---

## **Why TOTP PHP?**

- **Secure by Design**: Built with security as the top priority, ensuring your users' data is always protected.
- **Easy to Integrate**: Simple API and clear documentation make integration effortless.
- **Flexible and Customizable**: Tailor TOTP PHP to fit your application's unique needs.
- **Trusted by Developers**: Used by developers worldwide to add secure 2FA to their applications.

---

## **Support and Contributions**

Have questions or need help? Open an issue on [GitHub](https://github.com/remotemerge/totp-php/issues). Contributions are welcome!

---

### **License**

Licensed under the [MIT License](LICENSE).

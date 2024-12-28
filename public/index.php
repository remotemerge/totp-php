<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOTP Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans">
<div class="container mx-auto p-6 max-w-md">
    <!-- Enable 2FA Section -->
    <section class="bg-white p-8 rounded-xl shadow-lg text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Account Security</h1>
        <p class="text-gray-600 mb-6">
            Secure your account by enabling 2FA. Scan the QR code with your authenticator app.
        </p>

        <!-- Enable 2FA Button -->
        <button id="enable2FA"
                class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-8 py-3 rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 transform hover:scale-105">
            Enable 2FA
        </button>

        <!-- QR Code Section (Hidden by Default) -->
        <div id="qrSection" class="mt-6 hidden">
            <div class="bg-white p-6 rounded-lg">
                <img id="qrImage" src="" alt="QR Code" class="mx-auto w-48 h-48"/>
                <p class="text-gray-600 mt-4 text-sm">
                    Scan this QR code with your authenticator app.
                </p>
            </div>

            <!-- Verification Box -->
            <div class="mt-6">
                <label for="verifyCodeInput" class="sr-only">Enter TOTP Code</label>
                <input
                    id="verifyCodeInput"
                    type="text"
                    placeholder="Enter TOTP Code"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button id="verifyCode"
                        class="mt-4 bg-gradient-to-r from-green-500 to-teal-500 text-white px-8 py-3 rounded-lg hover:from-green-600 hover:to-teal-600 transition-all duration-300 transform hover:scale-105">
                    Verify Code
                </button>
            </div>

            <!-- Verification Result -->
            <div id="verificationResult" class="mt-6 p-4 rounded-lg text-center text-lg font-semibold hidden"></div>
        </div>
    </section>
</div>
<script src="js/app.js"></script>
</body>
</html>

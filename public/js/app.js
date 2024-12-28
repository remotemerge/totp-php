// JavaScript to interact with the 2FA flow
let secretKey = '';

// Enable 2FA Button
document.getElementById('enable2FA').addEventListener('click', async () => {
  // Fetch the secret key and QR code URI from the backend
  const response = await fetch('/secret.php');
  const data = await response.json();
  secretKey = data.secret;

  // Generate QR code URI
  const qrUri = `otpauth://totp/Demo:user@example.com?secret=${secretKey}&issuer=Demo`;
  document.getElementById('qrImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrUri)}`;

  // Show the QR code section
  document.getElementById('qrSection').classList.remove('hidden');
});

// Verify TOTP Code
document.getElementById('verifyCode').addEventListener('click', async () => {
  const code = document.getElementById('verifyCodeInput').value;
  const response = await fetch('/verify.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({secret: secretKey, code}),
  });
  const data = await response.json();

  // Show verification result
  const resultDiv = document.getElementById('verificationResult');
  resultDiv.classList.remove('hidden');
  if (data.valid) {
    resultDiv.textContent = '✅ 2FA Enabled Successfully!';
    resultDiv.classList.remove('text-red-500');
    resultDiv.classList.add('text-green-500');
  } else {
    resultDiv.textContent = '❌ Invalid Code. Please try again.';
    resultDiv.classList.remove('text-green-500');
    resultDiv.classList.add('text-red-500');
  }
});

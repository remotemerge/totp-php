import QRCode from 'https://cdn.jsdelivr.net/npm/qrcode@1.5/+esm';

// JavaScript to interact with the 2FA flow
let secretKey = '';

const generateQRImage = async (text) => {
  document.getElementById('qrImage').src = await QRCode.toDataURL(text, {
    errorCorrectionLevel: 'H',
    width: 256,
    margin: 2,
  });
};

// Enable 2FA Button
document.getElementById('enable2FA').addEventListener('click', async () => {
  // Fetch the secret key and QR code URI from the backend
  const response = await fetch('/secret.php');
  const data = await response.json();
  secretKey = data.secret;

  // Generate QR code from backend URI
  await generateQRImage(data.uri);

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

<?php
// Dummy Midtrans config for public repo
// Replace with your real keys in production

function getMidtransServerKey() {
    return 'DUMMY_SERVER_KEY';
}

function getMidtransClientKey() {
    return 'DUMMY_CLIENT_KEY';
}

function getMidtransBaseUrl() {
    // Sandbox URL for testing
    return 'https://api.sandbox.midtrans.com/v2';
}

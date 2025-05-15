<?php
// Google OAuth Configuration
$google_client_id = '182212861245-o99o3lq1h0ru17hvaenhke1dru7btocs.apps.googleusercontent.com';  // Replace with your Google Client ID
$google_client_secret = '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';  // Replace with your Google Client Secret

// Make sure the redirect URL is EXACTLY the same as what's configured in Google Cloud Console
// No trailing slashes, case sensitive, etc.
$google_redirect_url = 'http://localhost/Quiz/login.php';  // URL to handle the OAuth callback

// Google OAuth API URLs - these are the official endpoints
$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
$google_token_url = 'https://oauth2.googleapis.com/token';
$google_userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

// Required scopes (permissions) - DO NOT encode these here
// These are the minimum required scopes for basic profile information
$google_scopes = ['email', 'profile'];
?> 
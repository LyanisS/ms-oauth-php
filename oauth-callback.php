<?php

if (empty($_GET['code'])) die('No code');

require_once('conf.inc.php');

$req = curl_init('https://login.microsoftonline.com/' . MS_TENANT_ID . '/oauth2/v2.0/token');
curl_setopt($req, CURLOPT_POST, 1);
curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => MS_CLIENT_ID,
    'scope' => MS_SCOPES,
    'code' => $_GET['code'],
    'redirect_uri' => MS_REDIRECT_URI,
    'grant_type' => 'authorization_code',
    'client_secret' => MS_CLIENT_SECRET
]));
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($req);
curl_close($req);

$res = json_decode($res, true);

if (empty($res['access_token'])) die('No access token');

$req = curl_init('https://graph.microsoft.com/v1.0/me');
curl_setopt($req, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $res['access_token']
]);
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($req);
curl_close($req);

$res = json_decode($res, true);

echo '<pre>';
echo var_dump($res);
echo '</pre>';

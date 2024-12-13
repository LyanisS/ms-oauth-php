<?php require_once('conf.inc.php'); ?>
<a href="https://login.microsoftonline.com/<?= MS_TENANT_ID ?>/oauth2/v2.0/authorize?client_id=<?= MS_CLIENT_ID ?>&response_type=code&redirect_uri=<?= urlencode(MS_REDIRECT_URI) ?>&scope=<?= urlencode(MS_SCOPES) ?>&domain_hint=<?= MS_DOMAIN_HINT ?>">Login with Microsoft</a>

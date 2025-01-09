<?php require_once(dirname(__FILE__) . '/inc/functions.php'); start(); ?>
<html lang="en-us">
    <head>
        <title>MS OAuth PHP</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="color-scheme" content="dark">
        <meta name="robots" content="noindex,nofollow" />
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-900 text-white p-3 sm:p-5 flex items-center justify-center">
        <?php
        $user = get_user_info();
        if ($user === false) include('inc/login-button.php');
        else if (isset($user['data'])) include('inc/user-attributes-table.php');
        else {
            echo '
            <div class="mx-auto max-w-screen-2xl px-4 lg:px-12">
                <div class="bg-gray-800 relative shadow-md overflow-hidden">
                    <div class="m-4">
                        <p>An error occured during authentication:</p><br>
                        <p class="bg-gray-900 px-5 py-2 mx-2">' . ($user['error'] ?? 'Unknown error') . '</p><br>
                        <p class="mb-4">Please try again:</p>';
            include('inc/login-button.php');
            echo '
                    </div>
                </div>
            </div>';
        } ?>
    </body>
</html>
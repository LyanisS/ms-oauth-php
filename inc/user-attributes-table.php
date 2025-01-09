<?php 
require_once(dirname(__FILE__) . '/functions.php');
prevent_direct_access();

if (isset($user) && isset($user['data'])): ?>
<div class="mx-auto bg-gray-800 shadow-md max-w-full max-h-full overflow-x-auto">
    <?= data_to_table($user['data']); ?>
</div>
<?php else: ?>
<p>Unknown error</p>
<?php endif; ?>
<script>window.history.pushState('','','/')</script>
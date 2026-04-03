<?php
// views/register.php

// Registration now uses the student Google login entrypoint.
if (!headers_sent()) {
    set_flash('info', 'Student accounts are created with Google sign-in. Trainer access is provisioned by the team.');
}
redirect('/student_login');
?>

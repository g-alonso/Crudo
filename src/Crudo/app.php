<?php

$app->mount('/login', new Crudo\Controllers\Login());
$app->mount('/dashboard', new Crudo\Controllers\Dashboard());
$app->mount('/user', new Crudo\Controllers\User());

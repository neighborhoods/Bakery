<?php
declare(strict_types=1);
error_reporting(E_ALL);

use Neighborhoods\Bakery\Baker;

require_once __DIR__ . '/../vendor/autoload.php';
$baker = new Baker();
$baker->bake();
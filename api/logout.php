<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_destroy();
sendJsonResponse(['success' => true]); 
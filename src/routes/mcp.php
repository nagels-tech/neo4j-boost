<?php

use Illuminate\Support\Facades\Route;
use NagelsTech\Neo4jBoost\Http\Controllers\McpController;

Route::post('/mcp', McpController::class);
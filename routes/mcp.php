<?php

use Illuminate\Support\Facades\Route;
use Neo4j\LaravelBoost\Http\Controllers\McpController;

Route::post('/mcp', McpController::class);
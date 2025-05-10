<?php
session_start();

class AuthMiddleware {
    public function __invoke($request, $next) {
        if (!isset($_SESSION['user'])) {
            header("Location: login/signIn.php");
            exit();
        }
        return $next($request);
    }
}

class LoggingMiddleware {
    public function __invoke($request, $next) {
        error_log("Visited by: " . ($_SESSION['user'] ?? 'Guest'));
        return $next($request);
    }
}

$actualHandler = function ($request) {
    include("overview.php"); // move all HTML here
};

// Apply middleware
$middlewares = [
    new LoggingMiddleware(),
    new AuthMiddleware(),
];

foreach (array_reverse($middlewares) as $middleware) {
    $next = $actualHandler;
    $actualHandler = fn($request) => $middleware($request, $next);
}

// Call the wrapped handler (this executes or redirects)
$actualHandler("overview");

?>

<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;


class Filters extends BaseFilters
{
    public array $aliases = [
        'cors' => \App\Filters\Cors::class,
        'csrf' => \CodeIgniter\Filters\CSRF::class,
        'toolbar' => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars' => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
        'forcehttps' => \CodeIgniter\Filters\ForceHTTPS::class,
        'pagecache' => \CodeIgniter\Filters\PageCache::class,
        'performance' => \CodeIgniter\Filters\PerformanceMetrics::class,
    ];

    public array $required = [
        'before' => [
            // 'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            'cors',
        // 'honeypot',
        // 'csrf',
        // 'invalidchars',
        ],
        'after' => [
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
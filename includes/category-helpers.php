<?php
/**
 * Category Helper Functions
 * Provides emoji icons and colors for categories
 */

function getCategoryEmoji(string $categoryName): string {
    return match(strtolower($categoryName)) {
        'books' => '📚',
        'electronics' => '💻',
        'furniture' => '🛋️',
        'dorm equipment' => '🛏️',
        default => '📦'
    };
}

function getCategoryColor(string $categoryName): string {
    return match(strtolower($categoryName)) {
        'books' => 'primary',
        'electronics' => 'info',
        'furniture' => 'warning',
        'dorm equipment' => 'success',
        default => 'secondary'
    };
}

function getCategoryGradient(string $categoryName): string {
    return match(strtolower($categoryName)) {
        'books' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'electronics' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'furniture' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'dorm equipment' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        default => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
    };
}


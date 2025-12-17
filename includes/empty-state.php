<?php
/**
 * Empty State Component
 * Beautiful "no results" messages
 */

function renderEmptyState(string $type, array $options = []): string {
    $emoji = $options['emoji'] ?? '📭';
    $title = $options['title'] ?? 'No items found';
    $message = $options['message'] ?? 'There are no items to display at the moment.';
    $actionText = $options['actionText'] ?? null;
    $actionUrl = $options['actionUrl'] ?? null;
    
    $html = '<div class="text-center py-5">';
    $html .= '<div style="font-size: 5rem; opacity: 0.5;">' . $emoji . '</div>';
    $html .= '<h4 class="mt-3 mb-2">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h4>';
    $html .= '<p class="text-muted mb-4">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    
    if ($actionText && $actionUrl) {
        $html .= '<a href="' . htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">';
        $html .= htmlspecialchars($actionText, ENT_QUOTES, 'UTF-8') . ' →</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// Predefined empty states
function emptyListings(): string {
    return renderEmptyState('listings', [
        'emoji' => '📦',
        'title' => 'No Listings Yet',
        'message' => 'Be the first to create a listing and start selling!',
        'actionText' => 'Create Listing',
        'actionUrl' => base_url('/pages/add-listing.php')
    ]);
}

function emptyMessages(): string {
    return renderEmptyState('messages', [
        'emoji' => '💬',
        'title' => 'No Messages',
        'message' => 'Start a conversation by contacting a seller!',
        'actionText' => 'Browse Listings',
        'actionUrl' => base_url('/pages/listings.php')
    ]);
}

function emptyBids(): string {
    return renderEmptyState('bids', [
        'emoji' => '💰',
        'title' => 'No Bids Yet',
        'message' => 'You haven\'t placed any bids yet. Browse listings to start bidding!',
        'actionText' => 'Browse Listings',
        'actionUrl' => base_url('/pages/listings.php')
    ]);
}

function emptyNotifications(): string {
    return renderEmptyState('notifications', [
        'emoji' => '🔔',
        'title' => 'All Caught Up!',
        'message' => 'You don\'t have any notifications right now.'
    ]);
}

function emptySavedItems(): string {
    return renderEmptyState('saved', [
        'emoji' => '⭐',
        'title' => 'No Saved Items',
        'message' => 'Save listings you\'re interested in to view them here later.',
        'actionText' => 'Browse Listings',
        'actionUrl' => base_url('/pages/listings.php')
    ]);
}

function emptyReviews(): string {
    return renderEmptyState('reviews', [
        'emoji' => '⭐',
        'title' => 'No Reviews Yet',
        'message' => 'This user hasn\'t received any reviews yet.'
    ]);
}

function emptySearchResults(string $query): string {
    return renderEmptyState('search', [
        'emoji' => '🔍',
        'title' => 'No Results Found',
        'message' => 'We couldn\'t find any listings matching "' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '". Try different keywords.',
        'actionText' => 'View All Listings',
        'actionUrl' => base_url('/pages/listings.php')
    ]);
}



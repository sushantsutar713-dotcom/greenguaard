<?php
/**
 * GreenGuard — Explore Environmental Reports & Threat Map
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ai_service.php';

$allReports = DB::all('reports');
$categories = AIService::CATEGORIES;

$pageTitle = 'Explore Incidents & Threat Map — GreenGuard';
$activeNav = 'explore';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container">
        <!-- Header -->
        <div class="section-header" style="text-align: left; max-width: 100%; margin-bottom: 2rem; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-tag">Crowdsourced Environmental Radar</span>
                <h1 class="section-title" style="margin-bottom: 0.35rem;">Explore Environmental Incidents</h1>
                <p class="section-subtitle">
                    Discover verified community reports, track nearby hotspots, and inspect live evidence.
                </p>
            </div>
            <a href="report.php" class="btn btn-primary btn-lg">
                <span>📸</span> Report New Threat
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="explore-filter-bar">
            <!-- Search Box -->
            <div style="flex-grow: 1; min-width: 240px;">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by keyword, location, or river..." style="padding: 0.6rem 1rem;">
            </div>

            <!-- Category Filter -->
            <div style="min-width: 180px;">
                <select id="categoryFilter" class="form-control" style="padding: 0.6rem 2rem 0.6rem 1rem;">
                    <option value="ALL">All Categories</option>
                    <?php foreach ($categories as $catKey => $catLabel): ?>
                        <option value="<?= $catKey ?>"><?= htmlspecialchars($catLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Severity Filter -->
            <div style="min-width: 140px;">
                <select id="severityFilter" class="form-control" style="padding: 0.6rem 2rem 0.6rem 1rem;">
                    <option value="ALL">All Severities</option>
                    <option value="CRITICAL">🔴 Critical</option>
                    <option value="HIGH">🟠 High</option>
                    <option value="MEDIUM">🟡 Medium</option>
                    <option value="LOW">🔵 Low</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div style="min-width: 150px;">
                <select id="statusFilter" class="form-control" style="padding: 0.6rem 2rem 0.6rem 1rem;">
                    <option value="ALL">All Statuses</option>
                    <option value="PENDING">Pending</option>
                    <option value="UNDER_REVIEW">Under Review</option>
                    <option value="VERIFIED">Verified</option>
                    <option value="ACTION_INITIATED">In Progress</option>
                    <option value="RESOLVED">Resolved</option>
                </select>
            </div>

            <!-- View Toggle: Map vs Grid -->
            <div class="view-toggle">
                <button type="button" id="viewToggleMap" class="view-toggle-btn active">
                    🗺️ Map View
                </button>
                <button type="button" id="viewToggleGrid" class="view-toggle-btn">
                    📋 Grid View
                </button>
            </div>
        </div>

        <!-- Incident Count Banner -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            <div>Showing <strong id="incidentCountText" style="color: var(--text-main);"><?= count($allReports) ?></strong> incidents</div>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.82rem;">
                <span>🔴 Critical</span>
                <span>🟠 High</span>
                <span>🟡 Medium</span>
                <span>🟢 Resolved</span>
            </div>
        </div>

        <!-- 1. Interactive Leaflet Map Container -->
        <div id="mapViewWrapper">
            <div id="exploreMap" class="explore-map-container"></div>
        </div>

        <!-- 2. Grid Cards Container -->
        <div id="gridViewWrapper" style="display: none;">
            <div id="reportsGrid" class="reports-preview-grid">
                <!-- Dynamically populated by js/map.js -->
            </div>
        </div>

        <!-- Empty State Container -->
        <div id="emptyReportsState" style="display: none; text-align: center; padding: 4rem 1rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-glass);">
            <div style="font-size: 3rem; margin-bottom: 0.75rem;">🔍</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">No environmental incidents match your criteria</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
                Try adjusting your search terms, changing the category, or clearing severity filters.
            </p>
            <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset All Filters</button>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script src="js/map.js"></script>';
require_once __DIR__ . '/includes/footer.php'; 
?>

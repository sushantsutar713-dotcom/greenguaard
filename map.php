<?php
/**
 * GreenGuard — Dedicated Full-Screen Interactive Threat Map
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Live Threat Map — GreenGuard';
$activeNav = 'explore';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="padding-top: 2rem;">
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">🗺️ Real-Time Environmental Threat Map</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">Interactive GIS view with severity pins, AI classifications, and municipal status.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="explore.php" class="btn btn-secondary btn-sm">📋 Switch to Explorer List</a>
                <a href="report.php" class="btn btn-primary btn-sm">📸 Report Threat</a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="explore-filter-bar" style="margin-bottom: 1.5rem;">
            <div style="flex-grow: 1; min-width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search threats by name or location...">
            </div>
            <div style="min-width: 170px;">
                <select id="categoryFilter" class="form-control">
                    <option value="ALL">All Categories</option>
                    <option value="ILLEGAL_DUMPING">Illegal Dumping</option>
                    <option value="TREE_LOSS">Tree Loss</option>
                    <option value="WATER_POLLUTION">Water Pollution</option>
                    <option value="WASTE_BURNING">Waste Burning</option>
                    <option value="INDUSTRIAL_POLLUTION">Industrial Pollution</option>
                    <option value="PLASTIC_POLLUTION">Plastic Pollution</option>
                </select>
            </div>
            <div style="min-width: 140px;">
                <select id="severityFilter" class="form-control">
                    <option value="ALL">All Severities</option>
                    <option value="CRITICAL">🔴 Critical</option>
                    <option value="HIGH">🟠 High</option>
                    <option value="MEDIUM">🟡 Medium</option>
                    <option value="LOW">🔵 Low</option>
                </select>
            </div>
            <div style="min-width: 140px;">
                <select id="statusFilter" class="form-control">
                    <option value="ALL">All Statuses</option>
                    <option value="PENDING">Pending</option>
                    <option value="VERIFIED">Verified</option>
                    <option value="ACTION_INITIATED">In Progress</option>
                    <option value="RESOLVED">Resolved</option>
                </select>
            </div>
        </div>

        <!-- Full Map Container -->
        <div id="exploreMap" class="explore-map-container" style="height: 600px;"></div>
    </div>
</div>

<?php 
$extraScripts = '<script src="js/map.js"></script>';
require_once __DIR__ . '/includes/footer.php'; 
?>

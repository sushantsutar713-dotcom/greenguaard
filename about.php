<?php
/**
 * GreenGuard — About Platform, Mission, Architecture & UN SDGs
 */

$pageTitle = 'About GreenGuard — Community Environmental Platform';
$activeNav = 'about';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container" style="max-width: 960px;">
        
        <!-- Header -->
        <div class="section-header" style="text-align: center; margin-bottom: 3.5rem;">
            <span class="section-tag">Empowering Environmental Guardians</span>
            <h1 class="section-title">Protect Nature. Report Threats. Create Change.</h1>
            <p class="section-subtitle">
                GreenGuard combines citizen surveillance, Google Gemini AI computer vision, and real-time municipal accountability to safeguard local ecosystems.
            </p>
        </div>

        <!-- Problem & Solution Grid -->
        <div class="charts-grid" style="margin-bottom: 3rem;">
            <div class="chart-card" style="border-left: 4px solid var(--status-critical);">
                <div style="font-size: 2rem; margin-bottom: 0.75rem;">⚠️</div>
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-main);">
                    The Problem: Delayed Intervention
                </h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7;">
                    Environmental threats such as midnight chemical effluent dumping, unauthorized urban tree felling, and toxic open waste burning frequently go unnoticed by municipal enforcement agencies until severe ecological and public health damage has occurred. Citizens lack a transparent, geotagged reporting channel with tracked accountability.
                </p>
            </div>

            <div class="chart-card" style="border-left: 4px solid var(--primary);">
                <div style="font-size: 2rem; margin-bottom: 0.75rem;">🌱</div>
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-main);">
                    The GreenGuard Solution
                </h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7;">
                    GreenGuard turns millions of smartphone-equipped citizens into active environmental sentinels. With 1-click GPS location capture, automatic Google Gemini AI computer vision threat classification, and an urgency priority engine, field task forces can triage and neutralize hazards before permanent damage spreads.
                </p>
            </div>
        </div>

        <!-- 4-Step Protection Cycle -->
        <div class="details-card" style="margin-bottom: 3rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <span class="section-tag">End-to-End Operational Lifecycle</span>
                <h2 style="font-size: 1.6rem; font-weight: 800;">How GreenGuard Operates</h2>
            </div>

            <div class="workflow-grid">
                <div class="workflow-card">
                    <span class="step-badge">STEP 01</span>
                    <div class="workflow-icon">📸</div>
                    <h3 class="workflow-title">Citizen Capture</h3>
                    <p class="workflow-text">Citizens snap photographic evidence and capture GPS coordinates using our responsive mobile web app.</p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 02</span>
                    <div class="workflow-icon">🤖</div>
                    <h3 class="workflow-title">AI Threat Vision</h3>
                    <p class="workflow-text">Google Gemini AI classifies threat taxonomy, estimates severity, and suggests remedial protocols.</p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 03</span>
                    <div class="workflow-icon">👥</div>
                    <h3 class="workflow-title">Crowd Validation</h3>
                    <p class="workflow-text">Nearby citizens upvote and confirm incidents, dynamically elevating urgency scores for rapid municipal triage.</p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 04</span>
                    <div class="workflow-icon">🚜</div>
                    <h3 class="workflow-title">Municipal Action</h3>
                    <p class="workflow-text">Authorities deploy remediation equipment, update live status timelines, and notify citizens when resolved.</p>
                </div>
            </div>
        </div>

        <!-- Community Role vs Authority Role Comparison -->
        <div class="charts-grid" style="margin-bottom: 3rem;">
            <div class="chart-card">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div class="user-avatar-circle" style="width: 32px; height: 32px;">👥</div>
                    <h3 class="chart-title" style="margin: 0;">Community Guardian Role</h3>
                </div>
                <ul style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.8; padding-left: 1.25rem;">
                    <li>Report pollution, illegal dumping, tree felling, and toxic fires.</li>
                    <li>Provide photographic evidence with precise GPS coordinates.</li>
                    <li>Upvote and corroborate threats reported by neighboring citizens.</li>
                    <li>Earn Eco-Guardian impact points and badges.</li>
                    <li>Track real-time municipal remediation progress.</li>
                </ul>
            </div>

            <div class="chart-card">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div class="user-avatar-circle" style="width: 32px; height: 32px; background: linear-gradient(135deg, #ef4444, #dc2626);">🛡️</div>
                    <h3 class="chart-title" style="margin: 0;">Municipal Authority Role</h3>
                </div>
                <ul style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.8; padding-left: 1.25rem;">
                    <li>Triage incoming threats using AI urgency priority scoring.</li>
                    <li>Assign incidents to specialized departments (Solid Waste, Tree Authority, Pollution Board).</li>
                    <li>Deploy on-site inspection teams and earthmovers.</li>
                    <li>Log official resolution notes, vehicle details, and penalty fines.</li>
                    <li>Transition report status to keep citizens informed in real-time.</li>
                </ul>
            </div>
        </div>

        <!-- UN Sustainable Development Goals (SDG) Alignment -->
        <div class="details-card" style="margin-bottom: 3rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(14, 165, 233, 0.05));">
            <div style="text-align: center; margin-bottom: 2rem;">
                <span class="section-tag">Global Ecological Framework</span>
                <h2 style="font-size: 1.6rem; font-weight: 800;">Aligned with United Nations SDGs</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">GreenGuard directly advances four UN Sustainable Development Goals:</p>
            </div>

            <div class="dashboard-metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="metric-card" style="flex-direction: column; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏙️</div>
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">SDG 11</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Sustainable Cities &amp; Human Settlements</div>
                </div>

                <div class="metric-card" style="flex-direction: column; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌍</div>
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">SDG 13</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Climate Action &amp; Carbon Sink Protection</div>
                </div>

                <div class="metric-card" style="flex-direction: column; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🐟</div>
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">SDG 14</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Life Below Water &amp; River Cleanliness</div>
                </div>

                <div class="metric-card" style="flex-direction: column; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌳</div>
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">SDG 15</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Life on Land &amp; Forest Canopy Defense</div>
                </div>
            </div>
        </div>

        <!-- Tech Architecture Overview -->
        <div class="details-card">
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem;">
                🛠️ Engineering &amp; Technology Stack
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; font-size: 0.9rem;">
                <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <strong style="color: var(--primary); display: block; margin-bottom: 0.25rem;">Frontend Layer</strong>
                    HTML5, Vanilla CSS3 (Custom Emerald Design System), Vanilla JS (No build steps required).
                </div>
                <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <strong style="color: var(--secondary); display: block; margin-bottom: 0.25rem;">Backend Engine</strong>
                    PHP 8.2 with RESTful JSON API endpoints, Session management &amp; Bcrypt cryptography.
                </div>
                <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <strong style="color: #38bdf8; display: block; margin-bottom: 0.25rem;">AI Computer Vision</strong>
                    Google Gemini 1.5 Flash Vision API with intelligent heuristic offline fallback.
                </div>
                <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <strong style="color: #fde047; display: block; margin-bottom: 0.25rem;">GIS &amp; Visual Analytics</strong>
                    Leaflet.js, OpenStreetMap tiles, and Chart.js 4.4 data visualizations.
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * GreenGuard — Report Environmental Issue Portal
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ai_service.php';

$currentUser = Auth::user();
$categories = AIService::CATEGORIES;

$pageTitle = 'Report Environmental Threat — GreenGuard';
$activeNav = 'report';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container" style="max-width: 900px;">
        <div class="section-header" style="margin-bottom: 2rem;">
            <span class="section-tag">Incident Reporting Portal</span>
            <h1 class="section-title">Report an Environmental Threat</h1>
            <p class="section-subtitle">
                Submit geotagged photo evidence. Our AI vision system classifies the incident and routes it directly to responsible municipal departments.
            </p>
        </div>

        <div class="form-card">
            <form id="reportForm" action="api/submit_report.php" method="POST" enctype="multipart/form-data">
                
                <!-- 1. Evidence Photo Upload & AI Vision Section -->
                <div class="form-group">
                    <label class="form-label">
                        <span>📸 Upload Evidence Photo</span> <span class="required">*</span>
                    </label>
                    
                    <div class="dropzone" id="photoDropzone">
                        <span class="dropzone-icon">📁</span>
                        <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 0.25rem;">
                            Drag &amp; drop evidence image here, or <span style="color: var(--primary); text-decoration: underline;">browse file</span>
                        </div>
                        <p class="form-hint">Supports JPG, PNG, WEBP, SVG up to 10MB</p>
                        <input type="file" id="evidencePhotoInput" name="evidence_photo" accept="image/*" style="display: none;">
                    </div>

                    <!-- Live Image Preview Container -->
                    <div id="imagePreviewContainer" class="dropzone-preview-container">
                        <img id="imagePreview" class="dropzone-preview-img" src="" alt="Evidence Preview">
                        <button type="button" id="removePhotoBtn" class="btn btn-danger btn-sm" style="position: absolute; top: 12px; right: 12px;">
                            ✕ Change Photo
                        </button>
                    </div>

                    <!-- Quick Sample Photo Selector for Fast Demo -->
                    <div style="margin-top: 0.85rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">⚡ Quick Demo Evidence:</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectSampleImage('uploads/sample_dumping.svg', 'Illegal Dumping')">🗑️ Dumping</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectSampleImage('uploads/sample_tree.svg', 'Tree Cutting')">🌳 Tree Loss</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectSampleImage('uploads/sample_pollution.svg', 'Toxic Effluent')">🧪 Effluent</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectSampleImage('uploads/sample_burning.svg', 'Waste Burning')">🔥 Waste Fire</button>
                        <input type="hidden" id="selectedSampleImage" name="selected_sample_image" value="">
                    </div>

                    <!-- AI Analysis Box (Dynamically shown) -->
                    <div id="aiSuggestionBox" class="ai-suggestion-box">
                        <div class="ai-header">
                            <div class="ai-badge">
                                <span>🤖</span> <span id="aiSourceText">Google Gemini AI Analysis</span>
                            </div>
                            <button type="button" id="applyAiBtn" class="btn btn-primary btn-sm">
                                🪄 Apply AI Classification
                            </button>
                        </div>
                        
                        <div id="aiSummaryText" style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--text-main);">
                            Analyzing image...
                        </div>

                        <div class="ai-meta-grid">
                            <div class="ai-meta-item">
                                <span class="label">Suggested Category</span>
                                <strong id="aiCategoryVal" style="color: var(--primary);">—</strong>
                            </div>
                            <div class="ai-meta-item">
                                <span class="label">Severity Level</span>
                                <strong id="aiSeverityVal" style="color: var(--status-critical);">—</strong>
                            </div>
                            <div class="ai-meta-item">
                                <span class="label">Confidence Rating</span>
                                <strong id="aiConfidenceVal" style="color: #38bdf8;">—</strong>
                            </div>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            <strong>Ecological Impact:</strong> <span id="aiImpactVal">—</span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <strong>Recommended Action:</strong> <span id="aiActionVal">—</span>
                        </div>

                        <!-- Hidden AI values for submission -->
                        <input type="hidden" id="aiCategoryHidden" name="ai_category" value="">
                        <input type="hidden" id="aiSeverityHidden" name="ai_severity" value="">
                        <input type="hidden" id="aiConfidenceHidden" name="ai_confidence" value="">
                        <input type="hidden" id="aiDescriptionHidden" name="ai_description" value="">
                        <input type="hidden" id="aiImpactHidden" name="ai_impact" value="">
                        <input type="hidden" id="aiRecommendationHidden" name="ai_recommendation" value="">
                        <input type="hidden" id="aiSourceHidden" name="ai_source" value="Google Gemini 1.5 Flash Vision">
                    </div>
                </div>

                <!-- 2. Threat Details Section -->
                <div class="form-group">
                    <label class="form-label" for="reportTitle">
                        <span>Incident Title</span> <span class="required">*</span>
                    </label>
                    <input type="text" id="reportTitle" name="title" class="form-control" placeholder="e.g. Commercial Debris & Chemical Drums Dumped Beside Riverbank" required minlength="4">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="reportCategory">
                            <span>Threat Category</span> <span class="required">*</span>
                        </label>
                        <select id="reportCategory" name="category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $catKey => $catLabel): ?>
                                <option value="<?= $catKey ?>"><?= htmlspecialchars($catLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reportSeverity">
                            <span>Severity Assessment</span> <span class="required">*</span>
                        </label>
                        <select id="reportSeverity" name="severity" class="form-control" required>
                            <option value="LOW">🔵 Low — Minor issue / Non-hazardous</option>
                            <option value="MEDIUM" selected>🟡 Medium — Moderate localized impact</option>
                            <option value="HIGH">🟠 High — Significant hazard / Spreading</option>
                            <option value="CRITICAL">🔴 Critical — Immediate ecological threat</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reportDescription">
                        <span>Description &amp; Observations</span> <span class="required">*</span>
                    </label>
                    <textarea id="reportDescription" name="description" class="form-control" placeholder="Describe what you observed, visible environmental hazards, estimated scale, nearby water bodies, or repeat violation history..." required></textarea>
                </div>

                <!-- 3. Geolocation & Interactive Map Picker -->
                <div class="form-group">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <label class="form-label" style="margin-bottom: 0;">
                            <span>📍 Incident Location</span> <span class="required">*</span>
                        </label>
                        <button type="button" id="getCurrentLocationBtn" class="btn btn-outline-primary btn-sm">
                            📍 Use My GPS Location
                        </button>
                    </div>

                    <input type="text" id="reportAddress" name="address" class="form-control" placeholder="e.g. Mithi River Corridor, Bandra Kurla Complex, Mumbai" required>
                    <p class="form-hint">Click on the interactive map below or drag the pin to pinpoint the exact incident coordinates:</p>

                    <div id="pickerMap" class="map-picker-container"></div>

                    <div class="form-row" style="margin-top: 0.75rem;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Latitude</span>
                            <input type="text" id="reportLat" name="latitude" class="form-control" value="19.0760" readonly style="background: rgba(0,0,0,0.3); font-family: monospace;">
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Longitude</span>
                            <input type="text" id="reportLon" name="longitude" class="form-control" value="72.8777" readonly style="background: rgba(0,0,0,0.3); font-family: monospace;">
                        </div>
                    </div>
                </div>

                <!-- 4. Optional Contact Info -->
                <div class="form-group">
                    <label class="form-label" for="contactPhone">
                        <span>Contact Number (Optional)</span>
                    </label>
                    <input type="tel" id="contactPhone" name="contact_phone" class="form-control" placeholder="+91 98000 00000" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                    <p class="form-hint">Used strictly by municipal inspection teams if location clarification is required.</p>
                </div>

                <!-- Submit Action -->
                <div style="margin-top: 2rem;">
                    <button type="submit" id="submitReportBtn" class="btn btn-primary btn-block btn-lg">
                        🚀 Submit Environmental Threat Report →
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script src="js/report.js"></script>';
require_once __DIR__ . '/includes/footer.php'; 
?>

/**
 * GreenGuard — Threat Map & Explorer Module
 */

let exploreMap = null;
let markersLayer = null;
let currentReports = [];

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    initFilters();
    initViewToggle();
    fetchAndRenderReports();
});

/**
 * Initialize Leaflet Map
 */
function initMap() {
    const mapEl = document.getElementById('exploreMap');
    if (!mapEl) return;

    exploreMap = L.map('exploreMap').setView([19.1136, 72.8697], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors | GreenGuard Platform',
        maxZoom: 19
    }).addTo(exploreMap);

    markersLayer = L.layerGroup().addTo(exploreMap);
}

/**
 * Fetch Filtered Reports from API
 */
async function fetchAndRenderReports() {
    const category = document.getElementById('categoryFilter')?.value || 'ALL';
    const severity = document.getElementById('severityFilter')?.value || 'ALL';
    const status = document.getElementById('statusFilter')?.value || 'ALL';
    const search = document.getElementById('searchInput')?.value || '';

    const query = new URLSearchParams({ category, severity, status, search }).toString();

    try {
        const res = await fetch(`api/get_reports.php?${query}`);
        const data = await res.json();

        if (data.success) {
            currentReports = data.reports || [];
            updateUI(currentReports);
        }
    } catch (e) {
        console.error('Error fetching reports:', e);
    }
}

/**
 * Update Map Markers and Grid Cards
 */
function updateUI(reports) {
    // Update count text
    const countEl = document.getElementById('incidentCountText');
    if (countEl) countEl.innerText = reports.length;

    // Empty state check
    const emptyState = document.getElementById('emptyReportsState');
    const mapWrapper = document.getElementById('mapViewWrapper');
    const gridWrapper = document.getElementById('gridViewWrapper');

    if (reports.length === 0) {
        if (emptyState) emptyState.style.display = 'block';
        if (mapWrapper) mapWrapper.style.opacity = '0.5';
        if (gridWrapper) gridWrapper.style.display = 'none';
        if (markersLayer) markersLayer.clearLayers();
        return;
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (mapWrapper) mapWrapper.style.opacity = '1';
    }

    // Render Markers on Map
    if (markersLayer && exploreMap) {
        markersLayer.clearLayers();
        const latLngs = [];

        reports.forEach(r => {
            if (!r.latitude || !r.longitude) return;

            const lat = parseFloat(r.latitude);
            const lng = parseFloat(r.longitude);
            latLngs.push([lat, lng]);

            const status = (r.status || 'PENDING').toUpperCase();
            const sev = (r.severity || 'MEDIUM').toUpperCase();

            // Determine pin color
            let pinColor = '#f97316'; // High
            if (status === 'RESOLVED') {
                pinColor = '#10b981'; // Green
            } else if (sev === 'CRITICAL') {
                pinColor = '#ef4444'; // Red
            } else if (sev === 'MEDIUM') {
                pinColor = '#eab308'; // Yellow
            } else if (sev === 'LOW') {
                pinColor = '#3b82f6'; // Blue
            }

            // Category Icon
            let iconSymbol = '📍';
            const cat = (r.category || r.issue_type || '').toUpperCase();
            if (cat.includes('TREE')) iconSymbol = '🌳';
            else if (cat.includes('WATER')) iconSymbol = '🧪';
            else if (cat.includes('BURNING')) iconSymbol = '🔥';
            else if (cat.includes('DUMPING')) iconSymbol = '🗑️';
            else if (cat.includes('PLASTIC')) iconSymbol = '🧴';
            else if (cat.includes('INDUSTRIAL')) iconSymbol = '🏭';

            const customMarkerIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background: ${pinColor}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 0 14px ${pinColor}99; display: flex; align-items: center; justify-content: center; color: white; font-size: 15px; cursor: pointer; transition: transform 0.2s ease;">${iconSymbol}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            const marker = L.marker([lat, lng], { icon: customMarkerIcon });

            // Popup HTML
            const popupContent = `
                <div style="font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; max-width: 240px; padding: 4px;">
                    ${r.image_path ? `<img src="${escapeHtml(r.image_path)}" style="width: 100%; height: 110px; object-fit: cover; border-radius: 6px; margin-bottom: 6px;">` : ''}
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">${escapeHtml(r.category || 'Threat')}</div>
                    <div style="font-size: 14px; font-weight: 800; line-height: 1.25; margin-bottom: 4px;">${escapeHtml(r.title || 'Environmental Incident')}</div>
                    <div style="font-size: 12px; color: #475569; margin-bottom: 8px;">📍 ${escapeHtml((r.address || '').split(',')[0])}</div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; background: ${pinColor}22; color: ${pinColor}; padding: 2px 6px; border-radius: 4px;">${sev}</span>
                        <span style="font-size: 11px; font-weight: 600; color: #0f172a;">⚡ Score: ${r.priority_score || 50}</span>
                    </div>
                    <a href="report_details.php?id=${r.report_id}" style="display: block; background: #10b981; color: white; text-align: center; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 700;">
                        Inspect Report →
                    </a>
                </div>
            `;

            marker.bindPopup(popupContent);
            markersLayer.addLayer(marker);
        });

        // Fit map bounds if markers exist
        if (latLngs.length > 0) {
            exploreMap.fitBounds(latLngs, { padding: [40, 40], maxZoom: 14 });
        }
    }

    // Render Grid Cards
    const gridContainer = document.getElementById('reportsGrid');
    if (gridContainer) {
        gridContainer.innerHTML = '';
        reports.forEach(r => {
            const sev = (r.severity || 'MEDIUM').toUpperCase();
            const sevClass = 'badge-' + sev.toLowerCase();
            const status = (r.status || 'PENDING').toUpperCase();
            const statusClass = status === 'RESOLVED' ? 'badge-resolved' : (status === 'VERIFIED' ? 'badge-verified' : (status === 'ACTION_INITIATED' ? 'badge-progress' : 'badge-pending'));

            const card = document.createElement('div');
            card.className = 'report-card';
            card.innerHTML = `
                <div class="report-card-header">
                    <span class="badge ${sevClass}">${sev} SEVERITY</span>
                    <span class="badge ${statusClass}">${escapeHtml(status.replace('_', ' '))}</span>
                </div>
                ${r.image_path ? `<img src="${escapeHtml(r.image_path)}" alt="Evidence" style="height: 180px; width: 100%; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid var(--border-glass);">` : ''}
                <h3 class="report-title">${escapeHtml(r.title || 'Environmental Incident')}</h3>
                <p class="report-desc">${escapeHtml((r.description || '').substring(0, 110))}...</p>
                <div class="report-meta">
                    <span class="meta-item">📍 ${escapeHtml((r.address || 'Unknown').split(',')[0])}</span>
                    <span class="meta-item">👥 ${(r.community && r.community.confirmations) || 0} confirms</span>
                </div>
                <div style="margin-top: 1rem;">
                    <a href="report_details.php?id=${r.report_id}" class="btn btn-outline-primary btn-sm btn-block">
                        Inspect Report &amp; Timeline →
                    </a>
                </div>
            `;
            gridContainer.appendChild(card);
        });
    }
}

/**
 * Filter Event Listeners
 */
function initFilters() {
    const categorySelect = document.getElementById('categoryFilter');
    const severitySelect = document.getElementById('severityFilter');
    const statusSelect = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');

    let debounceTimer = null;

    if (categorySelect) categorySelect.addEventListener('change', fetchAndRenderReports);
    if (severitySelect) severitySelect.addEventListener('change', fetchAndRenderReports);
    if (statusSelect) statusSelect.addEventListener('change', fetchAndRenderReports);

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchAndRenderReports, 300);
        });
    }
}

/**
 * View Toggle (Map View vs Grid Cards View)
 */
function initViewToggle() {
    const mapBtn = document.getElementById('viewToggleMap');
    const gridBtn = document.getElementById('viewToggleGrid');
    const mapWrapper = document.getElementById('mapViewWrapper');
    const gridWrapper = document.getElementById('gridViewWrapper');

    if (!mapBtn || !gridBtn) return;

    mapBtn.addEventListener('click', () => {
        mapBtn.classList.add('active');
        gridBtn.classList.remove('active');
        if (mapWrapper) mapWrapper.style.display = 'block';
        if (gridWrapper) gridWrapper.style.display = 'none';
        if (exploreMap) exploreMap.invalidateSize();
    });

    gridBtn.addEventListener('click', () => {
        gridBtn.classList.add('active');
        mapBtn.classList.remove('active');
        if (mapWrapper) mapWrapper.style.display = 'none';
        if (gridWrapper) gridWrapper.style.display = 'block';
    });
}

function resetFilters() {
    if (document.getElementById('categoryFilter')) document.getElementById('categoryFilter').value = 'ALL';
    if (document.getElementById('severityFilter')) document.getElementById('severityFilter').value = 'ALL';
    if (document.getElementById('statusFilter')) document.getElementById('statusFilter').value = 'ALL';
    if (document.getElementById('searchInput')) document.getElementById('searchInput').value = '';
    fetchAndRenderReports();
}

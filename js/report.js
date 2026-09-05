/**
 * GreenGuard — Incident Reporting & AI Preview Module
 */

document.addEventListener('DOMContentLoaded', () => {
    initLocationPicker();
    initImageUploadAndAI();
});

let pickerMap = null;
let pickerMarker = null;

/**
 * Initialize Interactive Leaflet Location Picker
 */
function initLocationPicker() {
    const mapEl = document.getElementById('pickerMap');
    if (!mapEl) return;

    let defaultLat = parseFloat(document.getElementById('reportLat').value) || 19.0760;
    let defaultLon = parseFloat(document.getElementById('reportLon').value) || 72.8777;

    pickerMap = L.map('pickerMap').setView([defaultLat, defaultLon], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(pickerMap);

    // Custom Nature Pin Icon
    const customIcon = L.divIcon({
        className: 'custom-map-pin',
        html: `<div style="background: #ef4444; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 0 12px rgba(239, 68, 68, 0.8); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">📍</div>`,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    pickerMarker = L.marker([defaultLat, defaultLon], {
        draggable: true,
        icon: customIcon
    }).addTo(pickerMap);

    // Update on drag
    pickerMarker.on('dragend', (e) => {
        const pos = e.target.getLatLng();
        updateCoordinates(pos.lat, pos.lon || pos.lng);
        reverseGeocode(pos.lat, pos.lon || pos.lng);
    });

    // Update on map click
    pickerMap.on('click', (e) => {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        pickerMarker.setLatLng([lat, lng]);
        updateCoordinates(lat, lng);
        reverseGeocode(lat, lng);
    });

    // Geolocation button
    const geoBtn = document.getElementById('getCurrentLocationBtn');
    if (geoBtn) {
        geoBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                showToast('Geolocation is not supported by your browser.', 'error');
                return;
            }

            geoBtn.innerHTML = '⌛ Locating...';
            geoBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    pickerMap.setView([lat, lng], 15);
                    pickerMarker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                    reverseGeocode(lat, lng);
                    geoBtn.innerHTML = '📍 Use My GPS Location';
                    geoBtn.disabled = false;
                    showToast('Location captured with GPS precision!', 'success');
                },
                (err) => {
                    geoBtn.innerHTML = '📍 Use My GPS Location';
                    geoBtn.disabled = false;
                    showToast('Could not fetch GPS location: ' + err.message, 'error');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    }
}

function updateCoordinates(lat, lng) {
    document.getElementById('reportLat').value = lat.toFixed(5);
    document.getElementById('reportLon').value = lng.toFixed(5);
}

/**
 * Reverse Geocode via OpenStreetMap Nominatim
 */
async function reverseGeocode(lat, lng) {
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        if (res.ok) {
            const data = await res.json();
            if (data && data.display_name) {
                const addrInput = document.getElementById('reportAddress');
                if (addrInput) {
                    addrInput.value = data.display_name;
                }
            }
        }
    } catch (e) {
        console.warn('Geocoding fallback', e);
    }
}

/**
 * Image Upload, Drag & Drop, and AI Analysis
 */
function initImageUploadAndAI() {
    const dropzone = document.getElementById('photoDropzone');
    const fileInput = document.getElementById('evidencePhotoInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removePhotoBtn');

    if (!dropzone || !fileInput) return;

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files[0]) {
            handleFileSelect(e.target.files[0]);
        }
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            fileInput.value = '';
            document.getElementById('selectedSampleImage').value = '';
            previewContainer.style.display = 'none';
            dropzone.style.display = 'block';
            document.getElementById('aiSuggestionBox').style.display = 'none';
        });
    }
}

/**
 * Handle Selected File and Trigger AI Analysis
 */
function handleFileSelect(file) {
    const dropzone = document.getElementById('photoDropzone');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');

    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src = e.target.result;
        dropzone.style.display = 'none';
        previewContainer.style.display = 'block';
        
        // Trigger AI Analysis
        analyzeEvidenceWithAI(file);
    };
    reader.readAsDataURL(file);
}

/**
 * Select Quick Sample Image for Fast Hackathon Demo
 */
function selectSampleImage(imagePath, defaultTitle) {
    const dropzone = document.getElementById('photoDropzone');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const sampleInput = document.getElementById('selectedSampleImage');

    sampleInput.value = imagePath;
    previewImg.src = imagePath;
    dropzone.style.display = 'none';
    previewContainer.style.display = 'block';

    const titleInput = document.getElementById('reportTitle');
    if (titleInput && !titleInput.value) {
        titleInput.value = defaultTitle + ' observed in local vicinity';
    }

    // Call AI API for this sample image
    analyzeSampleWithAI(imagePath);
}

/**
 * Call Server-Side AI Vision API for Uploaded File
 */
async function analyzeEvidenceWithAI(file) {
    showAILoadingState();
    const formData = new FormData();
    formData.append('evidence_photo', file);
    formData.append('description', document.getElementById('reportDescription').value || document.getElementById('reportTitle').value);

    try {
        const res = await fetch('api/analyze_image.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success && data.ai) {
            renderAISuggestion(data.ai);
        } else {
            hideAILoadingState();
        }
    } catch (err) {
        console.error('AI analysis error:', err);
        hideAILoadingState();
    }
}

/**
 * Call Server-Side AI Vision API for Sample Path
 */
async function analyzeSampleWithAI(imagePath) {
    showAILoadingState();
    const formData = new FormData();
    formData.append('image_path', imagePath);
    formData.append('description', document.getElementById('reportDescription').value || document.getElementById('reportTitle').value);

    try {
        const res = await fetch('api/analyze_image.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success && data.ai) {
            renderAISuggestion(data.ai);
        } else {
            hideAILoadingState();
        }
    } catch (err) {
        console.error('AI sample analysis error:', err);
        hideAILoadingState();
    }
}

function showAILoadingState() {
    const aiBox = document.getElementById('aiSuggestionBox');
    aiBox.style.display = 'block';
    document.getElementById('aiSummaryText').innerHTML = '⏳ <em>GreenGuard AI is analyzing evidence photo pixels and ecological metadata...</em>';
}

function hideAILoadingState() {
    const aiBox = document.getElementById('aiSuggestionBox');
    aiBox.style.display = 'none';
}

/**
 * Render AI Suggestion Card and Setup 1-Click Auto-Fill
 */
function renderAISuggestion(ai) {
    const aiBox = document.getElementById('aiSuggestionBox');
    aiBox.style.display = 'block';

    document.getElementById('aiSourceText').innerText = ai.source || 'Google Gemini AI Analysis';
    document.getElementById('aiSummaryText').innerText = ai.description || 'Threat classification complete.';
    document.getElementById('aiCategoryVal').innerText = ai.category_label || ai.category || 'Environmental Threat';
    document.getElementById('aiSeverityVal').innerText = ai.suggested_severity || 'HIGH';
    document.getElementById('aiConfidenceVal').innerText = (ai.confidence || 94) + '%';
    document.getElementById('aiImpactVal').innerText = ai.environmental_impact || 'Moderate ecological disruption';
    document.getElementById('aiActionVal').innerText = ai.recommended_action || 'Municipal inspection recommended';

    // Store hidden payload
    document.getElementById('aiCategoryHidden').value = ai.category;
    document.getElementById('aiSeverityHidden').value = ai.suggested_severity;
    document.getElementById('aiConfidenceHidden').value = ai.confidence;
    document.getElementById('aiDescriptionHidden').value = ai.description;
    document.getElementById('aiImpactHidden').value = ai.environmental_impact;
    document.getElementById('aiRecommendationHidden').value = ai.recommended_action;
    document.getElementById('aiSourceHidden').value = ai.source;

    // 1-Click Apply AI Suggestions button
    const applyBtn = document.getElementById('applyAiBtn');
    applyBtn.onclick = () => {
        if (ai.category) {
            document.getElementById('reportCategory').value = ai.category;
        }
        if (ai.suggested_severity) {
            document.getElementById('reportSeverity').value = ai.suggested_severity;
        }
        const descInput = document.getElementById('reportDescription');
        if (descInput && (!descInput.value || descInput.value.length < 15)) {
            descInput.value = (ai.description ? ai.description + ' ' : '') + 
                (ai.environmental_impact ? 'Ecological impact: ' + ai.environmental_impact : '');
        }
        showToast('AI classification and severity applied to report!', 'success');
    };
}

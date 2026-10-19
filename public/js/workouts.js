/* global window, document, console, fetch, alert, setTimeout, location */
'use strict';

/**
 * File: workouts.js
 * Location: /public/js/workouts.js
 *
 * Purpose:
 * Handles all workouts page interactions, form submissions, and chart rendering.
 * Demonstrates MPA architecture with full CRUD operations.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Set all date inputs to today's date by default
    document.querySelectorAll('input[type="date"]').forEach(function (input) {
        if (!input.value) {
            input.valueAsDate = new Date();
        }
    });

    // Populate the initial exercise library based on user profile gender
    populateExerciseLibrary(window.currentGender || 'prefer_not_to_say');

    // Render default charts (Week view) on load
    switchChart('frequency', 'week');
    switchChart('volume', 'week');
});

/**
 * Chart Range Switcher
 * Uses Bar Chart for Week/Month, and a distinct Area Chart for Year.
 * FIXED: No longer relies on 'event.target' so it works on auto-load.
 */
window.switchChart = function (chartType, range) {
    const container = document.getElementById(chartType === 'frequency' ? 'frequencyChart' : 'volumeChart');
    const data = window.workoutChartData[chartType][range];
    
    // Update active button state automatically
    const buttons = container.parentElement.querySelectorAll('.chart-controls button');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick').includes("'" + range + "'")) {
            btn.classList.add('active');
        }
    });

    // Use Area Chart for 'year', Bar Chart for 'week'/'month'
    if (range === 'year') {
        renderAreaChart(container, data, chartType === 'frequency' ? 'count' : 'minutes', chartType + 'Tooltip');
    } else {
        renderBarChart(container, data, chartType === 'frequency' ? 'count' : 'minutes', chartType + 'Tooltip');
    }
};

/**
 * Bar Chart Renderer (for Week/Month)
 */
function renderBarChart(container, data, valueKey, tooltipId) {
    const width = 600;
    const height = 200;
    const padding = { top: 20, right: 20, bottom: 30, left: 40 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;
    
    const values = data.map(d => d[valueKey]);
    const maxVal = Math.max(...values, 1);
    const barWidth = (chartW / data.length) * 0.6;
    const spacing = (chartW / data.length) * 0.4;

    let svgContent = `<svg viewBox="0 0 ${width} ${height}" style="width: 100%; height: 100%;">`;

    // Grid lines
    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (i * chartH / 4);
        const val = Math.round(maxVal - (maxVal / 4) * i);
        svgContent += `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>`;
        svgContent += `<text x="${padding.left - 8}" y="${y + 4}" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.7">${val}</text>`;
    }

    // Bars
    data.forEach((d, i) => {
        const val = d[valueKey];
        const h = val > 0 ? (val / maxVal) * chartH : 0;
        const x = padding.left + (i * (chartW / data.length)) + (spacing / 2);
        const y = height - padding.bottom - h;

        svgContent += `<rect x="${x}" y="${y}" width="${barWidth}" height="${h}" fill="var(--accent)" rx="4" ry="4"
            onmouseenter="showTooltip(event, '${tooltipId}', '${d.label}: ${val}')" 
            onmouseleave="hideTooltip('${tooltipId}')"/>`;
        
        // X-axis labels
        const labelInterval = Math.ceil(data.length / 6);
        if (i % labelInterval === 0 || i === data.length - 1) {
            svgContent += `<text x="${x + barWidth / 2}" y="${height - 5}" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">${d.label}</text>`;
        }
    });

    svgContent += `</svg>`;
    container.innerHTML = svgContent;
}

/**
 * Area Chart Renderer (Specifically for Year view)
 */
function renderAreaChart(container, data, valueKey, tooltipId) {
    const width = 600;
    const height = 200;
    const padding = { top: 20, right: 20, bottom: 30, left: 40 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;
    
    const values = data.map(d => d[valueKey]);
    const maxVal = Math.max(...values, 1);
    
    const points = data.map((d, i) => {
        return {
            x: padding.left + (i / Math.max(data.length - 1, 1)) * chartW,
            y: height - padding.bottom - (d[valueKey] / maxVal) * chartH,
            label: d.label,
            value: d[valueKey]
        };
    });

    // Create area path
    let areaD = `M ${points[0].x} ${height - padding.bottom}`;
    points.forEach(p => {
        areaD += ` L ${p.x} ${p.y}`;
    });
    areaD += ` L ${points[points.length - 1].x} ${height - padding.bottom} Z`;

    // Create line path
    let lineD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');

    let svgContent = `<svg viewBox="0 0 ${width} ${height}" style="width: 100%; height: 100%;">`;
    
    // Define gradient for the area fill
    svgContent += `<defs>
        <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="var(--accent)" stop-opacity="0.3"/>
            <stop offset="100%" stop-color="var(--accent)" stop-opacity="0.05"/>
        </linearGradient>
    </defs>`;

    // Grid lines
    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (i * chartH / 4);
        const val = Math.round(maxVal - (maxVal / 4) * i);
        svgContent += `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>`;
        svgContent += `<text x="${padding.left - 8}" y="${y + 4}" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.7">${val}</text>`;
    }

    // Draw Area Fill
    svgContent += `<path d="${areaD}" fill="url(#areaGradient)"/>`;
    
    // Draw Line on top
    svgContent += `<path d="${lineD}" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`;

    // Draw Points with tooltips
    points.forEach(p => {
        svgContent += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="var(--card)" stroke="var(--accent)" stroke-width="2" 
            onmouseenter="showTooltip(event, '${tooltipId}', '${p.label}: ${p.value}')" 
            onmouseleave="hideTooltip('${tooltipId}')"/>`;
    });

    // X-axis labels
    const labelInterval = Math.ceil(points.length / 6);
    points.forEach((p, i) => {
        if (i % labelInterval === 0 || i === points.length - 1) {
            svgContent += `<text x="${p.x}" y="${height - 5}" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">${p.label}</text>`;
        }
    });

    svgContent += `</svg>`;
    container.innerHTML = svgContent;
}

/**
 * Tooltip Helpers
 */
window.showTooltip = function (event, tooltipId, text) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    tooltip.textContent = text;
    tooltip.classList.add('visible');
    
    // Position relative to viewport to avoid overflow issues
    tooltip.style.left = (event.clientX + 15) + 'px';
    tooltip.style.top = (event.clientY - 30) + 'px';
    tooltip.style.transform = 'translateX(-50%)';
};

window.hideTooltip = function (tooltipId) {
    const tooltip = document.getElementById(tooltipId);
    if (tooltip) {
        tooltip.classList.remove('visible');
    }
};

/**
 * Workout Form UI Helpers
 */
window.selectWorkoutType = function (type) {
    document.querySelectorAll('.workout-type-card').forEach(function (c) { c.classList.remove('active'); });
    document.querySelectorAll('.workout-form-section').forEach(function (f) { f.classList.remove('active'); });
    
    document.querySelector('.workout-type-card[data-type="' + type + '"]').classList.add('active');
    document.getElementById('form-' + type).classList.add('active');
};

window.setGender = function (gender) {
    window.currentGender = gender;
    document.querySelectorAll('.gender-toggle button').forEach(function (b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    populateExerciseLibrary(gender);
};

function populateExerciseLibrary(gender) {
    const library = document.getElementById('exerciseLibrary');
    const exercises = window.exercisePrograms[gender] || window.exercisePrograms.neutral;
    window.selectedExercises = [];
    
    library.innerHTML = exercises.map(function (ex) {
        return '<label class="exercise-item" onclick="toggleExercise(this, \'' + ex.name + '\')">' +
            '<input type="checkbox" value="' + ex.name + '">' +
            '<div><strong>' + ex.name + '</strong><br>' +
            '<small>' + ex.sets + ' sets × ' + ex.reps + ' (rest ' + ex.rest + ')</small></div>' +
            '</label>';
    }).join('');
}

window.toggleExercise = function (element, name) {
    const checkbox = element.querySelector('input');
    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selected', checkbox.checked);
    
    if (checkbox.checked) {
        window.selectedExercises.push(name);
    } else {
        window.selectedExercises = window.selectedExercises.filter(function (e) { return e !== name; });
    }
};

/**
 * Form Submission Handler
 */
window.submitWorkout = function (typeId) {
    const activeForm = document.querySelector('.workout-form-section.active');
    if (!activeForm) return;
    
    const titleInput = activeForm.querySelector('input[id$="Title"]');
    const title = titleInput ? titleInput.value : '';
    const dateInput = activeForm.querySelector('input[id$="Date"]');
    const date = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
    const durationInput = activeForm.querySelector('input[id$="Duration"]');
    const duration = durationInput ? durationInput.value : null;
    const distanceInput = activeForm.querySelector('input[id$="Distance"]');
    const distance = distanceInput ? distanceInput.value : null;
    
    if (!title) {
        alert('Please enter a session title.');
        return;
    }
    
    let finalTitle = title;
    if (typeId === 4 && window.selectedExercises.length > 0) {
        finalTitle = title + ' [' + window.selectedExercises.join(', ') + ']';
    }
    
    const payload = {
        activity_type_id: typeId,
        title: finalTitle,
        activity_date: date,
        duration_minutes: duration,
        distance_km: distance
    };
    
    fetch('api/activities.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function (res) { return res.json(); })
    .then(function (res) {
        if (res.success) {
            // Reset form fields
            activeForm.querySelectorAll('input').forEach(function (i) { i.value = ''; });
            activeForm.querySelector('input[type="date"]').valueAsDate = new Date();
            
            // Reset UI state
            document.querySelectorAll('.workout-type-card').forEach(function (c) { c.classList.remove('active'); });
            activeForm.classList.remove('active');
            window.selectedExercises = [];
            
            // Refresh data and charts
            location.reload();
        } else {
            alert('Error: ' + (res.error || 'Unknown error'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
};
/* global document, window, fetch, alert, setTimeout, location */
'use strict';

/**
 * File: workouts.js
 * Location: /public/js/workouts.js
 *
 * Purpose:
 * Handles dynamic form switching, exercise library population,
 * time-range chart rendering, and asynchronous workout submission.
 */

// Chart data injected by PHP
const chartData = window.workoutChartData || {};

document.addEventListener('DOMContentLoaded', function () {
    // Set default dates to today
    document.querySelectorAll('input[type="date"]').forEach(function (input) {
        if (!input.value) input.valueAsDate = new Date();
    });

    populateExerciseLibrary(window.currentGender || 'neutral');
    loadRecentWorkouts();

    // Initial chart render
    renderFrequencyChart('week');
    renderVolumeChart('week');
});

/**
 * Switch Chart Range (Week/Month/Year)
 */
window.switchChart = function (chartType, range) {
    // Update active button state
    const controls = event.target.parentElement;
    controls.querySelectorAll('button').forEach(function (btn) {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    if (chartType === 'frequency') {
        renderFrequencyChart(range);
    } else {
        renderVolumeChart(range);
    }
};

/**
 * Render Frequency Chart (Count of workouts)
 */
function renderFrequencyChart(range) {
    const container = document.getElementById('frequencyChart');
    let data = chartData.frequency[range] || [];

    // FIX: Ensure week view always has 7 days (Mon-Sun) even if empty
    if (range === 'week' && data.length < 7) {
        const fullWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const existingDays = data.map(function (d) { return d.label; });
        fullWeek.forEach(function (day) {
            if (existingDays.indexOf(day) === -1) {
                data.push({ label: day, count: 0, minutes: 0 });
            }
        });
        // Re-sort to match Mon-Sun order
        data.sort(function (a, b) { return fullWeek.indexOf(a.label) - fullWeek.indexOf(b.label); });
    }

    if (range === 'year') {
        renderLineChart(container, data, 'count', 'frequencyTooltip', false);
    } else {
        renderBarChart(container, data, 'count', 'workouts', 'frequencyTooltip');
    }
}

/**
 * Render Volume Chart (Minutes)
 */
function renderVolumeChart(range) {
    const container = document.getElementById('volumeChart');
    let data = chartData.volume[range] || [];

    // FIX: Ensure week view always has 7 days
    if (range === 'week' && data.length < 7) {
        const fullWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const existingDays = data.map(function (d) { return d.label; });
        fullWeek.forEach(function (day) {
            if (existingDays.indexOf(day) === -1) {
                data.push({ label: day, count: 0, minutes: 0 });
            }
        });
        data.sort(function (a, b) { return fullWeek.indexOf(a.label) - fullWeek.indexOf(b.label); });
    }

    if (range === 'month' || range === 'year') {
        renderLineChart(container, data, 'minutes', 'volumeTooltip', true);
    } else {
        renderBarChart(container, data, 'minutes', 'mins', 'volumeTooltip');
    }
}

/**
 * Bar Chart Renderer (SVG)
 * Uses explicit fill attributes to prevent black blocks.
 */
function renderBarChart(container, data, valueKey, unit, tooltipId) {
    const maxVal = Math.max.apply(null, data.map(function (d) { return d[valueKey]; })) || 1;
    const width = 400;
    const height = 160;
    const barWidth = (width / data.length) * 0.6;
    const spacing = (width / data.length) * 0.4;

    let svgContent = '<svg class="line-chart" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="none">';

    data.forEach(function (d, i) {
        const val = d[valueKey];
        const h = val > 0 ? Math.max(4, (val / maxVal) * (height - 30)) : 0;
        const x = (i * (width / data.length)) + (spacing / 2);
        const y = height - 20 - h;

        svgContent += '<rect x="' + x + '" y="' + y + '" width="' + barWidth + '" height="' + h + '" ' +
            'fill="var(--accent)" rx="4" ry="4" ' +
            'onmouseenter="showTooltip(event, \'' + tooltipId + '\', \'' + d.label + ': ' + val + ' ' + unit + '\')" ' +
            'onmouseleave="hideTooltip(\'' + tooltipId + '\')"></rect>';
        svgContent += '<text x="' + (x + barWidth / 2) + '" y="' + (height - 5) + '" text-anchor="middle" font-size="10" fill="var(--text)" opacity="0.7">' + d.label + '</text>';
    });

    svgContent += '</svg>';
    container.innerHTML = svgContent;
}

/**
 * Line Chart Renderer (SVG)
 * Uses explicit stroke attributes to prevent black lines.
 */
function renderLineChart(container, data, valueKey, tooltipId, showArea) {
    const width = 400;
    const height = 160;
    const padding = 20;
    const maxVal = Math.max.apply(null, data.map(function (d) { return d[valueKey]; })) || 1;

    const points = data.map(function (d, i) {
        return {
            x: padding + (i / Math.max(data.length - 1, 1)) * (width - 2 * padding),
            y: height - padding - (d[valueKey] / maxVal) * (height - 2 * padding),
            label: d.label,
            value: d[valueKey]
        };
    });

    let pathD = points.map(function (p, i) { return (i === 0 ? 'M' : 'L') + ' ' + p.x + ' ' + p.y; }).join(' ');
    let areaD = '';

    if (showArea) {
        areaD = pathD + ' L ' + points[points.length - 1].x + ' ' + (height - padding) + ' L ' + points[0].x + ' ' + (height - padding) + ' Z';
    }

    let svgContent = '<svg class="line-chart" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="none">';

    if (showArea) {
        svgContent += '<defs>' +
            '<linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1">' +
            '<stop offset="0%" stop-color="var(--accent)" stop-opacity="0.3"/>' +
            '<stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/>' +
            '</linearGradient></defs>' +
            '<path d="' + areaD + '" fill="url(#lineGradient)" opacity="0.5"></path>';
    }

    svgContent += '<path d="' + pathD + '" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>';

    points.forEach(function (p) {
        svgContent += '<circle cx="' + p.x + '" cy="' + p.y + '" r="4" fill="var(--card)" stroke="var(--accent)" stroke-width="2" ' +
            'onmouseenter="showTooltip(event, \'' + tooltipId + '\', \'' + p.label + ': ' + p.value + ' mins\')" ' +
            'onmouseleave="hideTooltip(\'' + tooltipId + '\')"></circle>';
    });

    svgContent += '</svg>';
    container.innerHTML = svgContent;
}

/**
 * Tooltip Logic
 */
window.showTooltip = function (event, tooltipId, text) {
    const tooltip = document.getElementById(tooltipId);
    const container = tooltip.parentElement;
    const rect = container.getBoundingClientRect();
    tooltip.textContent = text;
    tooltip.classList.add('visible');
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top - 40;
    tooltip.style.left = x + 'px';
    tooltip.style.top = y + 'px';
    tooltip.style.transform = 'translateX(-50%)';
};

window.hideTooltip = function (tooltipId) {
    document.getElementById(tooltipId).classList.remove('visible');
};

/**
 * Form & Exercise Logic
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

function loadRecentWorkouts() {
    fetch('/api/activities.php')
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                const container = document.getElementById('recentWorkoutsList');
                if (data.data.length === 0) {
                    container.innerHTML = '<p class="empty-state">No workouts logged yet.</p>';
                    return;
                }
                container.innerHTML = data.data.map(function (act) {
                    return '<div class="activity-item"><div><strong>' + act.title + '</strong> (' + act.type_name + ')<br>' +
                        '<small>' + act.activity_date + ' • ' + (act.duration_minutes || 0) + ' mins' +
                        (act.distance_km ? ' • ' + act.distance_km + ' km' : '') + '</small></div></div>';
                }).join('');
            }
        });
}

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

    fetch('/api/activities.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function (res) { return res.json(); })
    .then(function (res) {
        if (res.success) {
            activeForm.querySelectorAll('input').forEach(function (i) { i.value = ''; });
            activeForm.querySelector('input[type="date"]').valueAsDate = new Date();
            document.querySelectorAll('.workout-type-card').forEach(function (c) { c.classList.remove('active'); });
            activeForm.classList.remove('active');
            window.selectedExercises = [];
            loadRecentWorkouts();
            setTimeout(function () { location.reload(); }, 500);
        } else {
            alert('Error logging workout: ' + (res.error || 'Unknown error'));
        }
    });
};
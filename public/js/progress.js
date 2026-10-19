/* global document, window, JSON, IntersectionObserver */
'use strict';

/**
 * File: progress.js
 * Location: /public/js/progress.js
 * Purpose:
 * Renders SVG charts for the Progress page using data injected by progress.php.
 * Handles Weight Trend (line), Activity Volume (area), and Performance (radar).
 */

document.addEventListener('DOMContentLoaded', function() {
    // Parse chart data from hidden JSON element
    const chartDataEl = document.getElementById('chartData');
    if (!chartDataEl) return;

    let chartData;
    try {
        chartData = JSON.parse(chartDataEl.textContent);
    } catch (e) {
        console.error('Failed to parse chart data:', e);
        return;
    }

    // Initialize charts
    initWeightChart(chartData.weight || {});
    initVolumeChart(chartData.volume || {});
    initRadarChart(chartData.performance || []);
    initScrollAnimations();
    initTimeRangeToggles(chartData);
});

/**
 * Weight Trend Line Chart
 */
function initWeightChart(data) {
    const container = document.getElementById('weightChart');
    if (!container) return;

    // Clear any empty state message
    container.innerHTML = '';

    const range = 'month'; // Default
    const points = data[range] || [];

    if (points.length < 2) {
        container.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text); opacity:0.6;">Log measurements to see trends.</p>';
        return;
    }

    renderLineChart(container, points, 'var(--accent)', 'Weight (kg)');
}

/**
 * Activity Volume Area Chart
 */
function initVolumeChart(data) {
    const container = document.getElementById('volumeChart');
    if (!container) return;

    const range = 'month';
    const points = data[range] || [];

    if (points.length === 0) {
        container.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text); opacity:0.6;">No activity data yet.</p>';
        return;
    }

    renderAreaChart(container, points, 'var(--highlight)', 'Minutes');
}

/**
 * Performance Radar Chart
 */
function initRadarChart(metrics) {
    const container = document.getElementById('radarChart');
    if (!container || metrics.length === 0) return;

    const size = 200;
    const center = size / 2;
    const maxRadius = 80;
    const levels = 5;

    let svg = `<svg viewBox="0 0 ${size} ${size}" width="${size}" height="${size}">`;

    // Draw grid levels
    for (let i = 1; i <= levels; i++) {
        const r = (maxRadius / levels) * i;
        const points = metrics.map((_, idx) => {
            const angle = (Math.PI * 2 * idx) / metrics.length - Math.PI / 2;
            return `${center + r * Math.cos(angle)},${center + r * Math.sin(angle)}`;
        }).join(' ');
        svg += `<polygon points="${points}" fill="none" stroke="var(--border)" stroke-width="1" opacity="0.5"/>`;
    }

    // Draw axes
    metrics.forEach((m, idx) => {
        const angle = (Math.PI * 2 * idx) / metrics.length - Math.PI / 2;
        const x = center + maxRadius * Math.cos(angle);
        const y = center + maxRadius * Math.sin(angle);
        svg += `<line x1="${center}" y1="${center}" x2="${x}" y2="${y}" stroke="var(--border)" stroke-width="1" opacity="0.3"/>`;

        // Labels
        const labelX = center + (maxRadius + 20) * Math.cos(angle);
        const labelY = center + (maxRadius + 20) * Math.sin(angle);
        svg += `<text x="${labelX}" y="${labelY}" text-anchor="middle" font-size="10" fill="var(--text)">${m.label}</text>`;
    });

    // Draw data polygon
    const dataPoints = metrics.map((m, idx) => {
        const angle = (Math.PI * 2 * idx) / metrics.length - Math.PI / 2;
        const r = (m.value / 100) * maxRadius;
        return `${center + r * Math.cos(angle)},${center + r * Math.sin(angle)}`;
    }).join(' ');

    svg += `<polygon points="${dataPoints}" fill="var(--accent)" fill-opacity="0.2" stroke="var(--accent)" stroke-width="2"/>`;

    // Draw data points
    metrics.forEach((m, idx) => {
        const angle = (Math.PI * 2 * idx) / metrics.length - Math.PI / 2;
        const r = (m.value / 100) * maxRadius;
        const x = center + r * Math.cos(angle);
        const y = center + r * Math.sin(angle);
        svg += `<circle cx="${x}" cy="${y}" r="3" fill="var(--accent)"/>`;
    });

    svg += '</svg>';
    container.innerHTML = svg;
}

/**
 * Generic Line Chart Renderer (Responsive)
 */
function renderLineChart(container, data, colour, label) {
    // Get container width for responsive sizing
    const containerWidth = container.offsetWidth || 800;
    const width = Math.max(600, containerWidth - 40);
    const height = 320; // Increased from 200
    const padding = 60; // Increased padding for better spacing

    const values = data.map(d => d.value);
    const minVal = Math.min(...values);
    const maxVal = Math.max(...values);
    const range = maxVal - minVal || 1;

    const points = data.map((d, i) => ({
        x: padding + (i / Math.max(data.length - 1, 1)) * (width - 2 * padding),
        y: height - padding - ((d.value - minVal) / range) * (height - 2 * padding),
        label: d.label,
        value: d.value
    }));

    let svg = `<svg viewBox="0 0 ${width} ${height}" width="100%" height="${height}" preserveAspectRatio="xMidYMid meet">`;

    // Grid lines
    for (let i = 0; i <= 4; i++) {
        const y = padding + (i * (height - 2 * padding) / 4);
        const val = (maxVal - (range / 4) * i).toFixed(1);
        svg += `<line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.3"/>`;
        svg += `<text x="${padding - 10}" y="${y + 4}" text-anchor="end" font-size="11" fill="var(--text)" opacity="0.6">${val}</text>`;
    }

    // Line
    const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    svg += `<path d="${pathD}" fill="none" stroke="${colour}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;

    // Points
    points.forEach(p => {
        svg += `<circle cx="${p.x}" cy="${p.y}" r="5" fill="var(--card)" stroke="${colour}" stroke-width="2.5"/>`;
    });

    // X-axis labels
    const labelInterval = Math.ceil(points.length / 7);
    points.forEach((p, i) => {
        if (i % labelInterval === 0 || i === points.length - 1) {
            svg += `<text x="${p.x}" y="${height - 15}" text-anchor="middle" font-size="11" fill="var(--text)" opacity="0.7">${p.label}</text>`;
        }
    });

    svg += '</svg>';
    container.innerHTML = svg;
}

/**
 * Generic Area Chart Renderer (Responsive)
 */
function renderAreaChart(container, data, colour, label) {
    // Get container width for responsive sizing
    const containerWidth = container.offsetWidth || 800;
    const width = Math.max(600, containerWidth - 40);
    const height = 320; // Increased from 200
    const padding = 60; // Increased padding for better spacing

    const values = data.map(d => d.value);
    const maxVal = Math.max(...values, 60);

    const points = data.map((d, i) => ({
        x: padding + (i / Math.max(data.length - 1, 1)) * (width - 2 * padding),
        y: height - padding - (d.value / maxVal) * (height - 2 * padding),
        label: d.label,
        value: d.value
    }));

    let svg = `<svg viewBox="0 0 ${width} ${height}" width="100%" height="${height}" preserveAspectRatio="xMidYMid meet">`;

    // Grid lines
    for (let i = 0; i <= 4; i++) {
        const y = padding + (i * (height - 2 * padding) / 4);
        const val = Math.round(maxVal - (maxVal / 4) * i);
        svg += `<line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.3"/>`;
        svg += `<text x="${padding - 10}" y="${y + 4}" text-anchor="end" font-size="11" fill="var(--text)" opacity="0.6">${val}</text>`;
    }

    // Area fill
    const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    const areaD = `${pathD} L ${points[points.length - 1].x} ${height - padding} L ${points[0].x} ${height - padding} Z`;
    svg += `<path d="${areaD}" fill="${colour}" opacity="0.15"/>`;

    // Line
    svg += `<path d="${pathD}" fill="none" stroke="${colour}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;

    // Points
    points.forEach(p => {
        svg += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="var(--card)" stroke="${colour}" stroke-width="2.5"/>`;
    });

    // X-axis labels
    const labelInterval = Math.ceil(points.length / 7);
    points.forEach((p, i) => {
        if (i % labelInterval === 0 || i === points.length - 1) {
            svg += `<text x="${p.x}" y="${height - 15}" text-anchor="middle" font-size="11" fill="var(--text)" opacity="0.7">${p.label}</text>`;
        }
    });

    svg += '</svg>';
    container.innerHTML = svg;
}

/**
 * Scroll Animations
 */
function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (elements.length === 0) return;

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(function(el) { observer.observe(el); });
}

/**
 * Time Range Toggle Handlers
 */
function initTimeRangeToggles(chartData) {
    document.querySelectorAll('.time-range-toggles').forEach(function(toggleGroup) {
        const chartSection = toggleGroup.closest('.chart-section');
        const chartType = chartSection ? chartSection.dataset.chartType : null;

        toggleGroup.querySelectorAll('button').forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Update active state
                toggleGroup.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const range = btn.dataset.range;

                // Re-render appropriate chart
                if (chartType === 'weight' && chartData.weight) {
                    const container = document.getElementById('weightChart');
                    const points = chartData.weight[range] || [];
                    if (points.length < 2) {
                        container.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text); opacity:0.6;">Log measurements to see trends.</p>';
                    } else {
                        renderLineChart(container, points, 'var(--accent)', 'Weight (kg)');
                    }
                } else if (chartType === 'volume' && chartData.volume) {
                    const container = document.getElementById('volumeChart');
                    const points = chartData.volume[range] || [];
                    if (points.length === 0) {
                        container.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text); opacity:0.6;">No activity data yet.</p>';
                    } else {
                        renderAreaChart(container, points, 'var(--highlight)', 'Minutes');
                    }
                }
            });
        });
    });
}
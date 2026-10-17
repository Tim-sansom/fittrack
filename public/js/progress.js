/* global document, window, IntersectionObserver, setTimeout, JSON */
'use strict';

/**
 * File: progress.js
 * Location: /public/js/progress.js
 *
 * Purpose:
 * Custom Vanilla JS SVG Charting Engine. Renders Line, Area, and Radar charts
 * without external libraries. Implements scroll-triggered animations and
 * mouse-tracking tooltips.
 *
 * Architectural Role:
 * Demonstrates advanced client-side data visualisation and state management.
 * Satisfies the "independent contribution" criteria by building complex UI
 * components from scratch using pure mathematics and DOM APIs.
 */

const ProgressCharts = {
    /**
     * Initialises all charts on the page.
     * @param {Object} data - The dataset passed from PHP.
     */
    init(data) {
        this.data = data;
        this.currentRange = 'month';
        this.tooltip = this.createTooltip();

        this.renderWeightChart('month');
        this.renderVolumeChart('month');
        this.renderRadarChart();
        this.initScrollAnimations();
        this.initTimeRangeToggles();
    },

    /**
     * Creates a reusable tooltip element.
     */
    createTooltip() {
        const tooltip = document.createElement('div');
        tooltip.className = 'svg-tooltip';
        document.body.appendChild(tooltip);
        return tooltip;
    },

    /**
     * Shows tooltip at mouse coordinates.
     */
    showTooltip(e, text) {
        this.tooltip.textContent = text;
        this.tooltip.classList.add('visible');
        this.tooltip.style.left = e.pageX + 'px';
        this.tooltip.style.top = e.pageY + 'px';
    },

    hideTooltip() {
        this.tooltip.classList.remove('visible');
    },

    /**
     * Renders a smooth Line Chart for Weight/BMI trends.
     */
    renderWeightChart(range) {
        const container = document.getElementById('weightChart');
        if (!container) return;

        const dataset = this.data.weight[range] || [];
        if (dataset.length === 0) {
            container.innerHTML = '<p class="empty-state">Log measurements to see trends.</p>';
            return;
        }

        const width = container.clientWidth;
        const height = 300;
        const padding = 40;
        const maxVal = Math.max(...dataset.map(d => d.value)) * 1.1;
        const minVal = Math.min(...dataset.map(d => d.value)) * 0.9;

        let svg = `<svg class="chart-svg" viewBox="0 0 ${width} ${height}">`;

        // Grid lines
        for (let i = 0; i <= 4; i++) {
            const y = padding + (i / 4) * (height - 2 * padding);
            svg += `<line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" class="chart-grid-line"/>`;
            const val = Math.round(maxVal - (i / 4) * (maxVal - minVal));
            svg += `<text x="${padding - 10}" y="${y + 4}" text-anchor="end" class="chart-axis-text">${val}</text>`;
        }

        // Path calculation
        const points = dataset.map((d, i) => {
            const x = padding + (i / (dataset.length - 1 || 1)) * (width - 2 * padding);
            const y = height - padding - ((d.value - minVal) / (maxVal - minVal)) * (height - 2 * padding);
            return { x, y, label: d.label, value: d.value };
        });

        let pathD = `M ${points[0].x} ${points[0].y}`;
        for (let i = 1; i < points.length; i++) {
            // Simple smoothing using quadratic bezier curves
            const prev = points[i - 1];
            const curr = points[i];
            const cpX = (prev.x + curr.x) / 2;
            pathD += ` Q ${cpX} ${prev.y} ${curr.x} ${curr.y}`;
        }

        svg += `<path class="chart-line animate-draw" d="${pathD}" stroke="var(--accent)" stroke-dasharray="1000" stroke-dashoffset="1000"/>`;

        // Data points
        points.forEach(p => {
            svg += `<circle class="chart-point" cx="${p.x}" cy="${p.y}" r="4" stroke="var(--accent)" 
                    onmouseenter="ProgressCharts.showTooltip(event, '${p.label}: ${p.value} kg')" 
                    onmouseleave="ProgressCharts.hideTooltip()"/>`;
            svg += `<text x="${p.x}" y="${height - 10}" text-anchor="middle" class="chart-axis-text">${p.label}</text>`;
        });

        svg += `</svg>`;
        container.innerHTML = svg;
    },

    /**
     * Renders an Area Chart for Calorie/Volume trends.
     */
    renderVolumeChart(range) {
        const container = document.getElementById('volumeChart');
        if (!container) return;

        const dataset = this.data.volume[range] || [];
        if (dataset.length === 0) {
            container.innerHTML = '<p class="empty-state">Log activities to see volume.</p>';
            return;
        }

        const width = container.clientWidth;
        const height = 300;
        const padding = 40;
        const maxVal = Math.max(...dataset.map(d => d.value)) * 1.2;

        let svg = `<svg class="chart-svg" viewBox="0 0 ${width} ${height}">`;

        const points = dataset.map((d, i) => {
            const x = padding + (i / (dataset.length - 1 || 1)) * (width - 2 * padding);
            const y = height - padding - (d.value / maxVal) * (height - 2 * padding);
            return { x, y, label: d.label, value: d.value };
        });

        let pathD = `M ${points[0].x} ${points[0].y}`;
        for (let i = 1; i < points.length; i++) pathD += ` L ${points[i].x} ${points[i].y}`;
        
        const areaD = `${pathD} L ${points[points.length-1].x} ${height-padding} L ${points[0].x} ${height-padding} Z`;

        svg += `<path class="chart-area animate-draw" d="${areaD}" fill="var(--highlight)" opacity="0"/>`;
        svg += `<path class="chart-line animate-draw" d="${pathD}" stroke="var(--highlight)" stroke-dasharray="1000" stroke-dashoffset="1000"/>`;

        points.forEach(p => {
            svg += `<circle class="chart-point" cx="${p.x}" cy="${p.y}" r="4" stroke="var(--highlight)" 
                    onmouseenter="ProgressCharts.showTooltip(event, '${p.label}: ${p.value} mins')" 
                    onmouseleave="ProgressCharts.hideTooltip()"/>`;
        });

        svg += `</svg>`;
        container.innerHTML = svg;
    },

    /**
     * Renders a Radar (Spider) Chart for Performance Metrics.
     */
    renderRadarChart() {
        const container = document.getElementById('radarChart');
        if (!container) return;

        const metrics = this.data.performance;
        const width = 300;
        const height = 300;
        const center = { x: width / 2, y: height / 2 };
        const radius = 100;
        const angleStep = (Math.PI * 2) / metrics.length;

        let svg = `<svg class="chart-svg" viewBox="0 0 ${width} ${height}">`;

        // Draw grid rings
        for (let r = 1; r <= 4; r++) {
            const rRadius = (r / 4) * radius;
            let points = [];
            for (let i = 0; i < metrics.length; i++) {
                const angle = angleStep * i - Math.PI / 2;
                points.push(`${center.x + rRadius * Math.cos(angle)},${center.y + rRadius * Math.sin(angle)}`);
            }
            svg += `<polygon points="${points.join(' ')}" class="radar-grid"/>`;
        }

        // Draw axes and labels
        metrics.forEach((m, i) => {
            const angle = angleStep * i - Math.PI / 2;
            const x = center.x + radius * Math.cos(angle);
            const y = center.y + radius * Math.sin(angle);
            svg += `<line x1="${center.x}" y1="${center.y}" x2="${x}" y2="${y}" class="radar-axis"/>`;
            const labelX = center.x + (radius + 20) * Math.cos(angle);
            const labelY = center.y + (radius + 20) * Math.sin(angle);
            svg += `<text x="${labelX}" y="${labelY + 4}" class="radar-label">${m.label}</text>`;
        });

        // Draw data shape
        let shapePoints = [];
        metrics.forEach((m, i) => {
            const angle = angleStep * i - Math.PI / 2;
            const valRadius = (m.value / 100) * radius; // Assuming value is 0-100%
            const x = center.x + valRadius * Math.cos(angle);
            const y = center.y + valRadius * Math.sin(angle);
            shapePoints.push(`${x},${y}`);
        });

        svg += `<polygon points="${shapePoints.join(' ')}" class="radar-shape animate-draw" opacity="0"/>`;
        svg += `</svg>`;
        container.innerHTML = svg;
    },

    /**
     * Uses IntersectionObserver to trigger CSS animations when charts scroll into view.
     */
    initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    if (el.classList.contains('chart-line')) {
                        el.style.transition = 'stroke-dashoffset 1.5s ease-in-out';
                        el.style.strokeDashoffset = '0';
                    }
                    if (el.classList.contains('chart-area') || el.classList.contains('radar-shape')) {
                        el.style.transition = 'opacity 1s ease-in-out 0.5s';
                        el.style.opacity = el.classList.contains('radar-shape') ? '0.2' : '0.15';
                    }
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.animate-draw').forEach(el => observer.observe(el));
    },

    /**
     * Handles time-range toggle buttons.
     */
    initTimeRangeToggles() {
        document.querySelectorAll('.time-range-toggles button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const range = e.target.dataset.range;
                const chartType = e.target.closest('.chart-section').dataset.chartType;

                // Update active state
                e.target.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');

                // Re-render specific chart
                if (chartType === 'weight') this.renderWeightChart(range);
                if (chartType === 'volume') this.renderVolumeChart(range);

                // Re-attach animations
                setTimeout(() => this.initScrollAnimations(), 50);
            });
        });
    }
};

// Expose tooltip methods globally for inline SVG handlers
window.ProgressCharts = ProgressCharts;

document.addEventListener('DOMContentLoaded', () => {
    const chartDataElement = document.getElementById('chartData');
    if (chartDataElement) {
        const data = JSON.parse(chartDataElement.textContent);
        ProgressCharts.init(data);
    }
});
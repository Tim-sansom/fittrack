/**
 * File: dashboard.js
 * Location: /public/js/dashboard.js
 *
 * Purpose:
 * Handles all dashboard animations and chart rendering.
 * Implements animated counters, scroll-triggered fade-ins, bar chart growth,
 * SVG line/area chart drawing with tooltips, sparkline rendering, and
 * donut ring animations.
 *
 * Architectural Role:
 * Demonstrates advanced vanilla JS with IntersectionObserver, SVG DOM
 * manipulation, and CSS animation orchestration. No external libraries.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initAnimatedCounters();
    initScrollAnimations();
    initBarChart();
    initVolumeChart();
    initWeightSparkline();
    initDonutRings();
    initProgressBars();
});

/**
 * Animated Number Counters
 * Counts up from 0 to target value when element enters viewport.
 */
function initAnimatedCounters() {
    const counters = document.querySelectorAll('.stat-value[data-target]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target, 10);
                animateCounter(el, target);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, target) {
    const duration = 1200;
    const steps = 50;
    const increment = target / steps;
    let step = 0;
    const timer = setInterval(() => {
        step++;
        const current = Math.min(Math.round(increment * step), Math.abs(target));
        element.textContent = target < 0 ? -current : current;
        if (step >= steps) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, duration / steps);
}

/**
 * Scroll-Triggered Fade-In Animations
 */
function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    elements.forEach(el => observer.observe(el));
}

/**
 * Bar Chart Growth Animation
 */
function initBarChart() {
    const bars = document.querySelectorAll('.bar[data-height]');
    setTimeout(() => {
        bars.forEach((bar, index) => {
            setTimeout(() => {
                bar.style.height = bar.dataset.height;
            }, index * 80);
        });
    }, 400);
}

/**
 * Main Volume Line Chart (Week/Month/Year)
 * Draws an SVG area + line chart with interactive tooltips.
 */
function initVolumeChart() {
    const data = dashboardData.volumeData;
    if (!data || data.length === 0) {
        const svg = document.getElementById('volumeChart');
        if (svg) {
            svg.innerHTML = `<text x="400" y="140" text-anchor="middle" fill="var(--text)" opacity="0.5" font-size="14">No activity data for this period</text>`;
        }
        return;
    }

    const svg = document.getElementById('volumeChart');
    const line = document.getElementById('volumeLine');
    const area = document.getElementById('volumeArea');
    const pointsGroup = document.getElementById('volumePoints');
    const gridGroup = document.getElementById('volumeGrid');
    const tooltip = document.getElementById('volumeTooltip');

    const width = 800;
    const height = 280;
    const padding = { top: 20, right: 20, bottom: 40, left: 50 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    const maxVal = Math.max(...data.map(d => d.total_mins), 60);
    const yMax = Math.ceil(maxVal / 30) * 30; // Round up to nearest 30

    // Draw horizontal grid lines
    let gridHtml = '';
    const gridSteps = 4;
    for (let i = 0; i <= gridSteps; i++) {
        const y = padding.top + (chartH / gridSteps) * i;
        const val = Math.round(yMax - (yMax / gridSteps) * i);
        gridHtml += `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>`;
        gridHtml += `<text x="${padding.left - 8}" y="${y + 4}" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.6">${val}</text>`;
    }
    gridGroup.innerHTML = gridHtml;

    // Calculate points
    const points = data.map((d, i) => {
        const x = padding.left + (i / Math.max(data.length - 1, 1)) * chartW;
        const y = padding.top + chartH - (d.total_mins / yMax) * chartH;
        return { x, y, ...d };
    });

    // Build path strings
    const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    line.setAttribute('d', pathD);

    const areaD = `${pathD} L ${points[points.length - 1].x} ${padding.top + chartH} L ${points[0].x} ${padding.top + chartH} Z`;
    area.setAttribute('d', areaD);

    // Draw data points with tooltips
    points.forEach(p => {
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', p.x);
        circle.setAttribute('cy', p.y);
        circle.setAttribute('r', 5);
        circle.setAttribute('class', 'data-point');

        const dateObj = new Date(p.activity_date);
        const dateLabel = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });

        circle.addEventListener('mouseenter', () => {
            tooltip.innerHTML = `<strong>${dateLabel}</strong><br>${p.session_count} session${p.session_count !== 1 ? 's' : ''}<br>${p.total_mins} mins`;
            tooltip.classList.add('visible');
        });
        circle.addEventListener('mousemove', (e) => {
            const rect = svg.getBoundingClientRect();
            tooltip.style.left = (e.clientX - rect.left + 12) + 'px';
            tooltip.style.top = (e.clientY - rect.top - 40) + 'px';
        });
        circle.addEventListener('mouseleave', () => {
            tooltip.classList.remove('visible');
        });

        pointsGroup.appendChild(circle);
    });

    // X-axis labels (show every nth label to avoid crowding)
    const labelInterval = data.length > 14 ? Math.ceil(data.length / 7) : 1;
    let xLabelsHtml = '';
    points.forEach((p, i) => {
        if (i % labelInterval === 0 || i === points.length - 1) {
            const dateObj = new Date(p.activity_date);
            const label = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
            xLabelsHtml += `<text x="${p.x}" y="${height - 8}" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">${label}</text>`;
        }
    });
    gridGroup.innerHTML += xLabelsHtml;
}

/**
 * Weight Trend Sparkline
 */
function initWeightSparkline() {
    const data = dashboardData.weightTrend;
    if (!data || data.length < 2) return;

    const svg = document.getElementById('weightChart');
    const line = document.getElementById('weightLine');
    const pointsGroup = document.getElementById('weightPoints');
    if (!svg) return;

    const width = 300;
    const height = 100;
    const padding = 10;

    const weights = data.map(d => parseFloat(d.weight_kg));
    const minVal = Math.min(...weights);
    const maxVal = Math.max(...weights);
    const range = maxVal - minVal || 1;

    const points = weights.map((w, i) => {
        const x = padding + (i / (weights.length - 1)) * (width - padding * 2);
        const y = height - padding - ((w - minVal) / range) * (height - padding * 2);
        return { x, y, weight: w };
    });

    line.setAttribute('d', `M ${points.map(p => `${p.x},${p.y}`).join(' L ')}`);

    // Add points
    points.forEach(p => {
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', p.x);
        circle.setAttribute('cy', p.y);
        circle.setAttribute('r', 3);
        circle.setAttribute('fill', 'var(--card)');
        circle.setAttribute('stroke', 'var(--success)');
        circle.setAttribute('stroke-width', '2');
        pointsGroup.appendChild(circle);
    });
}

/**
 * Donut Ring Animations (calorie rings)
 */
function initDonutRings() {
    const rings = document.querySelectorAll('.donut-intake, .donut-burn');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                setTimeout(() => {
                    el.style.strokeDashoffset = el.dataset.target;
                }, 300);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    rings.forEach(ring => {
        ring.style.transition = 'stroke-dashoffset 1s ease-in-out';
        observer.observe(ring);
    });
}

/**
 * Progress Bar Animations (challenges)
 */
function initProgressBars() {
    const bars = document.querySelectorAll('.progress-fill[data-width]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.width = entry.target.dataset.width;
                }, 300);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    bars.forEach(bar => observer.observe(bar));
}
/* global window, document, console, IntersectionObserver, setInterval, clearInterval, setTimeout, dashboardData */
'use strict';

/**
 * File: dashboard.js
 * Location: /public/js/dashboard.js
 * Purpose:
 * Handles all dashboard animations and chart rendering.
 * Provides a global bridge function (window.runDashboardInits) so the
 * SPA shell can call these charts directly without reloading the page.
 */

/**
 * GLOBAL BRIDGE
 * Allows the SPA shell to call these charts directly.
 */
window.runDashboardInits = function () {
    if (!document.getElementById('volumeChart') && !document.querySelector('.stat-value[data-target]')) {
        return;
    }

    try { initAnimatedCounters(); } catch (e) { console.error('Counter error:', e); }
    try { initScrollAnimations(); } catch (e) { console.error('Scroll error:', e); }
    try { initBarChart(); } catch (e) { console.error('Bar chart error:', e); }
    try { initVolumeChart(); } catch (e) { console.error('Volume chart error:', e); }
    try { initWeightSparkline(); } catch (e) { console.error('Sparkline error:', e); }
    try { initDonutRings(); } catch (e) { console.error('Donut error:', e); }
    try { initProgressBars(); } catch (e) { console.error('Progress bar error:', e); }
    try { initHeroRings(); } catch (e) { console.error('Hero ring error:', e); }
};

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.runDashboardInits === 'function') {
        window.runDashboardInits();
    }
});

// =====================================================================
// CHART FUNCTIONS
// =====================================================================

function initAnimatedCounters() {
    const counters = document.querySelectorAll('.stat-value[data-target]');
    if (counters.length === 0) { return; }
    
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target, 10);
                animateCounter(el, target);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function (counter) { observer.observe(counter); });
}

function animateCounter(element, target) {
    const duration = 1200;
    const steps = 50;
    const increment = target / steps;
    let step = 0;
    
    const timer = setInterval(function () {
        step++;
        const current = Math.min(Math.round(increment * step), Math.abs(target));
        element.textContent = target < 0 ? -current : current;
        if (step >= steps) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, duration / steps);
}

function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (elements.length === 0) { return; }
    
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(function (el) { observer.observe(el); });
}

function initBarChart() {
    const bars = document.querySelectorAll('.bar[data-height]');
    if (bars.length === 0) { return; }
    
    setTimeout(function () {
        bars.forEach(function (bar, index) {
            setTimeout(function () {
                bar.style.height = bar.dataset.height;
            }, index * 80);
        });
    }, 400);
}

function aggregateByWeek(dailyData) {
    const weeks = {};
    dailyData.forEach(function (day) {
        const parts = day.activity_date.split('-');
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        
        const dayOfWeek = date.getDay() || 7;
        date.setDate(date.getDate() - dayOfWeek + 1);
        
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        const weekKey = `${y}-${m}-${d}`;
        
        if (!weeks[weekKey]) {
            weeks[weekKey] = { activity_date: weekKey, session_count: 0, total_mins: 0 };
        }
        weeks[weekKey].session_count += parseInt(day.session_count, 10) || 0;
        weeks[weekKey].total_mins += parseInt(day.total_mins, 10) || 0;
    });
    
    return Object.values(weeks)
        .filter(function (w) { return w.total_mins > 0; })
        .sort(function (a, b) { return new Date(a.activity_date) - new Date(b.activity_date); });
}

/**
 * Volume Chart (Adaptive: Bar for Week, Line for Month/Year)
 */
function initVolumeChart() {
    const svg = document.getElementById('volumeChart');
    if (!svg) { return; }
    
    if (typeof dashboardData === 'undefined' || !dashboardData.volumeData) { return; }
    
    const rawData = dashboardData.volumeData;
    if (!rawData || rawData.length === 0) {
        svg.innerHTML = '<text x="400" y="140" text-anchor="middle" fill="var(--text)" opacity="0.5" font-size="14">No activity data</text>';
        return;
    }

    const range = dashboardData.range || 'week';
    const data = range === 'year' ? aggregateByWeek(rawData) : rawData;
    
    const line = document.getElementById('volumeLine');
    const area = document.getElementById('volumeArea');
    const pointsGroup = document.getElementById('volumePoints');
    const gridGroup = document.getElementById('volumeGrid');
    const tooltip = document.getElementById('volumeTooltip');
    
    if (!line || !area || !pointsGroup || !gridGroup) { return; }

    // Clear previous renders
    line.setAttribute('d', '');
    area.setAttribute('d', '');
    pointsGroup.innerHTML = '';
    gridGroup.innerHTML = '';

    const width = 800;
    const height = 280;
    const padding = { top: 20, right: 20, bottom: 40, left: 50 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;
    
    const maxVal = Math.max.apply(null, data.map(function (d) { return parseInt(d.total_mins, 10) || 0; }));
    const yMax = Math.max(Math.ceil(maxVal / 60) * 60, 60);
    
    let gridHtml = '';
    const gridSteps = 4;
    for (let i = 0; i <= gridSteps; i++) {
        const y = padding.top + (chartH / gridSteps) * i;
        const val = Math.round(yMax - (yMax / gridSteps) * i);
        gridHtml += '<line x1="' + padding.left + '" y1="' + y + '" x2="' + (width - padding.right) + '" y2="' + y + '" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>';
        gridHtml += '<text x="' + (padding.left - 8) + '" y="' + (y + 4) + '" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.6">' + val + '</text>';
    }
    gridGroup.innerHTML = gridHtml;

    const points = data.map(function (d, i) {
        const x = padding.left + (i / Math.max(data.length - 1, 1)) * chartW;
        const mins = parseInt(d.total_mins, 10) || 0;
        const y = padding.top + chartH - (mins / yMax) * chartH;
        
        const parts = d.activity_date.split('-');
        const dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
        const dateLabel = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
        
        return { x: x, y: y, activity_date: d.activity_date, session_count: d.session_count, numericMins: mins, dateLabel: dateLabel };
    });

    // =========================================================
    // WEEK VIEW: Render as Bar Chart
    // =========================================================
    if (range === 'week') {
        line.style.display = 'none';
        area.style.display = 'none';
        
        const barWidth = (chartW / data.length) * 0.5;
        const barGap = (chartW / data.length) * 0.5;
        
        points.forEach(function (p, i) {
            const barX = padding.left + (i * (barWidth + barGap)) + (barGap / 2);
            const barHeight = Math.max(4, (padding.top + chartH) - p.y);
            const barY = (padding.top + chartH) - barHeight;
            
            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', barX);
            rect.setAttribute('y', barY);
            rect.setAttribute('width', barWidth);
            rect.setAttribute('height', barHeight);
            rect.setAttribute('fill', '#5D2E5D');
            rect.setAttribute('rx', '4');
            rect.setAttribute('ry', '4');
            rect.style.opacity = p.numericMins === 0 ? '0.2' : '1';
            rect.style.transition = 'height 0.6s ease, opacity 0.3s ease, fill 0.2s ease';
            
            rect.addEventListener('mouseenter', function () {
                tooltip.innerHTML = '<strong>' + p.dateLabel + '</strong><br>' + p.session_count + ' session' + (p.session_count !== 1 ? 's' : '') + '<br>' + p.numericMins + ' mins';
                tooltip.classList.add('visible');
                rect.setAttribute('fill', '#9F5F91');
            });
            rect.addEventListener('mousemove', function (e) {
                const rectSvg = svg.getBoundingClientRect();
                tooltip.style.left = (e.clientX - rectSvg.left + 12) + 'px';
                tooltip.style.top = (e.clientY - rectSvg.top - 40) + 'px';
            });
            rect.addEventListener('mouseleave', function () {
                tooltip.classList.remove('visible');
                rect.setAttribute('fill', '#5D2E5D');
            });
            pointsGroup.appendChild(rect);
            
            // X-axis labels for bars
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', barX + (barWidth / 2));
            text.setAttribute('y', height - 8);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('fill', 'var(--text)');
            text.setAttribute('font-size', '10');
            text.setAttribute('opacity', '0.7');
            text.textContent = p.dateLabel;
            gridGroup.appendChild(text);
        });
    } 
    // =========================================================
    // MONTH/YEAR VIEW: Render as Line Chart
    // =========================================================
    else {
        line.style.display = 'block';
        area.style.display = 'block';
        
        const pathD = points.map(function (p, i) { return (i === 0 ? 'M' : 'L') + ' ' + p.x + ' ' + p.y; }).join(' ');
        line.setAttribute('d', pathD);
        
        const areaD = pathD + ' L ' + points[points.length - 1].x + ' ' + (padding.top + chartH) + ' L ' + points[0].x + ' ' + (padding.top + chartH) + ' Z';
        area.setAttribute('d', areaD);

        const showCircles = data.length <= 15;
        if (showCircles) {
            points.forEach(function (p) {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', p.x);
                circle.setAttribute('cy', p.y);
                circle.setAttribute('r', 5);
                circle.setAttribute('class', 'data-point');
                
                circle.addEventListener('mouseenter', function () {
                    tooltip.innerHTML = '<strong>' + p.dateLabel + '</strong><br>' + p.session_count + ' session' + (p.session_count !== 1 ? 's' : '') + '<br>' + p.numericMins + ' mins';
                    tooltip.classList.add('visible');
                });
                circle.addEventListener('mousemove', function (e) {
                    const rectSvg = svg.getBoundingClientRect();
                    tooltip.style.left = (e.clientX - rectSvg.left + 12) + 'px';
                    tooltip.style.top = (e.clientY - rectSvg.top - 40) + 'px';
                });
                circle.addEventListener('mouseleave', function () {
                    tooltip.classList.remove('visible');
                });
                pointsGroup.appendChild(circle);
            });
        }

        const labelInterval = data.length > 14 ? Math.ceil(data.length / 7) : 1;
        let xLabelsHtml = '';
        points.forEach(function (p, i) {
            if (i % labelInterval === 0 || i === points.length - 1) {
                xLabelsHtml += '<text x="' + p.x + '" y="' + (height - 8) + '" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">' + p.dateLabel + '</text>';
            }
        });
        gridGroup.innerHTML += xLabelsHtml;
    }
}

function initWeightSparkline() {
    const svg = document.getElementById('weightChart');
    if (!svg) { return; }
    if (typeof dashboardData === 'undefined' || !dashboardData.weightTrend) { return; }
    
    const data = dashboardData.weightTrend;
    if (!data || data.length < 2) { return; }
    
    const line = document.getElementById('weightLine');
    const pointsGroup = document.getElementById('weightPoints');
    if (!line || !pointsGroup) { return; }

    const width = 300;
    const height = 100;
    const padding = 10;
    const weights = data.map(function (d) { return parseFloat(d.weight_kg); });
    const minVal = Math.min.apply(null, weights);
    const maxVal = Math.max.apply(null, weights);
    const range = maxVal - minVal || 1;

    const points = weights.map(function (w, i) {
        const x = padding + (i / (weights.length - 1)) * (width - padding * 2);
        const y = height - padding - ((w - minVal) / range) * (height - padding * 2);
        return { x: x, y: y, weight: w };
    });

    line.setAttribute('d', 'M ' + points.map(function (p) { return p.x + ',' + p.y; }).join(' L '));
    
    points.forEach(function (p) {
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

function initDonutRings() {
    const rings = document.querySelectorAll('.donut-intake, .donut-burn');
    if (rings.length === 0) { return; }
    
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                const el = entry.target;
                setTimeout(function () {
                    el.style.strokeDashoffset = el.dataset.target;
                }, 300);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    rings.forEach(function (ring) {
        ring.style.transition = 'stroke-dashoffset 1s ease-in-out';
        observer.observe(ring);
    });
}

function initProgressBars() {
    const bars = document.querySelectorAll('.progress-fill[data-width]');
    if (bars.length === 0) { return; }
    
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                setTimeout(function () {
                    entry.target.style.width = entry.target.dataset.width;
                }, 300);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    bars.forEach(function (bar) { observer.observe(bar); });
}

function initHeroRings() {
    const rings = document.querySelectorAll('.ring-fill');
    if (rings.length === 0) { return; }

    setTimeout(function () {
        rings.forEach(function (ring) {
            const target = ring.getAttribute('data-target');
            if (target !== null && target !== '') {
                ring.style.strokeDashoffset = target;
            }
        });
    }, 100);
}
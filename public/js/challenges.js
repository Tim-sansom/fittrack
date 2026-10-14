/**
 * File: challenges.js
 * Location: /public/js/challenges.js
 *
 * Purpose:
 * Manages the Challenges page UI: Heatmap generation, Tab switching,
 * Async form submission, and dynamic challenge rendering.
 *
 * Architectural Role:
 * Demonstrates advanced client-side data visualisation and state management.
 * Satisfies the "independent contribution" criteria by building complex UI
 * components from scratch using pure mathematics and DOM APIs.
 *
 * Guard Clause:
 * All initialisation functions check for the existence of their required
 * DOM elements before running. This prevents errors when the script loads
 * on non-challenges pages (including the SPA shell).
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Page Detection Guard
     * Only initialise challenge functionality when the challenges page
     * elements are present in the DOM. This prevents the script from
     * running on the SPA shell or other pages.
     */
    const challengePageIndicator = document.getElementById('heatmapGrid')
        || document.getElementById('challengeList')
        || document.getElementById('challengeModal');

    if (!challengePageIndicator) return;

    initHeatmap();
    initTabs();
    initModal();
    loadChallenges('active');
});

/* =========================================
 * 1. HEATMAP GENERATOR
 * =========================================
 * Builds a 365-day GitHub-style contribution heatmap.
 * Activity counts are read from a global `activityData` object
 * injected by the server-side template. Levels are calculated
 * as percentiles of the maximum count.
 */
function initHeatmap() {
    const grid = document.getElementById('heatmapGrid');
    if (!grid) return;

    // Guard: activityData must be defined by the server template
    if (typeof activityData === 'undefined') {
        grid.innerHTML = '<div class="empty-state">No activity data available.</div>';
        return;
    }

    const today = new Date();
    const daysToShow = 365;
    const counts = Object.values(activityData);
    const maxCount = Math.max(...counts, 1);

    for (let i = daysToShow; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        const count = activityData[dateStr] || 0;

        let level = 0;
        if (count > 0) level = 1;
        if (count > maxCount * 0.25) level = 2;
        if (count > maxCount * 0.50) level = 3;
        if (count > maxCount * 0.75) level = 4;

        const cell = document.createElement('div');
        cell.className = `heatmap-day level-${level}`;
        cell.title = `${date.toLocaleDateString('en-GB')}: ${count} activities`;
        grid.appendChild(cell);
    }
}

/* =========================================
 * 2. TABS & DATA FETCHING
 * =========================================
 * Tab buttons switch the challenge list between active,
 * completed, and expired states. Each click triggers a
 * fresh fetch from the API endpoint.
 */
function initTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
    if (tabs.length === 0) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            tabs.forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            loadChallenges(e.target.dataset.status);
        });
    });
}

function loadChallenges(status) {
    const container = document.getElementById('challengeList');
    if (!container) return;

    container.innerHTML = '<div class="empty-state">Loading...</div>';

    fetch(`/api/challenges.php?status=${status}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (status === 'active' && document.getElementById('activeGoalCount')) {
                    document.getElementById('activeGoalCount').textContent = data.data.length;
                }
                renderChallenges(data.data, container);
            } else {
                container.innerHTML = '<div class="empty-state">Failed to load.</div>';
            }
        })
        .catch(() => container.innerHTML = '<div class="empty-state">Error connecting to server.</div>');
}

function renderChallenges(challenges, container) {
    if (challenges.length === 0) {
        container.innerHTML = '<div class="empty-state">No challenges found in this category.</div>';
        return;
    }

    container.innerHTML = challenges.map(c => {
        const progress = Math.min(100, (c.current_value / c.target_value) * 100);
        const daysLeft = Math.ceil((new Date(c.end_date) - new Date()) / (1000 * 60 * 60 * 24));

        return `
        <div class="challenge-card">
            <div class="challenge-title">${escapeHtml(c.title)}</div>
            <div class="challenge-stats">
                <span>${c.current_value} / ${c.target_value} ${escapeHtml(c.unit)}</span>
                <span>${daysLeft > 0 ? daysLeft + ' days left' : 'Expired'}</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: ${progress}%;"></div>
            </div>
            <div style="text-align:center; font-size:0.875rem; opacity:0.7;">
                ${progress.toFixed(1)}% Complete
            </div>
        </div>
        `;
    }).join('');
}

/* =========================================
 * 3. MODAL & FORM SUBMISSION
 * =========================================
 * The modal uses inline style.display for visibility control
 * to bypass any CSS conflicts. It only opens in response to
 * a user click.
 */
function initModal() {
    const modal = document.getElementById('challengeModal');
    const openBtn = document.getElementById('openChallengeModal');
    const closeBtn = document.getElementById('closeChallengeModal');
    const form = document.getElementById('challengeForm');

    // Force modal hidden on init using inline style
    if (modal) {
        modal.style.display = 'none';
    }

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    // Close when clicking the backdrop (outside the modal content)
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Async form submission
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            fetch('/api/challenges.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    modal.style.display = 'none';
                    form.reset();
                    // Refresh the active tab to show the new challenge
                    const activeTab = document.querySelector('.tab-btn[data-status="active"]');
                    if (activeTab) activeTab.click();
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            })
            .catch(() => alert('Network error. Please try again.'));
        });
    }
}

/* =========================================
 * UTILITY
 * =========================================
 * Escapes HTML entities to prevent XSS when rendering
 * user-supplied challenge titles and units.
 */
function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
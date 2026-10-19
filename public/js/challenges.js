/* eslint-env browser */
'use strict';

/**
 * File: challenges.js
 * Location: /public/js/challenges.js
 *
 * Purpose:
 * Manages the Challenges page UI: GitHub-style heatmap generation,
 * tab switching, async form submission, and dynamic challenge rendering.
 *
 * Architectural Role:
 * Demonstrates advanced client-side data visualisation and state management.
 * The heatmap uses column-major ordering (week-by-week, Mon-Sun) to match
 * GitHub's contribution graph layout.
 *
 * Guard Clause:
 * All initialisation functions check for the existence of their required
 * DOM elements before running. This prevents errors when the script loads
 * on non-challenges pages (including the SPA shell).
 */

document.addEventListener('DOMContentLoaded', function () {
    /*
     * Page Detection Guard
     * Only initialise challenge functionality when the challenges page
     * elements are present in the DOM.
     */
    const challengePageIndicator = document.getElementById('heatmapGrid')
        || document.getElementById('challengeList')
        || document.getElementById('challengeModal');
    if (!challengePageIndicator) {
        return;
    }

    initHeatmap();
    initTabs();
    initModal();
    loadChallenges('active');
});

/* =========================================
   1. HEATMAP GENERATOR (GitHub-style)
   ========================================= */
function initHeatmap() {
    const grid = document.getElementById('heatmapGrid');
    if (!grid) {
        return;
    }

    // Guard: activityData must be defined by the server template
    if (typeof activityData === 'undefined') {
        grid.innerHTML = '<div class="empty-state">No activity data available.</div>';
        return;
    }

    const today = new Date();
    const daysToShow = 364; // 52 weeks × 7 days = 364 days

    // Find the counts to determine intensity levels
    const counts = Object.values(activityData);
    const maxCount = Math.max(...counts, 1);

    // GitHub heatmap renders column-by-column (week by week), not row-by-row.
    // Each column is a week (Sunday to Saturday), each row is a day of week.
    // We need to calculate the start date as 52 weeks ago, aligned to Sunday.
    const startDate = new Date(today);
    startDate.setDate(startDate.getDate() - daysToShow);

    // Align to Sunday (day 0)
    while (startDate.getDay() !== 0) {
        startDate.setDate(startDate.getDate() - 1);
    }

    // Generate 52 weeks × 7 days = 364 cells
    for (let week = 0; week < 52; week++) {
        for (let day = 0; day < 7; day++) {
            const cellDate = new Date(startDate);
            cellDate.setDate(startDate.getDate() + (week * 7) + day);

            // Skip future dates
            if (cellDate > today) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'heatmap-day level-0';
                emptyCell.style.visibility = 'hidden';
                grid.appendChild(emptyCell);
                continue;
            }

            const dateStr = cellDate.toISOString().split('T')[0];
            const count = activityData[dateStr] || 0;

            let level = 0;
            if (count > 0) { level = 1; }
            if (count > maxCount * 0.25) { level = 2; }
            if (count > maxCount * 0.50) { level = 3; }
            if (count > maxCount * 0.75) { level = 4; }

            const cell = document.createElement('div');
            cell.className = 'heatmap-day level-' + level;
            cell.title = cellDate.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ': ' + count + ' activities';
            grid.appendChild(cell);
        }
    }
}

/* =========================================
   2. TABS & DATA FETCHING
   ========================================= */
function initTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
    if (tabs.length === 0) {
        return;
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            tabs.forEach(function (t) {
                t.classList.remove('active');
            });
            e.target.classList.add('active');
            loadChallenges(e.target.dataset.status);
        });
    });
}

function loadChallenges(status) {
    const container = document.getElementById('challengeList');
    if (!container) {
        return;
    }

    container.innerHTML = '<div class="empty-state">Loading...</div>';

    fetch('api/challenges.php?status=' + status)
        .then(function (res) {
            return res.json();
        })
        .then(function (data) {
            if (data.success) {
                if (status === 'active' && document.getElementById('activeGoalCount')) {
                    document.getElementById('activeGoalCount').textContent = data.data.length;
                }
                renderChallenges(data.data, container);
            } else {
                container.innerHTML = '<div class="empty-state">Failed to load.</div>';
            }
        })
        .catch(function () {
            container.innerHTML = '<div class="empty-state">Error connecting to server.</div>';
        });
}

function renderChallenges(challenges, container) {
    if (challenges.length === 0) {
        container.innerHTML = '<div class="empty-state">No challenges found in this category.</div>';
        return;
    }

    container.innerHTML = challenges.map(function (c) {
        // Prevent division by zero if target is missing
        const target = parseFloat(c.target_value) || 1;
        const progress = Math.min(100, (parseFloat(c.current_value) / target) * 100);
        const daysLeft = Math.ceil((new Date(c.end_date) - new Date()) / (1000 * 60 * 60 * 24));

        return '<div class="challenge-card">' +
            '<div class="challenge-title">' + escapeHtml(c.title) + '</div>' +
            '<div class="challenge-stats">' +
                '<span>' + c.current_value + ' / ' + c.target_value + ' ' + escapeHtml(c.unit) + '</span>' +
                '<span>' + (daysLeft > 0 ? daysLeft + ' days left' : 'Expired') + '</span>' +
            '</div>' +
            '<div class="progress-track">' +
                '<div class="progress-fill" style="width: ' + progress + '%;"></div>' +
            '</div>' +
            '<div style="text-align:center; font-size:0.875rem; opacity:0.7;">' +
                progress.toFixed(1) + '% Complete' +
            '</div>' +
        '</div>';
    }).join('');
}

/* =========================================
   3. MODAL & FORM SUBMISSION
   ========================================= */
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
        openBtn.addEventListener('click', function () {
            modal.style.display = 'flex';
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    // Close when clicking the backdrop (outside the modal content)
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Async form submission
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            fetch('api/challenges.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    modal.style.display = 'none';
                    form.reset();
                    // Refresh the active tab to show the new challenge
                    const activeTab = document.querySelector('.tab-btn[data-status="active"]');
                    if (activeTab) {
                        activeTab.click();
                    }
                } else {
                    // eslint-disable-next-line no-alert
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            })
            // eslint-disable-next-line no-alert
            .catch(function () {
                alert('Network error. Please try again.');
            });
        });
    }
}

/* =========================================
   UTILITY
   ========================================= */
function escapeHtml(text) {
    if (typeof text !== 'string') {
        return '';
    }
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
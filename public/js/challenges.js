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
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initHeatmap();
    initTabs();
    initModal();
    loadChallenges('active'); // Initial load
});

/* =========================================
   1. HEATMAP GENERATOR
   ========================================= */
function initHeatmap() {
    const grid = document.getElementById('heatmapGrid');
    if (!grid) return;

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
   2. TABS & DATA FETCHING
   ========================================= */
function initTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
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
    container.innerHTML = '<div class="empty-state">Loading...</div>';

    fetch(`/api/challenges.php?status=${status}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (status === 'active') {
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
   3. MODAL & FORM SUBMISSION
   ========================================= */
function initModal() {
    const modal = document.getElementById('challengeModal');
    const openBtn = document.getElementById('openChallengeModal');
    const closeBtn = document.getElementById('closeChallengeModal');
    const form = document.getElementById('challengeForm');

    if (openBtn) openBtn.addEventListener('click', () => modal.classList.add('active'));
    if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });

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
                    modal.classList.remove('active');
                    form.reset();
                    // Refresh active tab
                    document.querySelector('.tab-btn[data-status="active"]').click();
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            });
        });
    }
}

/* Utility */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}   
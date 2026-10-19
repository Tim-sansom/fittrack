/* eslint-env browser */
'use strict';

/**
 * File: community.js
 * Location: /public/js/community.js
 *
 * Purpose:
 * Handles asynchronous interactions for the Community page, specifically
 * the Kudos (like) toggle system.
 *
 * Architectural Pattern: Optimistic UI
 * Instead of waiting for the server to respond before updating the interface,
 * the DOM is mutated immediately. This provides zero-latency feedback to the user.
 * If the server request fails, a rollback mechanism reverts the UI to its previous state.
 */

/**
 * Toggles the kudos state for a specific activity log.
 * @param {number} activityId - The primary key of the activity log.
 * @param {HTMLElement} btnElement - The button element that was clicked.
 */
window.toggleKudos = function (activityId, btnElement) {
    const countSpan = btnElement.querySelector('.kudos-count');
    const svgIcon = btnElement.querySelector('svg');
    let currentCount = parseInt(countSpan.textContent, 10);
    const isActive = btnElement.classList.contains('active');

    // 1. Optimistic UI Update: Mutate DOM immediately
    if (isActive) {
        currentCount--;
        btnElement.classList.remove('active');
        svgIcon.setAttribute('fill', 'none'); // Remove solid fill
    } else {
        currentCount++;
        btnElement.classList.add('active');
        svgIcon.setAttribute('fill', 'currentColor'); // Apply solid fill
    }
    countSpan.textContent = currentCount;

    // 2. Asynchronous Server Request
    // FIXED: Removed leading slash and added '../' to go up from js/ to public/
    fetch('../api/kudos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ activity_log_id: activityId })
    })
    .then(function (res) {
        return res.json();
    })
    .then(function (data) {
        // 3. Rollback mechanism: If server rejects, revert the optimistic update
        if (!data.success) {
            countSpan.textContent = isActive ? currentCount + 1 : currentCount - 1;
            if (isActive) {
                btnElement.classList.add('active');
                svgIcon.setAttribute('fill', 'currentColor');
            } else {
                btnElement.classList.remove('active');
                svgIcon.setAttribute('fill', 'none');
            }
        }
    })
    .catch(function (err) {
        console.error('Kudos synchronization error:', err);
        // Note: In a production app, we would trigger a full rollback here as well.
    });
};
/* global document, window */
'use strict';

/**
 * File: tooltips.js
 * Location: /js/tooltips.js
 * 
 * Purpose:
 * Global tooltip handler using Event Delegation.
 * Automatically handles hover and focus tooltips for any HTML element 
 * containing a 'data-tooltip' attribute, even if added dynamically via SPA.
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Create the tooltip element and append it to the body
    const tooltip = document.createElement('div');
    tooltip.className = 'global-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    document.body.appendChild(tooltip);

    let activeTooltipTarget = null;

    // 2. Event Delegation for Mouse Events (Works on dynamically added elements)
    document.addEventListener('mouseover', function (e) {
        const target = e.target.closest('[data-tooltip]');
        if (target && target !== activeTooltipTarget) {
            activeTooltipTarget = target;
            const text = target.getAttribute('data-tooltip');
            if (text) {
                tooltip.textContent = text;
                tooltip.classList.add('visible');
                positionTooltip(target);
            }
        }
    });

    document.addEventListener('mouseout', function (e) {
        const target = e.target.closest('[data-tooltip]');
        if (target && activeTooltipTarget === target) {
            if (!e.relatedTarget || !target.contains(e.relatedTarget)) {
                tooltip.classList.remove('visible');
                activeTooltipTarget = null;
            }
        }
    });

    // 3. Event Delegation for Keyboard Focus (Accessibility)
    document.addEventListener('focus', function (e) {
        const target = e.target.closest('[data-tooltip]');
        if (target) {
            activeTooltipTarget = target;
            const text = target.getAttribute('data-tooltip');
            if (text) {
                tooltip.textContent = text;
                tooltip.classList.add('visible');
                positionTooltip(target);
            }
        }
    }, true);

    document.addEventListener('blur', function (e) {
        const target = e.target.closest('[data-tooltip]');
        if (target && activeTooltipTarget === target) {
            tooltip.classList.remove('visible');
            activeTooltipTarget = null;
        }
    }, true);

    function positionTooltip(target) {
        const rect = target.getBoundingClientRect();
        const tooltipWidth = tooltip.offsetWidth || 250;
        const tooltipHeight = tooltip.offsetHeight || 40;

        let top = rect.bottom + 8; 
        let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);

        if (left < 10) left = 10;
        if (left + tooltipWidth > window.innerWidth - 10) {
            left = window.innerWidth - tooltipWidth - 10;
        }
        
        if (top + tooltipHeight > window.innerHeight - 10) {
            top = rect.top - tooltipHeight - 8;
        }

        tooltip.style.top = (top + window.scrollY) + 'px';
        tooltip.style.left = (left + window.scrollX) + 'px';
    }
});
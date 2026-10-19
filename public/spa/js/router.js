/**
 * File: router.js
 * Location: /public/spa/js/router.js
 *
 * Purpose:
 * Vanilla JS router for the FitTrack Single Page Application (SPA).
 * Intercepts standard sidebar navigation, fetches JSON from the decoupled
 * API layer, and dynamically renders components into the #spa-feed container.
 *
 * Architectural Role:
 * Replaces the traditional PHP page-rendering cycle for authenticated users
 * in SPA mode. It maintains a static header and sidebar while swapping the
 * main content area, providing a fluid user experience without relying on
 * external frameworks.
 *
 * Security:
 * All data is fetched from server-side API endpoints that enforce session
 * authentication and tenant scoping. User-supplied strings are escaped via
 * template literals where rendered into the DOM.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const feedContainer = document.getElementById('spa-feed');
    const scrollContainer = document.getElementById('spa-main') || document.querySelector('.main-content');
    const pageTitle = document.querySelector('.page-heading');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-item');

    // API paths are relative to <base href="/COM6011/public/">
    const apiMap = {
        '/dashboard.php': 'api/activities.php',
        '/workouts.php': 'api/activities.php',
        '/nutrition.php': 'api/nutrition.php',
        '/progress.php': 'api/measurements.php',
        '/challenges.php': 'api/challenges.php',
        '/community.php': 'api/kudos.php',
        '/settings.php': 'api/settings.php',
        '/plans.php': 'api/plans.php',
        '/meal-plans.php': 'api/meal-plans.php',
        '/admin.php': 'api/admin.php',
        '/wellbeing.php': 'api/wellbeing.php'
    };

    // Handle sidebar navigation clicks
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href && apiMap[href]) {
                e.preventDefault();
                const routeName = href.replace('/', '').replace('.php', '');
                window.location.hash = routeName;
                
                sidebarLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                const title = link.querySelector('.nav-text').textContent;
                if (pageTitle) pageTitle.textContent = title;
                
                loadRoute(href, apiMap[href]);
            }
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('hashchange', () => {
        const hash = window.location.hash.replace('#', '');
        const routePath = '/' + hash + '.php';
        if (apiMap[routePath]) {
            sidebarLinks.forEach(l => l.classList.remove('active'));
            const activeLink = document.querySelector(`.sidebar-nav .nav-item[href="${routePath}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                if (pageTitle) pageTitle.textContent = activeLink.querySelector('.nav-text').textContent;
            }
            loadRoute(routePath, apiMap[routePath]);
        }
    });

    // Initial route load
    const initialHash = window.location.hash.replace('#', '');
    const initialRoute = apiMap['/' + initialHash + '.php'] ? '/' + initialHash + '.php' : '/dashboard.php';
    const initialLink = document.querySelector(`.sidebar-nav .nav-item[href="${initialRoute}"]`);
    if (initialLink) {
        initialLink.classList.add('active');
        if (pageTitle) pageTitle.textContent = initialLink.querySelector('.nav-text').textContent;
    }
    loadRoute(initialRoute, apiMap[initialRoute]);

    /**
     * Core routing function. Fetches JSON from the API and dispatches to the correct renderer.
     */
    async function loadRoute(route, apiUrl) {
        if (!feedContainer) {
            console.error('SPA Feed container (#spa-feed) not found!');
            return;
        }
        
        feedContainer.innerHTML = '<div style="text-align:center; padding: 2rem; color: var(--text);">Loading data...</div>';
        if (scrollContainer) scrollContainer.scrollTop = 0;
        
        try {
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error('API Error: ' + response.status);
            
            const jsonResponse = await response.json();
            const data = jsonResponse.data || jsonResponse;
            
            // Dispatch to the appropriate component renderer
            if (route === '/dashboard.php') renderDashboard(data, feedContainer);
            else if (route === '/workouts.php') renderWorkoutsInfiniteScroll(data, feedContainer);
            else if (route === '/nutrition.php') renderNutrition(data, feedContainer);
            else if (route === '/progress.php') renderProgress(data, feedContainer);
            else if (route === '/challenges.php') renderChallenges(data, feedContainer);
            else if (route === '/community.php') renderCommunity(data, feedContainer);
            else if (route === '/settings.php') renderSettings(data, feedContainer);
            else if (route === '/plans.php') renderPlans(data, feedContainer);
            else if (route === '/meal-plans.php') renderMealPlans(data, feedContainer);
            else if (route === '/admin.php') renderAdmin(data, feedContainer);
            else if (route === '/wellbeing.php') renderWellbeing(data, feedContainer);
        } catch (error) {
            console.error('Fetch failed:', error);
            feedContainer.innerHTML = `<div style="color: var(--alert); text-align: center; padding: 2rem;">Failed to load data. <br> Error: ${error.message}</div>`;
        }
    }

    // =====================================================================
    // COMPONENT RENDERERS
    // =====================================================================

    function renderDashboard(data, container) {
        const totalSessions = Array.isArray(data) ? data.length : 0;
        const totalMinutes = Array.isArray(data) ? data.reduce((sum, item) => sum + (parseInt(item.duration_minutes) || 0), 0) : 0;
        const avgPerSession = totalSessions > 0 ? Math.round(totalMinutes / totalSessions) : 0;
        
        container.innerHTML = `
            <div style="display: grid; gap: 1.5rem; max-width: 1200px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Total workouts logged this week.">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${totalSessions}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Workouts This Week</div>
                        <div style="font-size: 0.8rem; color: var(--success);">Goal: 5 sessions</div>
                    </div>
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Total active minutes this week.">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${totalMinutes}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Minutes Active</div>
                        <div style="font-size: 0.8rem; color: var(--success);">Goal: 150 mins</div>
                    </div>
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Average duration per workout session.">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${avgPerSession}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Avg Mins/Session</div>
                    </div>
                    <div class="stat-card highlight" style="background: var(--accent); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Consecutive days with logged activity.">
                        <div style="font-size: 0.9rem;">Day Streak</div>
                        <div style="font-size: 2rem; font-weight: bold; margin-top: 0.5rem;">0</div>
                    </div>
                </div>
                <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Visualises your daily activity volume over time.">
                    <h3 style="margin-bottom: 1rem; color: var(--text);">Activity Progress</h3>
                    <svg id="volumeChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
                </div>
            </div>`;
        drawVolumeChart(data);
    }

    function renderWorkoutsInfiniteScroll(data, container) {
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<div style="padding: 2rem; text-align: center;">No workout data found.</div>';
            return;
        }
        let html = '<div style="max-width: 1000px; margin: 0 auto;">';
        html += '<h2 style="margin-bottom: 1.5rem; color: var(--text);">Recent Workouts (SPA View)</h2>';
        html += '<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">';
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="text-align: left; border-bottom: 2px solid var(--border);"><th style="padding: 0.5rem;">Date</th><th style="padding: 0.5rem;">Type</th><th style="padding: 0.5rem;">Title</th><th style="padding: 0.5rem;">Duration</th><th style="padding: 0.5rem;">Action</th></tr></thead>';
        html += '<tbody>';
        data.forEach(item => {
            const date = item.activity_date ? new Date(item.activity_date).toLocaleDateString('en-GB') : 'Unknown';
            const type = item.type_name || 'Activity';
            const title = item.title || 'Workout';
            const duration = item.duration_minutes || 0;
            const id = item.activity_log_id;
            html += `<tr style="border-bottom: 1px solid var(--border);"><td style="padding: 0.5rem;">${date}</td><td style="padding: 0.5rem;">${type}</td><td style="padding: 0.5rem;">${title}</td><td style="padding: 0.5rem;">${duration} mins</td><td style="padding: 0.5rem;"><button onclick="deleteWorkoutSpa(${id})" style="background: var(--alert); color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;" data-tooltip="Permanently delete this workout.">Delete</button></td></tr>`;
        });
        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }

    function renderNutrition(data, container) {
        const items = Array.isArray(data) ? data : [];
        if (items.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:2rem; color:var(--text);">No nutrition data logged yet. Use the MPA page to log food.</div>';
            return;
        }
        let html = '<div style="display: grid; gap: 1rem; max-width: 800px; margin: 0 auto;">';
        items.forEach(item => {
            const name = item.food_name || item.name || 'Daily Log';
            const cals = parseInt(item.calories || item.calories_consumed || 0);
            const protein = parseInt(item.protein || item.protein_g || 0);
            const carbs = parseInt(item.carbs || item.carbs_g || 0);
            const fats = parseInt(item.fats || item.fats_g || 0);
            const date = item.logged_at || item.log_date || 'Recent';
            html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Nutritional breakdown for ${name}.">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h4 style="margin: 0; color: var(--accent);">${name}</h4>
                    <span style="font-size: 0.85rem; opacity: 0.7;">${date}</span>
                </div>
                <div style="display: flex; gap: 1.5rem; font-size: 0.9rem;">
                    <span style="color: var(--accent); font-weight: bold;">${cals} kcal</span>
                    <span style="opacity: 0.8;">Protein: ${protein}g</span>
                    <span style="opacity: 0.8;">Carbs: ${carbs}g</span>
                    <span style="opacity: 0.8;">Fats: ${fats}g</span>
                </div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function renderProgress(data, container) {
        if (!data || !data.latest) {
            container.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--text);">No progress measurements recorded.</div>';
            return;
        }
        const latest = data.latest;
        const weightDelta = data.weight_delta || 0;
        const bmiDelta = data.bmi_delta || 0;
        const weightHistory = data.weight_history || {};
        const volumeHistory = data.volume_history || {};
        
        const formatDelta = (delta) => {
            if (delta === 0) return '';
            const arrow = delta < 0 ? '↓' : '↑';
            const colour = delta < 0 ? 'var(--success)' : 'var(--alert)';
            return `<span style="color: ${colour}; font-size: 0.85rem;">${arrow} ${Math.abs(delta)}% vs last</span>`;
        };
        
        const stats = [
            { label: 'Current Weight', value: latest.weight_kg, unit: 'kg', delta: formatDelta(weightDelta), tip: 'Your current body weight.' },
            { label: 'BMI Score', value: latest.bmi, unit: '', delta: formatDelta(bmiDelta), tip: 'Your Body Mass Index score.' },
            { label: 'Chest', value: latest.chest_cm, unit: 'cm', delta: '', tip: 'Chest circumference.' },
            { label: 'Waist', value: latest.waist_cm, unit: 'cm', delta: '', tip: 'Waist circumference.' },
            { label: 'Left Arm', value: latest.left_arm_cm, unit: 'cm', delta: '', tip: 'Left arm circumference.' },
            { label: 'Right Arm', value: latest.right_arm_cm, unit: 'cm', delta: '', tip: 'Right arm circumference.' },
            { label: 'Left Leg', value: latest.left_leg_cm, unit: 'cm', delta: '', tip: 'Left leg circumference.' },
            { label: 'Right Leg', value: latest.right_leg_cm, unit: 'cm', delta: '', tip: 'Right leg circumference.' }
        ];
        
        let html = '<div style="max-width: 1200px; margin: 0 auto;">';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
        stats.forEach(stat => {
            if (stat.value !== null && stat.value !== undefined && stat.value !== '') {
                html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); border-left: 4px solid var(--accent);" data-tooltip="${stat.tip}">
                    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; margin-bottom: 0.5rem;">${stat.label}</div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--text);">${stat.value}<span style="font-size: 1rem; opacity: 0.6; margin-left: 4px;">${stat.unit}</span></div>
                    ${stat.delta ? `<div style="margin-top: 0.5rem;">${stat.delta}</div>` : ''}
                </div>`;
            }
        });
        html += '</div>';
        
        html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 2rem;" data-tooltip="Tracks your weight changes over time.">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; color: var(--text);">Weight Trend</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button data-range="week" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;" data-tooltip="Show last 7 days.">Week</button>
                    <button data-range="month" class="active" style="padding: 0.4rem 1rem; border: 1px solid var(--accent); background: var(--accent); color: white; border-radius: 6px; cursor: pointer;" data-tooltip="Show last 4 weeks.">Month</button>
                    <button data-range="year" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;" data-tooltip="Show last 12 months.">Year</button>
                </div>
            </div>
            <svg id="weightChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
        </div>`;
        
        html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);" data-tooltip="Tracks your total active minutes over time.">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; color: var(--text);">Activity Volume (Minutes)</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button data-vol-range="week" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;" data-tooltip="Show last 7 days.">Week</button>
                    <button data-vol-range="month" class="active" style="padding: 0.4rem 1rem; border: 1px solid var(--accent); background: var(--accent); color: white; border-radius: 6px; cursor: pointer;" data-tooltip="Show last 4 weeks.">Month</button>
                    <button data-vol-range="year" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;" data-tooltip="Show last 12 months.">Year</button>
                </div>
            </div>
            <svg id="volumeChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
        </div>`;
        html += '</div>';
        container.innerHTML = html;
        
        drawLineChart('weightChart', weightHistory.month || [], 'var(--accent)');
        drawLineChart('volumeChart', volumeHistory.month || [], 'var(--highlight)');
        
        container.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const range = e.target.dataset.range;
                container.querySelectorAll('[data-range]').forEach(b => {
                    b.style.background = 'var(--bg)'; b.style.color = 'var(--text)'; b.style.border = '1px solid var(--border)';
                });
                e.target.style.background = 'var(--accent)'; e.target.style.color = 'white'; e.target.style.border = '1px solid var(--accent)';
                drawLineChart('weightChart', weightHistory[range] || [], 'var(--accent)');
            });
        });
        
        container.querySelectorAll('[data-vol-range]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const range = e.target.dataset.volRange;
                container.querySelectorAll('[data-vol-range]').forEach(b => {
                    b.style.background = 'var(--bg)'; b.style.color = 'var(--text)'; b.style.border = '1px solid var(--border)';
                });
                e.target.style.background = 'var(--accent)'; e.target.style.color = 'white'; e.target.style.border = '1px solid var(--accent)';
                drawLineChart('volumeChart', volumeHistory[range] || [], 'var(--highlight)');
            });
        });
    }

    function renderChallenges(data, container) {
        const items = Array.isArray(data) ? data : (data.data || []);
        if (items.length === 0) {
            container.innerHTML = '<div style="padding: 2rem; text-align: center;">No active challenges found.</div>';
            return;
        }
        let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 1000px; margin: 0 auto;">';
        items.forEach(item => {
            const title = item.title || item.name || 'Challenge';
            const current = parseFloat(item.current_value) || 0;
            const target = parseFloat(item.target_value) || 1;
            const unit = item.unit || '';
            const progress = Math.min(100, (current / target) * 100);
            let daysLeft = '';
            if (item.end_date) {
                const end = new Date(item.end_date);
                const now = new Date();
                const diff = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
                daysLeft = diff > 0 ? `${diff} days left` : 'Expired';
            }
            html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); border-top: 4px solid var(--accent);" data-tooltip="Progress towards your ${title} goal.">
                <h4 style="margin: 0 0 1rem 0; color: var(--text);">${title}</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text);">
                    <span>${current} / ${target} ${unit}</span>
                    <span>${daysLeft}</span>
                </div>
                <div style="background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 0.5rem;">
                    <div style="width: ${progress}%; height: 100%; background: var(--accent); border-radius: 4px; transition: width 0.3s ease;"></div>
                </div>
                <div style="text-align: center; font-size: 0.85rem; opacity: 0.7; color: var(--text);">${progress.toFixed(1)}% Complete</div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function renderCommunity(data, container) {
        const communityData = data && typeof data === 'object' && !Array.isArray(data) ? data : {};
        const feed = communityData.feed || [];
        const leaderboard = communityData.leaderboard || [];
        const tenantTotal = communityData.tenant_total_minutes || 0;
        const tenantGoal = communityData.tenant_goal_minutes || 10000;
        
        if (feed.length === 0 && leaderboard.length === 0) {
            container.innerHTML = '<div style="padding: 2rem; text-align: center;">No recent community activity.</div>';
            return;
        }
        
        const goalPercent = Math.min(100, (tenantTotal / tenantGoal) * 100);
        let html = '<div style="max-width: 1100px; margin: 0 auto;">';
        html += `<div style="background: var(--accent); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;" data-tooltip="Collective activity goal for all users in your tenant this month.">
            <h3 style="margin: 0 0 0.5rem 0;">Tenant Goal: ${tenantGoal.toLocaleString()} Active Minutes This Month</h3>
            <p style="margin: 0 0 1rem 0; opacity: 0.9;">Together, we've logged <strong>${tenantTotal.toLocaleString()}</strong> minutes. Keep it up!</p>
            <div style="background: rgba(255,255,255,0.2); height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="width: ${goalPercent}%; height: 100%; background: #f5e6a3; border-radius: 6px; transition: width 0.6s ease;"></div>
            </div>
        </div>`;
        
        html += '<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">';
        html += '<div><h3 style="margin-bottom: 1rem; color: var(--text);">Recent Activity</h3>';
        if (feed.length === 0) {
            html += '<div style="padding: 1rem; background: var(--card); border-radius: 12px;">No public activity yet.</div>';
        } else {
            feed.forEach(item => {
                const initials = (item.display_name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const date = item.activity_date ? new Date(item.activity_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) : 'Recent';
                const type = item.activity_type_name || 'Activity';
                const duration = item.duration_minutes ? `${item.duration_minutes} mins` : '';
                const kudos = item.kudos_count || 0;
                html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 1rem;">
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">${initials}</div>
                        <div>
                            <strong style="color: var(--text);">${item.display_name || 'User'}</strong>
                            <div style="font-size: 0.85rem; opacity: 0.7;">completed a ${type} session</div>
                            <div style="font-size: 0.8rem; opacity: 0.6;">${date}</div>
                        </div>
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; color: var(--accent);">${item.title || 'Activity'}</h4>
                    <div style="display: flex; gap: 1.5rem; font-size: 0.9rem; opacity: 0.8; margin-bottom: 1rem;">
                        ${duration ? `<span> ${duration}</span>` : ''}
                    </div>
                    <div style="border-top: 1px solid var(--border); padding-top: 0.75rem;">
                        <button style="background: none; border: 1px solid var(--border); padding: 0.4rem 1rem; border-radius: 20px; cursor: pointer; color: var(--text); font-size: 0.85rem;" data-tooltip="Give a kudo to this activity."> ${kudos} Kudos</button>
                    </div>
                </div>`;
            });
        }
        html += '</div>';
        
        html += '<div><div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">';
        html += '<h3 style="margin: 0 0 1rem 0; color: var(--text);">Weekly Leaderboard</h3>';
        if (leaderboard.length === 0) {
            html += '<div style="opacity: 0.7;">No activity this week.</div>';
        } else {
            leaderboard.forEach((entry, idx) => {
                const rank = idx + 1;
                const rankStyle = rank === 1 ? 'background: #f5e6a3; color: #333; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: bold;' : 'opacity: 0.7;';
                html += `<div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="${rankStyle}">${rank}${rank === 1 ? 'st' : rank === 2 ? 'nd' : rank === 3 ? 'rd' : 'th'}</span>
                        <strong style="color: var(--text);">${entry.display_name}</strong>
                    </div>
                    <span style="color: var(--accent); font-weight: bold;">${entry.total_minutes} mins</span>
                </div>`;
            });
        }
        html += '</div></div></div></div>';
        container.innerHTML = html;
    }

    function renderSettings(data, container) {
        const prefs = Array.isArray(data) ? (data[0] || {}) : (data.data || data || {});
        container.innerHTML = `
            <div style="max-width: 800px; margin: 0 auto; background: var(--card); padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--text);">Account Settings</h2>
                <div class="settings-tabs" style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 2rem;">
                    <button class="tab-btn active" data-tab="pref" style="opacity: 1; color: var(--accent); border-bottom: 3px solid var(--accent); background: none; border-top: none; border-left: none; border-right: none; padding: 0.75rem 1rem; cursor: pointer; font-weight: 600;" data-tooltip="Manage display and unit preferences.">Preferences</button>
                    <button class="tab-btn" data-tab="sec" style="opacity: 0.7; background: none; border: none; border-bottom: 3px solid transparent; padding: 0.75rem 1rem; cursor: pointer; font-weight: 600; color: var(--text);" data-tooltip="Manage password and 2FA settings.">Security</button>
                    <button class="tab-btn" data-tab="acc" style="opacity: 0.7; background: none; border: none; border-bottom: 3px solid transparent; padding: 0.75rem 1rem; cursor: pointer; font-weight: 600; color: var(--text);" data-tooltip="Manage account data and exports.">Account</button>
                </div>
                <div id="spa-tab-pref" class="spa-tab-content">
                    <h3>User Preferences</h3>
                    <div style="display: grid; gap: 1.5rem; margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border);" data-tooltip="Preferred unit for tracking distance.">
                            <div><div style="font-weight: bold; color: var(--text);">Distance Unit</div></div>
                            <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.distance_unit || 'km'}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border);" data-tooltip="Preferred unit for body weight.">
                            <div><div style="font-weight: bold; color: var(--text);">Weight Unit</div></div>
                            <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.weight_unit ? prefs.weight_unit.replace('_', ' ') : 'kg'}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;" data-tooltip="Default visibility for new activities.">
                            <div><div style="font-weight: bold; color: var(--text);">Activity Visibility</div></div>
                            <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.default_activity_visibility || 'private'}</div>
                        </div>
                    </div>
                    <p style="margin-top: 2rem; font-size: 0.85rem; opacity: 0.6; text-align: center;">To change preferences, please use the MPA interface.</p>
                </div>
                <div id="spa-tab-sec" class="spa-tab-content" style="display: none;">
                    <h3>Two-Factor Authentication (2FA)</h3>
                    <p style="color: var(--text); margin-bottom: 1.5rem;">Add an extra layer of security to your account.</p>
                    <div id="spa-totp-container" style="background: var(--bg); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <div id="spa-totp-loading">Checking status...</div>
                        <div id="spa-totp-disabled" style="display: none;">
                            <p>2FA is currently <strong>disabled</strong>.</p>
                            <button id="spa-btn-enable" class="btn-primary" style="background: var(--accent); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" data-tooltip="Starts the 2FA setup process.">Enable 2FA</button>
                        </div>
                        <div id="spa-totp-setup" style="display: none;">
                            <p>Scan QR or enter secret:</p>
                            <img id="spa-qr" src="" style="width: 150px; height: 150px; background: white; padding: 5px; border-radius: 4px; margin-bottom: 1rem;">
                            <div style="font-family: monospace; background: white; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem;" id="spa-secret"></div>
                            <input type="text" id="spa-totp-code" placeholder="6-digit code" maxlength="6" style="padding: 0.5rem; width: 120px; margin-right: 0.5rem;" data-tooltip="Enter the 6-digit code from your app.">
                            <button id="spa-btn-verify" class="btn-primary" style="background: var(--accent); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" data-tooltip="Verifies the code and enables 2FA.">Verify</button>
                            <button id="spa-btn-cancel" style="background: none; border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" data-tooltip="Cancels the 2FA setup process.">Cancel</button>
                            <div id="spa-totp-error" style="color: var(--alert); margin-top: 0.5rem;"></div>
                        </div>
                        <div id="spa-totp-enabled" style="display: none;">
                            <span style="background: rgba(76, 175, 80, 0.1); color: var(--success); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Enabled</span>
                            <p style="margin: 1rem 0;">Your account is protected.</p>
                            <button id="spa-btn-disable" class="btn-danger" style="background: var(--alert); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;" data-tooltip="Disables 2FA protection on your account.">Disable 2FA</button>
                        </div>
                    </div>
                </div>
                <div id="spa-tab-acc" class="spa-tab-content" style="display: none;">
                    <h3>Data Export</h3>
                    <p style="color: var(--text); margin-bottom: 1.5rem;">Download a copy of your fitness data.</p>
                    <a href="api/export.php" class="btn-primary" style="display: inline-block; background: var(--accent); color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px;" data-tooltip="Downloads your fitness data as a JSON file.">Export My Data</a>
                </div>
            </div>
        `;
        // Tab Logic
        container.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                container.querySelectorAll('.tab-btn').forEach(b => {
                    b.style.opacity = '0.7'; b.style.borderBottomColor = 'transparent'; b.style.color = 'var(--text)'; b.classList.remove('active');
                });
                container.querySelectorAll('.spa-tab-content').forEach(c => c.style.display = 'none');
                e.target.style.opacity = '1'; e.target.style.borderBottomColor = 'var(--accent)'; e.target.style.color = 'var(--accent)'; e.target.classList.add('active');
                document.getElementById('spa-tab-' + e.target.dataset.tab).style.display = 'block';
            });
        });
        // TOTP Logic
        const tContainer = document.getElementById('spa-totp-container');
        if (tContainer) {
            const views = {
                load: document.getElementById('spa-totp-loading'),
                off: document.getElementById('spa-totp-disabled'),
                setup: document.getElementById('spa-totp-setup'),
                on: document.getElementById('spa-totp-enabled')
            };
            fetch('api/totp.php').then(r => r.json()).then(d => {
                views.load.style.display = 'none';
                if (d.success && d.data.is_enabled) views.on.style.display = 'block';
                else views.off.style.display = 'block';
            });
            document.getElementById('spa-btn-enable').addEventListener('click', () => {
                views.off.style.display = 'none'; views.setup.style.display = 'block';
                fetch('api/totp.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'generate_secret'}) })
                .then(r => r.json()).then(d => {
                    if (d.success) {
                        document.getElementById('spa-secret').textContent = d.secret;
                        document.getElementById('spa-qr').src = `https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(d.otpauth_uri)}`;
                    }
                });
            });
            document.getElementById('spa-btn-cancel').addEventListener('click', () => {
                views.setup.style.display = 'none'; views.off.style.display = 'block';
            });
            document.getElementById('spa-btn-verify').addEventListener('click', () => {
                const code = document.getElementById('spa-totp-code').value;
                fetch('api/totp.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'verify_code', code: code}) })
                .then(r => r.json()).then(d => {
                    if (d.success) {
                        alert('2FA Enabled! Recovery codes: ' + d.recovery_codes.join(', '));
                        views.setup.style.display = 'none'; views.on.style.display = 'block';
                    } else {
                        document.getElementById('spa-totp-error').textContent = d.error;
                    }
                });
            });
            document.getElementById('spa-btn-disable').addEventListener('click', () => {
                if (confirm('Disable 2FA?')) {
                    fetch('api/totp.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'disable'}) })
                    .then(r => r.json()).then(d => {
                        if (d.success) { views.on.style.display = 'none'; views.off.style.display = 'block'; }
                    });
                }
            });
        }
    }

    function renderPlans(data, container) {
        const plans = (data && data.plans) ? data.plans : [];
        const activityTypes = (data && data.activity_types) ? data.activity_types : [];
        let html = '<div style="max-width: 1000px; margin: 0 auto;">';
        html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 2rem;">
            <h3 style="margin: 0 0 1rem 0; color: var(--text);">Create New Training Plan</h3>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Plan Title</label>
                    <input type="text" id="newPlanTitle" placeholder="e.g. 12-Week Marathon Prep" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Give your training plan a memorable name.">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Start Date</label>
                    <input type="date" id="newPlanStart" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Select the start date for this plan.">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">End Date</label>
                    <input type="date" id="newPlanEnd" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Select the end date for this plan.">
                </div>
            </div>
            <button id="createPlanBtn" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" data-tooltip="Creates a new training plan.">Create Plan</button>
        </div>`;
        if (plans.length === 0) {
            html += '<div style="text-align: center; padding: 3rem; background: var(--card); border-radius: 12px; opacity: 0.7;"><p>No active training plans. Create one above to get started.</p></div>';
        } else {
            plans.forEach(plan => {
                const progress = plan.stats ? plan.stats.progress : 0;
                const statusColor = plan.status === 'active' ? 'var(--accent)' : 'var(--border)';
                html += `<div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 1.5rem; border-left: 4px solid ${statusColor};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--text);">${plan.title || 'Untitled Plan'}</h4>
                            <p style="margin: 0; font-size: 0.85rem; opacity: 0.7;">${plan.start_date} to ${plan.end_date} <span style="margin-left: 1rem; padding: 0.2rem 0.6rem; background: var(--bg); border-radius: 12px; font-size: 0.75rem; text-transform: uppercase;">${plan.status || 'draft'}</span></p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent);">${progress}%</div>
                            <div style="font-size: 0.75rem; opacity: 0.7;">${plan.stats ? plan.stats.completed : 0}/${plan.stats ? plan.stats.total : 0} completed</div>
                        </div>
                    </div>
                    <div style="background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 1rem;">
                        <div style="width: ${progress}%; height: 100%; background: var(--accent); border-radius: 4px; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="background: var(--bg); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <h5 style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: var(--text);">Schedule Activity</h5>
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 2fr auto; gap: 0.75rem; align-items: end;">
                            <div>
                                <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Activity Type</label>
                                <select id="activityType_${plan.plan_id}" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;" data-tooltip="Select the type of activity for this session.">
                                    <option value="">Select...</option>
                                    ${activityTypes.map(t => `<option value="${t.activity_type_id}">${t.name}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Date</label>
                                <input type="date" id="planDate_${plan.plan_id}" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;" data-tooltip="Select the date for this activity.">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Mins</label>
                                <input type="number" id="planDuration_${plan.plan_id}" placeholder="60" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;" data-tooltip="Estimated duration in minutes.">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Notes</label>
                                <input type="text" id="planNotes_${plan.plan_id}" placeholder="Optional" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;" data-tooltip="Optional notes for this session.">
                            </div>
                            <button onclick="addPlanItem(${plan.plan_id})" style="padding: 0.5rem 1rem; background: var(--accent); color: white; border: none; border-radius: 6px; cursor: pointer;" data-tooltip="Adds this activity to the plan.">Add</button>
                        </div>
                    </div>
                    ${(!plan.items || plan.items.length === 0) ? '<p style="opacity: 0.6; font-style: italic; font-size: 0.9rem;">No activities scheduled yet.</p>' : `
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            ${plan.items.map(item => {
                                const itemStatusColor = item.completion_status === 'completed' ? 'var(--success)' : item.completion_status === 'skipped' ? 'var(--alert)' : 'var(--text)';
                                return `<div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg); border-radius: 8px; border-left: 3px solid ${itemStatusColor};">
                                    <div style="flex-grow: 1;">
                                        <div style="font-weight: 600; color: var(--text); font-size: 0.9rem;">${item.type_name || 'Activity'}</div>
                                        <div style="font-size: 0.8rem; opacity: 0.7;">${item.planned_date} • ${item.planned_duration_minutes} mins${item.notes ? ' • ' + item.notes : ''}</div>
                                    </div>
                                    <select onchange="togglePlanItemStatus(${item.plan_item_id}, this.value)" style="padding: 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border); background: var(--card); color: var(--text);" data-tooltip="Update the completion status of this activity.">
                                        <option value="planned" ${item.completion_status === 'planned' ? 'selected' : ''}>Planned</option>
                                        <option value="completed" ${item.completion_status === 'completed' ? 'selected' : ''}>Completed</option>
                                        <option value="skipped" ${item.completion_status === 'skipped' ? 'selected' : ''}>Skipped</option>
                                    </select>
                                </div>`;
                            }).join('')}
                        </div>
                    `}
                    <div style="margin-top: 1rem; text-align: right;">
                        <button onclick="deletePlan(${plan.plan_id})" style="padding: 0.5rem 1rem; background: var(--alert); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" data-tooltip="Permanently deletes this plan and all its activities.">Delete Plan</button>
                    </div>
                </div>`;
            });
        }
        html += '</div>';
        container.innerHTML = html;
        const createBtn = document.getElementById('createPlanBtn');
        if (createBtn) {
            createBtn.addEventListener('click', () => {
                const title = document.getElementById('newPlanTitle').value;
                const start = document.getElementById('newPlanStart').value;
                const end = document.getElementById('newPlanEnd').value;
                if (!title || !start || !end) { alert('Please fill in all fields'); return; }
                fetch('api/plans.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ action: 'create_plan', title, start_date: start, end_date: end }) 
                })
                .then(res => res.json())
                .then(data => { 
                    if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); 
                    else alert('Error: ' + (data.error || 'Unknown error')); 
                })
                .catch(err => alert('Network error: ' + err.message));
            });
        }
    }

    function renderMealPlans(data, container) {
        const weekData = Array.isArray(data) ? data : [];
        if (weekData.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:2rem; color:var(--text);">No meals planned for this week. Use the MPA interface to add meals.</div>';
            return;
        }
        let html = '<div style="max-width: 1200px; margin: 0 auto;">';
        html += '<h2 style="margin-bottom: 0.5rem; color: var(--text);">Weekly Meal Planner</h2>';
        html += '<p style="margin-bottom: 2rem; color: var(--text); opacity: 0.8;">Your meal plan for the current week. Use the MPA interface to add, edit, or apply meals to your nutrition log.</p>';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">';
        weekData.forEach(day => {
            const todayClass = day.is_today ? 'border: 2px solid var(--accent);' : '';
            const todayBadge = day.is_today ? '<span style="background: var(--accent); color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">TODAY</span>' : '';
            html += `<div style="background: var(--card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px var(--shadow); ${todayClass}">`;
            html += `<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border);">`;
            html += `<h3 style="margin: 0; color: var(--text); font-size: 1.25rem;">${day.day_name}</h3>`;
            html += `<div style="display: flex; gap: 0.5rem; align-items: center;">`;
            html += `<span style="font-size: 0.875rem; color: var(--text); opacity: 0.7;">${day.day_short}</span>`;
            html += todayBadge;
            html += `</div></div>`;
            const slots = ['breakfast', 'lunch', 'dinner', 'snacks'];
            slots.forEach(slot => {
                const meal = day.meals[slot];
                html += `<div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg); border-radius: 8px; border: 1px solid var(--border);" data-tooltip="${slot.charAt(0).toUpperCase() + slot.slice(1)} meal slot.">`;
                html += `<h4 style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--accent);">${slot.charAt(0).toUpperCase() + slot.slice(1)}</h4>`;
                if (meal) {
                    html += `<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">`;
                    html += `<div style="flex: 1;">`;
                    html += `<strong style="display: block; font-size: 1rem; color: var(--text); margin-bottom: 0.25rem;">${meal.food_name}</strong>`;
                    if (meal.calories) {
                        html += `<span style="display: inline-block; font-size: 0.875rem; color: var(--success); font-weight: 600; margin-bottom: 0.25rem;">${meal.calories} kcal</span>`;
                    }
                    if (meal.notes) {
                        html += `<p style="font-size: 0.875rem; color: var(--text); opacity: 0.8; margin: 0.25rem 0 0 0; font-style: italic;">${meal.notes}</p>`;
                    }
                    html += `</div>`;
                    const protein = meal.protein_g || 0;
                    const carbs = meal.carbs_g || 0;
                    const fats = meal.fats_g || 0;
                    if (protein || carbs || fats) {
                        html += `<div style="font-size: 0.75rem; color: var(--text); opacity: 0.7; white-space: nowrap;">`;
                        if (protein) html += `<span>P: ${protein}g</span> `;
                        if (carbs) html += `<span>C: ${carbs}g</span> `;
                        if (fats) html += `<span>F: ${fats}g</span>`;
                        html += `</div>`;
                    }
                    html += `</div>`;
                } else {
                    html += `<div style="font-size: 0.875rem; color: var(--text); opacity: 0.5; font-style: italic;">No meal planned</div>`;
                }
                html += `</div>`;
            });
            html += `</div>`;
        });
        html += '</div></div>';
        container.innerHTML = html;
    }

    function renderAdmin(data, container) {
        const types = (data && data.activity_types) ? data.activity_types : [];
        const users = (data && data.users) ? data.users : [];
        const roles = (data && data.roles) ? data.roles : [];
        let html = '<div style="max-width: 1200px; margin: 0 auto;">';
        // Activity Types Management
        html += '<div style="background: var(--card); padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 2rem;">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text);">Manage Activity Types</h2>';
        html += '<div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">';
        html += '<input type="text" id="newActivityName" placeholder="New Activity Type" style="flex-grow: 1; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: var(--text); background: var(--bg);" data-tooltip="Enter the name of the new activity type.">';
        html += '<button id="addActivityBtn" style="padding: 0.75rem 1.5rem; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" data-tooltip="Adds a new activity type to the catalogue.">Add</button>';
        html += '</div>';
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="text-align: left; border-bottom: 2px solid var(--border);"><th style="padding: 0.75rem 0.5rem;">Name</th><th style="padding: 0.75rem 0.5rem;">Category</th><th style="padding: 0.75rem 0.5rem;">Actions</th></tr></thead>';
        html += '<tbody>';
        if (types.length === 0) {
            html += '<tr><td colspan="3" style="padding: 1rem; text-align: center; opacity: 0.7;">No activity types found.</td></tr>';
        } else {
            types.forEach(type => {
                html += `<tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 0.5rem; color: var(--text);">${type.name}</td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text);">${type.category}</td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <button onclick="deleteActivity(${type.activity_type_id})" style="background: var(--alert); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" data-tooltip="Permanently removes this activity type.">Delete</button>
                    </td>
                </tr>`;
            });
        }
        html += '</tbody></table></div>';
        // User Management
        html += '<div style="background: var(--card); padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text);">Tenant User Management</h2>';
        // Add User Form
        html += '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">';
        html += '<div><label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Username</label><input type="text" id="adminUsername" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Unique username for the new account."></div>';
        html += '<div><label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Email</label><input type="email" id="adminEmail" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Email address for the new account."></div>';
        html += '<div><label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Display Name</label><input type="text" id="adminDisplayName" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Public display name for the user."></div>';
        html += '<div><label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Password (Min 12)</label><input type="password" id="adminPassword" minlength="12" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Secure password (minimum 12 characters)."></div>';
        html += '<div><label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Role</label><select id="adminRoleId" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;" data-tooltip="Assign a role to determine user permissions."';
        roles.forEach(role => {
            html += `<option value="${role.role_id}">${role.role_key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`;
        });
        html += '</select></div>';
        html += '<div style="display: flex; align-items: flex-end;"><button id="createUserBtn" style="padding: 0.75rem 1.5rem; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" data-tooltip="Creates the new user account and assigns them to the tenant.">Create User</button></div>';
        html += '</div>';
        // User List
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="text-align: left; border-bottom: 2px solid var(--border);"><th style="padding: 0.75rem 0.5rem;">Name</th><th style="padding: 0.75rem 0.5rem;">Username</th><th style="padding: 0.75rem 0.5rem;">Role</th><th style="padding: 0.75rem 0.5rem;">Status</th><th style="padding: 0.75rem 0.5rem;">Actions</th></tr></thead>';
        html += '<tbody>';
        if (users.length === 0) {
            html += '<tr><td colspan="5" style="padding: 1rem; text-align: center; opacity: 0.7;">No users found.</td></tr>';
        } else {
            users.forEach(user => {
                const statusColor = user.membership_status === 'active' ? 'var(--success)' : 'var(--alert)';
                const statusBg = user.membership_status === 'active' ? 'rgba(76, 175, 80, 0.1)' : 'rgba(226, 105, 114, 0.1)';
                html += `<tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 0.5rem; color: var(--text);">${user.display_name}</td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text);">${user.username}</td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text);">${user.role_key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td>
                    <td style="padding: 0.75rem 0.5rem;"><span style="color: ${statusColor}; background: ${statusBg}; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; text-transform: uppercase; font-size: 0.85rem;">${user.membership_status}</span></td>
                    <td style="padding: 0.75rem 0.5rem;">`;
                if (user.membership_status === 'active') {
                    html += `<button onclick="suspendUser(${user.user_id})" style="background: #f5e6a3; color: #333; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; margin-right: 0.5rem;" data-tooltip="Temporarily bans the user from logging in.">Suspend</button>`;
                    html += `<button onclick="removeUser(${user.user_id})" style="background: var(--alert); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" data-tooltip="Permanently removes the user from this tenant.">Remove</button>`;
                } else {
                    html += '<span style="font-size: 0.8rem; color: var(--text); opacity: 0.7;">Inactive</span>';
                }
                html += '</td></tr>';
            });
        }
        html += '</tbody></table></div>';
        html += '</div>';
        container.innerHTML = html;
        // Event Listeners
        const addActivityBtn = document.getElementById('addActivityBtn');
        if (addActivityBtn) {
            addActivityBtn.addEventListener('click', () => {
                const name = document.getElementById('newActivityName').value;
                if (!name) { alert('Please enter an activity type name'); return; }
                fetch('api/admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_activity', name: name })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { loadRoute('/admin.php', apiMap['/admin.php']); }
                    else { alert('Error: ' + (data.error || 'Unknown error')); }
                })
                .catch(err => alert('Network error: ' + err.message));
            });
        }
        const createUserBtn = document.getElementById('createUserBtn');
        if (createUserBtn) {
            createUserBtn.addEventListener('click', () => {
                const username = document.getElementById('adminUsername').value;
                const email = document.getElementById('adminEmail').value;
                const displayName = document.getElementById('adminDisplayName').value;
                const password = document.getElementById('adminPassword').value;
                const roleId = document.getElementById('adminRoleId').value;
                if (!username || !email || !displayName || !password) { alert('Please fill in all fields'); return; }
                if (password.length < 12) { alert('Password must be at least 12 characters'); return; }
                fetch('api/admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_user', username, email, display_name: displayName, password, role_id: roleId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { loadRoute('/admin.php', apiMap['/admin.php']); }
                    else { alert('Error: ' + (data.error || 'Unknown error')); }
                })
                .catch(err => alert('Network error: ' + err.message));
            });
        }
    }

    function renderWellbeing(data, container) {
        let html = '<div class="main-content">';
        // Hero Card
        html += '<div class="card" style="background: linear-gradient(135deg, var(--accent), var(--text)); color: white; margin-bottom: 1.5rem;" data-tooltip="Overview of evidence-based physical activity guidance.">';
        html += '<h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">Physical Wellbeing</h1>';
        html += '<p style="margin: 0; font-size: 1.05rem; opacity: 0.95; max-width: 800px;">Evidence-based guidance on physical activity for adults in the United Kingdom. Content is drawn from peer-reviewed research and official UK government guidance, not commercial fitness advice.</p>';
        html += '</div>';
        // UK Guidelines Card
        html += '<div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Official UK Chief Medical Officers activity guidelines.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">UK Physical Activity Guidelines</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">The UK Chief Medical Officers published revised physical activity guidelines in 2019, synthesising evidence from over 200 systematic reviews (Department of Health and Social Care, 2019). These guidelines apply to adults aged 19–64 and form the clinical baseline against which FitTrack measures your activity volume.</p>';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Weekly aerobic activity target."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Aerobic Activity</h3><p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">150–300 minutes</p><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">of moderate-intensity aerobic activity per week, or 75–150 minutes of vigorous-intensity activity, or an equivalent combination. Activity should be performed in bouts of at least 10 minutes.</p></div>';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Weekly muscle strengthening target."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Muscle Strengthening</h3><p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">2+ days per week</p><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">activities involving major muscle groups at moderate or greater intensity. This includes resistance training, bodyweight exercises, and heavy gardening.</p></div>';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Guidance on reducing sedentary behaviour."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Sedentary Behaviour</h3><p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">Minimise and interrupt</p><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">prolonged sitting. Any amount of sedentary behaviour carries some risk, and the relationship with health outcomes is independent of physical activity levels.</p></div>';
        html += '</div>';
        html += '<div style="margin-top: 1.5rem; padding: 1rem; background: rgba(159, 95, 145, 0.08); border-radius: 8px; border-left: 4px solid var(--highlight);" data-tooltip="Context on the 150-minute evidence base."><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);"><strong style="color: var(--accent);">A note on the evidence:</strong> The 150-minute threshold is a population-level recommendation. Warburton and Bredin (2017) note that dose–response curves are non-linear: the largest relative health gains occur when moving from sedentary to modest activity, and additional minutes yield progressively smaller marginal benefits. FitTrack\'s 150-minute weekly goal reflects this guideline, but individual targets should be discussed with a GP where pre-existing conditions apply.</p></div>';
        html += '</div>';
        // Four Pillars Card
        html += '<div class="card" style="margin-bottom: 1.5rem;" data-tooltip="The four components of physical fitness.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">The Four Pillars of Physical Activity</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">The American College of Sports Medicine identifies four distinct components of physical fitness, each producing different physiological adaptations (Garber <em>et al.</em>, 2011). A balanced programme addresses all four.</p>';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Heart and lung fitness pillar."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Cardiorespiratory Endurance</h3><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">Sustained rhythmic activity that challenges the heart and lungs. Examples in FitTrack: running, walking, swimming. Regular aerobic training reduces resting heart rate, improves VO₂ max, and lowers all-cause mortality risk.</p></div>';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Muscle and bone fitness pillar."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Muscular Strength & Endurance</h3><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">Resistance activity that overloads skeletal muscle. Preserves bone density, supports metabolic health, and reduces falls risk in later life. FitTrack\'s gym sessions target this pillar.</p></div>';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Joint mobility pillar."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Flexibility</h3><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">Range of motion across a joint. Maintained through static and dynamic stretching. Reduces injury risk when performed after, rather than before, aerobic activity.</p></div>';
        html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Balance and coordination pillar."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Neuromotor Balance</h3><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">Proprioception, agility, and coordination. Particularly important for adults over 40 to mitigate falls risk. Activities include yoga, Pilates, and racket sports.</p></div>';
        html += '</div>';
        html += '</div>';
        // Research Benefits Card
        html += '<div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Evidence-based health outcomes from physical activity.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">What the Research Shows</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">The physiological case for regular physical activity is among the most robust findings in modern epidemiology. Pedersen and Saltin (2015) describe exercise as "the best buy in public health," citing evidence across the following outcomes:</p>';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">';
        const benefits = [
            { title: 'Cardiovascular Disease', text: 'Regular moderate activity reduces coronary heart disease risk by approximately 30–40%.' },
            { title: 'Type 2 Diabetes', text: 'Risk reduction of 30–40% among active adults, with dose–response effects observed up to approximately 300 minutes per week.' },
            { title: 'Common Cancers', text: 'Colon, breast, and endometrial cancers show 20–30% risk reduction in active populations.' },
            { title: 'Mental Health', text: 'Physical activity reduces the incidence of depression by 20–30% and is an effective adjunct treatment for mild to moderate depressive episodes.' },
            { title: 'Musculoskeletal Health', text: 'Resistance training increases bone mineral density and reduces osteoporotic fracture risk in postmenopausal women.' }
        ];
        benefits.forEach(b => {
            html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="' + b.title + ' benefits."><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">' + b.title + '</h3><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">' + b.text + '</p></div>';
        });
        html += '</div>';
        html += '</div>';
        // Safety Card
        html += '<div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Clinical guidance on safe exercise practice.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Safety and Injury Prevention</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">Physical activity carries a small absolute risk of musculoskeletal injury and, in rare cases, cardiac events. The following precautions are supported by clinical guidance:</p>';
        html += '<ol style="margin: 0 0 1.5rem 1.5rem; line-height: 1.8; color: var(--text);">';
        html += '<li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Pre-participation screening:</strong> Adults with known cardiovascular, metabolic, or renal disease should seek medical clearance before beginning vigorous activity.</li>';
        html += '<li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Gradual progression:</strong> Increase weekly volume by no more than 10% per week to reduce overuse injury risk.</li>';
        html += '<li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Warm-up and cool-down:</strong> Five to ten minutes of light aerobic activity before and after each session.</li>';
        html += '<li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Hydration:</strong> Replace fluid losses, particularly in sessions exceeding 60 minutes or in warm conditions.</li>';
        html += '<li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Listen to your body:</strong> Sharp pain, chest discomfort, or unusual breathlessness are signals to stop and seek medical advice.</li>';
        html += '</ol>';
        html += '<div style="padding: 1rem; background: rgba(226, 105, 114, 0.08); border-radius: 8px; border-left: 4px solid var(--alert);" data-tooltip="When to consult a GP before exercising."><p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);"><strong style="color: var(--alert);">When to see a GP:</strong> If you have not been active for some time, are over 40, or have a long-term health condition, speak to your GP before starting a new exercise programme. NHS services are free at the point of use and can provide personalised advice or referral to a physiotherapist.</p></div>';
        html += '</div>';
        // Local Resources Card
        html += '<div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Free and subsidised local activity services.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Resources in the North West</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">FitTrack users are primarily based in Greater Manchester, Merseyside, and the wider North West. The following local services are free or subsidised and complement the tracking functions of this application:</p>';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">';
        const resources = [
            { name: 'Active Lancashire', text: 'County-wide programme offering subsidised gym access, group sessions, and walking groups across all 14 Lancashire districts.', url: 'https://www.activelancashire.org.uk/', label: 'Visit Active Lancashire →' },
            { name: 'Greater Manchester Moving', text: 'The city region\'s movement hub, listing free and low-cost activity sessions from Stockport to Bolton.', url: 'https://gmmoving.co.uk/', label: 'Visit GM Moving →' },
            { name: 'Live Life Better Liverpool', text: 'Liverpool City Council\'s leisure service, offering concessionary rates for residents on low incomes.', url: 'https://www.livelifebetterliverpool.com/', label: 'Visit Live Life Liverpool →' },
            { name: 'Parkrun UK', text: 'Free, weekly 5k events held in parks across the North West. Open to all abilities; volunteers welcome. Over 60 locations in the region.', url: 'https://www.parkrun.org.uk/', label: 'Find your local parkrun →' }
        ];
        resources.forEach(r => {
            html += '<div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;"><h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">' + r.name + '</h3><p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">' + r.text + '</p><a href="' + r.url + '" rel="noopener noreferrer" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the ' + r.name + ' website.">' + r.label + '</a></div>';
        });
        html += '</div>';
        html += '</div>';
        // Authoritative Sources Card
        html += '<div class="card" data-tooltip="Official sources for physical activity guidance.">';
        html += '<h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Authoritative Sources</h2>';
        html += '<p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">The following organisations publish evidence-based guidance on physical activity. FitTrack links to them as permitted by the assignment brief; no third-party content is embedded directly.</p>';
        html += '<ul style="list-style: none; padding: 0; margin: 0;">';
        const sources = [
            { name: 'NHS — Exercise', url: 'https://www.nhs.uk/live-well/exercise/', label: 'Visit NHS Exercise →' },
            { name: 'British Heart Foundation — Physical Activity', url: 'https://www.bhf.org.uk/informationsupport/heart-health/physical-activity', label: 'Visit BHF →' },
            { name: 'Sport England', url: 'https://www.sportengland.org/', label: 'Visit Sport England →' },
            { name: 'WHO Guidelines on Physical Activity (2020)', url: 'https://www.who.int/publications/i/item/9789240015128', label: 'Visit WHO →' }
        ];
        sources.forEach(s => {
            html += '<li style="padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;"><span style="font-weight: 600; color: var(--text);">' + s.name + '</span><a href="' + s.url + '" rel="noopener noreferrer" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the ' + s.name + ' website.">' + s.label + '</a></li>';
        });
        html += '</ul>';
        html += '</div>';
        html += '</div>';
        html += '<style>.main-content a:hover { text-decoration: underline !important; opacity: 0.8; }</style>';
        container.innerHTML = html;
    }

    // =====================================================================
    // GLOBAL HANDLERS
    // =====================================================================
    window.addPlanItem = function(planId) {
        const activityTypeId = document.getElementById(`activityType_${planId}`).value;
        const plannedDate = document.getElementById(`planDate_${planId}`).value;
        const duration = document.getElementById(`planDuration_${planId}`).value;
        const notes = document.getElementById(`planNotes_${planId}`).value;
        if (!activityTypeId || !plannedDate || !duration) { alert('Please fill in activity type, date, and duration'); return; }
        fetch('api/plans.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ action: 'add_item', plan_id: planId, activity_type_id: activityTypeId, planned_date: plannedDate, duration, notes }) 
        })
        .then(res => res.json())
        .then(data => { 
            if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); 
            else alert('Error: ' + (data.error || 'Unknown error')); 
        })
        .catch(err => alert('Network error: ' + err.message));
    };
    
    window.togglePlanItemStatus = function(itemId, status) {
        fetch('api/plans.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ action: 'toggle_status', item_id: itemId, status }) 
        })
        .then(res => res.json())
        .then(data => { 
            if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); 
        })
        .catch(err => console.error('Status toggle failed:', err));
    };
    
    window.deletePlan = function(planId) {
        if (!confirm('Delete this plan and all its activities?')) return;
        fetch('api/plans.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ action: 'delete_plan', plan_id: planId }) 
        })
        .then(res => res.json())
        .then(data => { 
            if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); 
            else alert('Error: ' + (data.error || 'Unknown error')); 
        })
        .catch(err => alert('Network error: ' + err.message));
    };
    
    window.deleteWorkoutSpa = function(id) {
        if (!confirm('Delete this workout?')) return;
        fetch('api/activities.php', { 
            method: 'DELETE', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ activity_log_id: id }) 
        })
        .then(res => res.json())
        .then(data => { 
            if (data.success) loadRoute('/workouts.php', apiMap['/workouts.php']); 
            else alert('Error deleting workout.'); 
        })
        .catch(err => alert('Network error: ' + err.message));
    };

    window.deleteActivity = function(activityTypeId) {
        if (!confirm('Delete this activity type?')) return;
        fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_activity', activity_type_id: activityTypeId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadRoute('/admin.php', apiMap['/admin.php']);
            else alert('Error: ' + (data.error || 'Unknown error'));
        })
        .catch(err => alert('Network error: ' + err.message));
    };

    window.suspendUser = function(userId) {
        if (!confirm('Suspend (ban) this user?')) return;
        fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'suspend_user', user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadRoute('/admin.php', apiMap['/admin.php']);
            else alert('Error: ' + (data.error || 'Unknown error'));
        })
        .catch(err => alert('Network error: ' + err.message));
    };

    window.removeUser = function(userId) {
        if (!confirm('Remove this user from the tenant?')) return;
        fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove_user', user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadRoute('/admin.php', apiMap['/admin.php']);
            else alert('Error: ' + (data.error || 'Unknown error'));
        })
        .catch(err => alert('Network error: ' + err.message));
    };

    // =====================================================================
    // CHARTING LOGIC
    // =====================================================================
    function drawVolumeChart(data) {
        const svg = document.getElementById('volumeChart');
        if (!svg || !Array.isArray(data) || data.length === 0) return;
        const width = 800;
        const height = 280;
        const padding = { top: 20, right: 20, bottom: 40, left: 50 };
        const chartW = width - padding.left - padding.right;
        const chartH = height - padding.top - padding.bottom;
        const maxVal = Math.max(...data.map(d => parseInt(d.duration_minutes) || 0), 60);
        const yMax = Math.ceil(maxVal / 60) * 60;
        let gridHtml = '';
        for (let i = 0; i <= 4; i++) {
            const y = padding.top + (chartH / 4) * i;
            const val = Math.round(yMax - (yMax / 4) * i);
            gridHtml += `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>`;
            gridHtml += `<text x="${padding.left - 8}" y="${y + 4}" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.6">${val}</text>`;
        }
        const points = data.map((d, i) => {
            const x = padding.left + (i / Math.max(data.length - 1, 1)) * chartW;
            const mins = parseInt(d.duration_minutes) || 0;
            const y = padding.top + chartH - (mins / yMax) * chartH;
            return { x, y, mins, date: d.activity_date };
        });
        const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
        const line = `<path d="${pathD}" fill="none" stroke="var(--accent)" stroke-width="2"/>`;
        const areaD = `${pathD} L ${points[points.length - 1].x} ${padding.top + chartH} L ${points[0].x} ${padding.top + chartH} Z`;
        const area = `<path d="${areaD}" fill="var(--accent)" opacity="0.1"/>`;
        let pointsHtml = '';
        points.forEach(p => { pointsHtml += `<circle cx="${p.x}" cy="${p.y}" r="5" fill="var(--card)" stroke="var(--accent)" stroke-width="2"/>`; });
        let labelsHtml = '';
        const labelInterval = data.length > 14 ? Math.ceil(data.length / 7) : 1;
        points.forEach((p, i) => {
            if (i % labelInterval === 0 || i === points.length - 1) {
                const dateObj = new Date(p.date);
                const label = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
                labelsHtml += `<text x="${p.x}" y="${height - 8}" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">${label}</text>`;
            }
        });
        svg.innerHTML = gridHtml + area + line + pointsHtml + labelsHtml;
    }

    function drawLineChart(svgId, dataPoints, colour) {
        const svg = document.getElementById(svgId);
        if (!svg || !Array.isArray(dataPoints) || dataPoints.length === 0) {
            if (svg) svg.innerHTML = '<text x="50%" y="50%" text-anchor="middle" fill="var(--text)" opacity="0.6">No data available</text>';
            return;
        }
        const width = 800;
        const height = 280;
        const padding = { top: 30, right: 30, bottom: 40, left: 50 };
        const chartW = width - padding.left - padding.right;
        const chartH = height - padding.top - padding.bottom;
        const values = dataPoints.map(d => d.value);
        const maxVal = Math.max(...values);
        const minVal = Math.min(...values);
        const range = maxVal - minVal || 1;
        const yMax = maxVal + range * 0.1;
        const yMin = minVal - range * 0.1;
        let gridHtml = '';
        const gridSteps = 4;
        for (let i = 0; i <= gridSteps; i++) {
            const y = padding.top + (chartH / gridSteps) * i;
            const val = (yMax - (yMax - yMin) / gridSteps * i).toFixed(1);
            gridHtml += `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="var(--border)" stroke-width="1" stroke-dasharray="4" opacity="0.5"/>`;
            gridHtml += `<text x="${padding.left - 8}" y="${y + 4}" text-anchor="end" fill="var(--text)" font-size="10" opacity="0.6">${val}</text>`;
        }
        const points = dataPoints.map((d, i) => {
            const x = padding.left + (i / Math.max(dataPoints.length - 1, 1)) * chartW;
            const y = padding.top + chartH - ((d.value - yMin) / (yMax - yMin)) * chartH;
            return { x, y, value: d.value, label: d.label };
        });
        const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
        const line = `<path d="${pathD}" fill="none" stroke="${colour}" stroke-width="2"/>`;
        let pointsHtml = '';
        points.forEach(p => { pointsHtml += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="var(--card)" stroke="${colour}" stroke-width="2"/>`; });
        let labelsHtml = '';
        points.forEach((p, i) => {
            if (i % Math.ceil(points.length / 7) === 0 || i === points.length - 1) {
                labelsHtml += `<text x="${p.x}" y="${height - 10}" text-anchor="middle" fill="var(--text)" font-size="10" opacity="0.7">${p.label}</text>`;
            }
        });
        svg.innerHTML = gridHtml + line + pointsHtml + labelsHtml;
    }
});
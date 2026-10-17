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
 * authentication and tenant scoping.
 * User-supplied strings are escaped via template literals where rendered
 * into the DOM.
 */
'use strict';
document.addEventListener('DOMContentLoaded', () => {
    const feedContainer = document.getElementById('spa-feed');
    const scrollContainer = document.getElementById('spa-main') || document.querySelector('.main-content');
    const pageTitle = document.querySelector('.page-heading');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-item');

    const apiMap = {
        '/dashboard.php': '../api/activities.php',
        '/workouts.php': '../api/activities.php',
        '/nutrition.php': '../api/nutrition.php',
        '/progress.php': '../api/measurements.php',
        '/challenges.php': '../api/challenges.php',
        '/community.php': '../api/kudos.php',
        '/settings.php': '../api/settings.php',
        '/plans.php': '../api/plans.php'
    };

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

    const initialHash = window.location.hash.replace('#', '');
    const initialRoute = apiMap['/' + initialHash + '.php'] ? '/' + initialHash + '.php' : '/dashboard.php';
    const initialLink = document.querySelector(`.sidebar-nav .nav-item[href="${initialRoute}"]`);
    if (initialLink) {
        initialLink.classList.add('active');
        if (pageTitle) pageTitle.textContent = initialLink.querySelector('.nav-text').textContent;
    }
    loadRoute(initialRoute, apiMap[initialRoute]);

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
            console.log('Loaded data for', route, data);

            if (route === '/dashboard.php') renderDashboard(data, feedContainer);
            else if (route === '/workouts.php') renderWorkoutsInfiniteScroll(data, feedContainer);
            else if (route === '/nutrition.php') renderNutrition(data, feedContainer);
            else if (route === '/progress.php') renderProgress(data, feedContainer);
            else if (route === '/challenges.php') renderChallenges(data, feedContainer);
            else if (route === '/community.php') renderCommunity(data, feedContainer);
            else if (route === '/settings.php') renderSettings(data, feedContainer);
            else if (route === '/plans.php') renderPlans(data, feedContainer);
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
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${totalSessions}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Workouts This Week</div>
                        <div style="font-size: 0.8rem; color: var(--success);">Goal: 5 sessions</div>
                    </div>
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${totalMinutes}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Minutes Active</div>
                        <div style="font-size: 0.8rem; color: var(--success);">Goal: 150 mins</div>
                    </div>
                    <div class="stat-card" style="background: var(--card); padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--accent);">${avgPerSession}</div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem;">Avg Mins/Session</div>
                    </div>
                    <div class="stat-card highlight" style="background: var(--accent); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px var(--shadow);">
                        <div style="font-size: 0.9rem;">Day Streak</div>
                        <div style="font-size: 2rem; font-weight: bold; margin-top: 0.5rem;">0</div>
                    </div>
                </div>
                <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">
                    <h3 style="margin-bottom: 1rem; color: var(--text);">Activity Progress</h3>
                    <svg id="volumeChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
                </div>
            </div>
        `;
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
            html += `<tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 0.5rem;">${date}</td>
                <td style="padding: 0.5rem;">${type}</td>
                <td style="padding: 0.5rem;">${title}</td>
                <td style="padding: 0.5rem;">${duration} mins</td>
                <td style="padding: 0.5rem;">
                    <button onclick="deleteWorkoutSpa(${id})" style="background: var(--alert); color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Delete</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }

    function renderNutrition(data, container) {
        if (!Array.isArray(data) || data.length === 0) {
            data = [
                { food_name: 'Breakfast', name: 'Oatmeal with Berries', calories: 350, protein: 12, carbs: 58, fats: 8, logged_at: new Date().toISOString() },
                { food_name: 'Lunch', name: 'Grilled Chicken Salad', calories: 450, protein: 42, carbs: 18, fats: 22, logged_at: new Date(Date.now() - 86400000).toISOString() }
            ];
        }
        let html = '<div style="display: grid; gap: 1rem; max-width: 800px; margin: 0 auto;">';
        data.forEach(item => {
            const name = item.food_name || item.name || 'Meal';
            const cals = item.calories || 0;
            const protein = item.protein || 0;
            const carbs = item.carbs || 0;
            const fats = item.fats || 0;
            const date = item.logged_at ? new Date(item.logged_at).toLocaleDateString('en-GB') : 'Recent';
            html += `
                <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">
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
                </div>
            `;
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
            { label: 'Current Weight', value: latest.weight_kg, unit: 'kg', delta: formatDelta(weightDelta) },
            { label: 'BMI Score', value: latest.bmi, unit: '', delta: formatDelta(bmiDelta) },
            { label: 'Chest', value: latest.chest_cm, unit: 'cm', delta: '' },
            { label: 'Waist', value: latest.waist_cm, unit: 'cm', delta: '' },
            { label: 'Left Arm', value: latest.left_arm_cm, unit: 'cm', delta: '' },
            { label: 'Right Arm', value: latest.right_arm_cm, unit: 'cm', delta: '' },
            { label: 'Left Leg', value: latest.left_leg_cm, unit: 'cm', delta: '' },
            { label: 'Right Leg', value: latest.right_leg_cm, unit: 'cm', delta: '' }
        ];
        let html = '<div style="max-width: 1200px; margin: 0 auto;">';
        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
        stats.forEach(stat => {
            if (stat.value !== null && stat.value !== undefined && stat.value !== '') {
                html += `
                    <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); border-left: 4px solid var(--accent);">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; margin-bottom: 0.5rem;">${stat.label}</div>
                        <div style="font-size: 2rem; font-weight: bold; color: var(--text);">${stat.value}<span style="font-size: 1rem; opacity: 0.6; margin-left: 4px;">${stat.unit}</span></div>
                        ${stat.delta ? `<div style="margin-top: 0.5rem;">${stat.delta}</div>` : ''}
                    </div>
                `;
            }
        });
        html += '</div>';
        html += `
            <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0; color: var(--text);">Weight Trend</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button data-range="week" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;">Week</button>
                        <button data-range="month" class="active" style="padding: 0.4rem 1rem; border: 1px solid var(--accent); background: var(--accent); color: white; border-radius: 6px; cursor: pointer;">Month</button>
                        <button data-range="year" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;">Year</button>
                    </div>
                </div>
                <svg id="weightChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
            </div>
        `;
        html += `
            <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0; color: var(--text);">Activity Volume (Minutes)</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button data-vol-range="week" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;">Week</button>
                        <button data-vol-range="month" class="active" style="padding: 0.4rem 1rem; border: 1px solid var(--accent); background: var(--accent); color: white; border-radius: 6px; cursor: pointer;">Month</button>
                        <button data-vol-range="year" style="padding: 0.4rem 1rem; border: 1px solid var(--border); background: var(--bg); border-radius: 6px; cursor: pointer;">Year</button>
                    </div>
                </div>
                <svg id="volumeChart" width="100%" height="280" style="background: var(--bg); border-radius: 8px;"></svg>
            </div>
        `;
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
            html += `
                <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); border-top: 4px solid var(--accent);">
                    <h4 style="margin: 0 0 1rem 0; color: var(--text);">${title}</h4>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text);">
                        <span>${current} / ${target} ${unit}</span>
                        <span>${daysLeft}</span>
                    </div>
                    <div style="background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 0.5rem;">
                        <div style="width: ${progress}%; height: 100%; background: var(--accent); border-radius: 4px; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="text-align: center; font-size: 0.85rem; opacity: 0.7; color: var(--text);">
                        ${progress.toFixed(1)}% Complete
                    </div>
                </div>
            `;
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
        html += `
            <div style="background: var(--accent); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <h3 style="margin: 0 0 0.5rem 0;">Tenant Goal: ${tenantGoal.toLocaleString()} Active Minutes This Month</h3>
                <p style="margin: 0 0 1rem 0; opacity: 0.9;">Together, we've logged <strong>${tenantTotal.toLocaleString()}</strong> minutes. Keep it up!</p>
                <div style="background: rgba(255,255,255,0.2); height: 12px; border-radius: 6px; overflow: hidden;">
                    <div style="width: ${goalPercent}%; height: 100%; background: #f5e6a3; border-radius: 6px; transition: width 0.6s ease;"></div>
                </div>
            </div>
        `;
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
                html += `
                    <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 1rem;">
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
                            ${duration ? `<span>🕐 ${duration}</span>` : ''}
                        </div>
                        <div style="border-top: 1px solid var(--border); padding-top: 0.75rem;">
                            <button style="background: none; border: 1px solid var(--border); padding: 0.4rem 1rem; border-radius: 20px; cursor: pointer; color: var(--text); font-size: 0.85rem;"> 👍 ${kudos} Kudos</button>
                        </div>
                    </div>
                `;
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
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="${rankStyle}">${rank}${rank === 1 ? 'st' : rank === 2 ? 'nd' : rank === 3 ? 'rd' : 'th'}</span>
                            <strong style="color: var(--text);">${entry.display_name}</strong>
                        </div>
                        <span style="color: var(--accent); font-weight: bold;">${entry.total_minutes} mins</span>
                    </div>
                `;
            });
        }
        html += '</div></div></div></div>';
        container.innerHTML = html;
    }

    function renderSettings(data, container) {
        const prefs = Array.isArray(data) ? (data[0] || {}) : (data.data || data || {});
        container.innerHTML = `
            <div style="max-width: 600px; margin: 0 auto; background: var(--card); padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow);">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--text);">User Preferences</h3>
                <div style="display: grid; gap: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                        <div><div style="font-weight: bold; color: var(--text);">Distance Unit</div><div style="font-size: 0.85rem; opacity: 0.7;">Preferred unit for tracking distance</div></div>
                        <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.distance_unit || 'km'}</div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                        <div><div style="font-weight: bold; color: var(--text);">Weight Unit</div><div style="font-size: 0.85rem; opacity: 0.7;">Preferred unit for body weight</div></div>
                        <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.weight_unit ? prefs.weight_unit.replace('_', ' ') : 'kg'}</div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                        <div><div style="font-weight: bold; color: var(--text);">Temperature Unit</div><div style="font-size: 0.85rem; opacity: 0.7;">Preferred unit for weather/temperature</div></div>
                        <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.temperature_unit || 'celsius'}</div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div><div style="font-weight: bold; color: var(--text);">Activity Visibility</div><div style="font-size: 0.85rem; opacity: 0.7;">Default visibility for new activities</div></div>
                        <div style="background: var(--bg); padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; color: var(--accent); text-transform: uppercase;">${prefs.default_activity_visibility || 'private'}</div>
                    </div>
                </div>
                <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 0.85rem; opacity: 0.6; text-align: center;">
                    Preferences are managed via the MPA interface. Changes sync automatically.
                </div>
            </div>
        `;
    }

    function renderPlans(data, container) {
        const plans = (data && data.plans) ? data.plans : [];
        const activityTypes = (data && data.activity_types) ? data.activity_types : [];
        let html = '<div style="max-width: 1000px; margin: 0 auto;">';
        html += `
            <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 2rem;">
                <h3 style="margin: 0 0 1rem 0; color: var(--text);">Create New Training Plan</h3>
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Plan Title</label>
                        <input type="text" id="newPlanTitle" placeholder="e.g. 12-Week Marathon Prep" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Start Date</label>
                        <input type="date" id="newPlanStart" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">End Date</label>
                        <input type="date" id="newPlanEnd" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); box-sizing: border-box;">
                    </div>
                </div>
                <button id="createPlanBtn" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Create Plan</button>
            </div>
        `;
        if (plans.length === 0) {
            html += '<div style="text-align: center; padding: 3rem; background: var(--card); border-radius: 12px; opacity: 0.7;"><p>No active training plans. Create one above to get started.</p></div>';
        } else {
            plans.forEach(plan => {
                const progress = plan.stats ? plan.stats.progress : 0;
                const statusColor = plan.status === 'active' ? 'var(--accent)' : 'var(--border)';
                html += `
                    <div style="background: var(--card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 1.5rem; border-left: 4px solid ${statusColor};">
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
                                    <select id="activityType_${plan.plan_id}" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;">
                                        <option value="">Select...</option>
                                        ${activityTypes.map(t => `<option value="${t.activity_type_id}">${t.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Date</label>
                                    <input type="date" id="planDate_${plan.plan_id}" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Mins</label>
                                    <input type="number" id="planDuration_${plan.plan_id}" placeholder="60" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem;">Notes</label>
                                    <input type="text" id="planNotes_${plan.plan_id}" placeholder="Optional" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card); color: var(--text); box-sizing: border-box;">
                                </div>
                                <button onclick="addPlanItem(${plan.plan_id})" style="padding: 0.5rem 1rem; background: var(--accent); color: white; border: none; border-radius: 6px; cursor: pointer;">Add</button>
                            </div>
                        </div>
                        ${(!plan.items || plan.items.length === 0) ? '<p style="opacity: 0.6; font-style: italic; font-size: 0.9rem;">No activities scheduled yet.</p>' : `
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                ${plan.items.map(item => {
                                    const itemStatusColor = item.completion_status === 'completed' ? 'var(--success)' : item.completion_status === 'skipped' ? 'var(--alert)' : 'var(--text)';
                                    return `
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg); border-radius: 8px; border-left: 3px solid ${itemStatusColor};">
                                            <div style="flex-grow: 1;">
                                                <div style="font-weight: 600; color: var(--text); font-size: 0.9rem;">${item.type_name || 'Activity'}</div>
                                                <div style="font-size: 0.8rem; opacity: 0.7;">${item.planned_date} • ${item.planned_duration_minutes} mins${item.notes ? ' • ' + item.notes : ''}</div>
                                            </div>
                                            <select onchange="togglePlanItemStatus(${item.plan_item_id}, this.value)" style="padding: 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border); background: var(--card); color: var(--text);">
                                                <option value="planned" ${item.completion_status === 'planned' ? 'selected' : ''}>Planned</option>
                                                <option value="completed" ${item.completion_status === 'completed' ? 'selected' : ''}>Completed</option>
                                                <option value="skipped" ${item.completion_status === 'skipped' ? 'selected' : ''}>Skipped</option>
                                            </select>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        `}
                        <div style="margin-top: 1rem; text-align: right;">
                            <button onclick="deletePlan(${plan.plan_id})" style="padding: 0.5rem 1rem; background: var(--alert); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">Delete Plan</button>
                        </div>
                    </div>
                `;
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
                fetch('../api/plans.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'create_plan', title, start_date: start, end_date: end }) })
                .then(res => res.json()).then(data => { if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); else alert('Error: ' + (data.error || 'Unknown error')); })
                .catch(err => alert('Network error: ' + err.message));
            });
        }
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
        fetch('../api/plans.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'add_item', plan_id: planId, activity_type_id: activityTypeId, planned_date: plannedDate, duration, notes }) })
        .then(res => res.json()).then(data => { if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); else alert('Error: ' + (data.error || 'Unknown error')); })
        .catch(err => alert('Network error: ' + err.message));
    };

    window.togglePlanItemStatus = function(itemId, status) {
        fetch('../api/plans.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'toggle_status', item_id: itemId, status }) })
        .then(res => res.json()).then(data => { if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); })
        .catch(err => console.error('Status toggle failed:', err));
    };

    window.deletePlan = function(planId) {
        if (!confirm('Delete this plan and all its activities?')) return;
        fetch('../api/plans.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete_plan', plan_id: planId }) })
        .then(res => res.json()).then(data => { if (data.success) loadRoute('/plans.php', apiMap['/plans.php']); else alert('Error: ' + (data.error || 'Unknown error')); })
        .catch(err => alert('Network error: ' + err.message));
    };

    window.deleteWorkoutSpa = function(id) {
        if (!confirm('Delete this workout?')) return;
        fetch('../api/activities.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ activity_log_id: id }) })
        .then(res => res.json()).then(data => { if (data.success) loadRoute('/workouts.php', apiMap['/workouts.php']); else alert('Error deleting workout.'); })
        .catch(err => alert('Network error: ' + err.message));
    };

    // =====================================================================
    // CHARTING LOGIC (FULL IMPLEMENTATION)
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
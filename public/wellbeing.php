<?php
/**
 * File: wellbeing.php
 * Location: /public/wellbeing.php
 *
 * Purpose:
 * Evidence-based physical wellbeing information hub.
 * Addresses the brief's "wellbeing information" requirement by providing
 * UK-specific, peer-reviewed content on physical activity guidelines,
 * the physiological benefits of exercise, safety considerations, and
 * locally relevant resources for North West England users.
 *
 * Architectural Role:
 * Static informational view with external authoritative links.
 * Content is curated, not user-generated, to maintain clinical accuracy.
 *
 * Brief Alignment:
 * - Focus: Physical wellbeing (one of four categories specified in brief).
 * - Links to off-site information sources (NHS, BHF, Sport England) as permitted.
 * - No embedded third-party creative content.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();
// Explicitly set SPA mode to MPA so the header toggle renders correctly
$_SESSION['spa_mode'] = 'mpa';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pageTitle = 'Physical Wellbeing';
$currentPage = 'wellbeing';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <!-- Hero Card -->
    <div class="card" style="background: linear-gradient(135deg, var(--accent), var(--text)); color: white; margin-bottom: 1.5rem;" data-tooltip="Overview of evidence-based physical activity guidance.">
        <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">Physical Wellbeing</h1>
        <p style="margin: 0; font-size: 1.05rem; opacity: 0.95; max-width: 800px;">
            Evidence-based guidance on physical activity for adults in the United Kingdom.
            Content is drawn from peer-reviewed research and official UK government guidance,
            not commercial fitness advice.
        </p>
    </div>
    <!-- UK Guidelines Card -->
    <div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Official UK Chief Medical Officers activity guidelines.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">UK Physical Activity Guidelines</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            The UK Chief Medical Officers published revised physical activity guidelines in 2019,
            synthesising evidence from over 200 systematic reviews (Department of Health and Social Care, 2019).
            These guidelines apply to adults aged 19–64 and form the clinical baseline against which
            FitTrack measures your activity volume.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Weekly aerobic activity target.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Aerobic Activity</h3>
                <p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">150–300 minutes</p>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    of moderate-intensity aerobic activity per week, or 75–150 minutes of vigorous-intensity
                    activity, or an equivalent combination. Activity should be performed in bouts of at
                    least 10 minutes.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Weekly muscle strengthening target.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Muscle Strengthening</h3>
                <p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">2+ days per week</p>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    activities involving major muscle groups at moderate or greater intensity.
                    This includes resistance training, bodyweight exercises, and heavy gardening.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--accent);" data-tooltip="Guidance on reducing sedentary behaviour.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Sedentary Behaviour</h3>
                <p style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text); font-size: 1.1rem;">Minimise and interrupt</p>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    prolonged sitting. Any amount of sedentary behaviour carries some risk,
                    and the relationship with health outcomes is independent of physical activity levels.
                </p>
            </div>
        </div>
        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(159, 95, 145, 0.08); border-radius: 8px; border-left: 4px solid var(--highlight);" data-tooltip="Context on the 150-minute evidence base.">
            <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                <strong style="color: var(--accent);">A note on the evidence:</strong>
                The 150-minute threshold is a population-level recommendation. Warburton and Bredin (2017)
                note that dose–response curves are non-linear: the largest relative health gains occur when
                moving from sedentary to modest activity, and additional minutes yield progressively smaller
                marginal benefits. FitTrack's 150-minute weekly goal reflects this guideline, but individual
                targets should be discussed with a GP where pre-existing conditions apply.
            </p>
        </div>
    </div>
    <!-- Four Pillars Card -->
    <div class="card" style="margin-bottom: 1.5rem;" data-tooltip="The four components of physical fitness.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">The Four Pillars of Physical Activity</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            The American College of Sports Medicine identifies four distinct components of physical fitness,
            each producing different physiological adaptations (Garber <em>et al.</em>, 2011).
            A balanced programme addresses all four.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Heart and lung fitness pillar.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Cardiorespiratory Endurance</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Sustained rhythmic activity that challenges the heart and lungs.
                    Examples in FitTrack: running, walking, swimming. Regular aerobic training
                    reduces resting heart rate, improves VO₂ max, and lowers all-cause mortality risk.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Muscle and bone fitness pillar.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Muscular Strength & Endurance</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Resistance activity that overloads skeletal muscle. Preserves bone density,
                    supports metabolic health, and reduces falls risk in later life.
                    FitTrack's gym sessions target this pillar.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Joint mobility pillar.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Flexibility</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Range of motion across a joint. Maintained through static and dynamic stretching.
                    Reduces injury risk when performed after, rather than before, aerobic activity.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;" data-tooltip="Balance and coordination pillar.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Neuromotor Balance</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Proprioception, agility, and coordination. Particularly important for adults
                    over 40 to mitigate falls risk. Activities include yoga, Pilates, and racket sports.
                </p>
            </div>
        </div>
    </div>
    <!-- Research Benefits Card -->
    <div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Evidence-based health outcomes from physical activity.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">What the Research Shows</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            The physiological case for regular physical activity is among the most robust findings
            in modern epidemiology. Pedersen and Saltin (2015) describe exercise as "the best buy
            in public health," citing evidence across the following outcomes:
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="Heart disease risk reduction.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Cardiovascular Disease</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Regular moderate activity reduces coronary heart disease risk by approximately 30–40%.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="Diabetes risk reduction.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Type 2 Diabetes</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Risk reduction of 30–40% among active adults, with dose–response effects observed
                    up to approximately 300 minutes per week.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="Cancer risk reduction.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Common Cancers</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Colon, breast, and endometrial cancers show 20–30% risk reduction in active populations.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="Mental health benefits.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Mental Health</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Physical activity reduces the incidence of depression by 20–30% and is an effective
                    adjunct treatment for mild to moderate depressive episodes.
                </p>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px; border-top: 3px solid var(--accent);" data-tooltip="Bone and muscle health benefits.">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1rem;">Musculoskeletal Health</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Resistance training increases bone mineral density and reduces osteoporotic fracture
                    risk in postmenopausal women.
                </p>
            </div>
        </div>
    </div>
    <!-- Safety Card -->
    <div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Clinical guidance on safe exercise practice.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Safety and Injury Prevention</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            Physical activity carries a small absolute risk of musculoskeletal injury and, in rare cases,
            cardiac events. The following precautions are supported by clinical guidance:
        </p>
        <ol style="margin: 0 0 1.5rem 1.5rem; line-height: 1.8; color: var(--text);">
            <li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Pre-participation screening:</strong> Adults with known cardiovascular, metabolic, or renal disease should seek medical clearance before beginning vigorous activity.</li>
            <li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Gradual progression:</strong> Increase weekly volume by no more than 10% per week to reduce overuse injury risk.</li>
            <li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Warm-up and cool-down:</strong> Five to ten minutes of light aerobic activity before and after each session.</li>
            <li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Hydration:</strong> Replace fluid losses, particularly in sessions exceeding 60 minutes or in warm conditions.</li>
            <li style="margin-bottom: 0.5rem;"><strong style="color: var(--accent);">Listen to your body:</strong> Sharp pain, chest discomfort, or unusual breathlessness are signals to stop and seek medical advice.</li>
        </ol>
        <div style="padding: 1rem; background: rgba(226, 105, 114, 0.08); border-radius: 8px; border-left: 4px solid var(--alert);" data-tooltip="When to consult a GP before exercising.">
            <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                <strong style="color: var(--alert);">When to see a GP:</strong>
                If you have not been active for some time, are over 40, or have a long-term health condition,
                speak to your GP before starting a new exercise programme. NHS services are free at the point
                of use and can provide personalised advice or referral to a physiotherapist.
            </p>
        </div>
    </div>
    <!-- Local Resources Card -->
    <div class="card" style="margin-bottom: 1.5rem;" data-tooltip="Free and subsidised local activity services.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Resources in the North West</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            FitTrack users are primarily based in Greater Manchester, Merseyside, and the wider North West.
            The following local services are free or subsidised and complement the tracking functions
            of this application:
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Active Lancashire</h3>
                <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    County-wide programme offering subsidised gym access, group sessions, and walking
                    groups across all 14 Lancashire districts.
                </p>
                <a href="https://www.activelancashire.org.uk/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the Active Lancashire website.">
                    Visit Active Lancashire →
                </a>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Greater Manchester Moving</h3>
                <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    The city region's movement hub, listing free and low-cost activity sessions
                    from Stockport to Bolton.
                </p>
                <a href="https://gmmoving.co.uk/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the GM Moving website.">
                    Visit GM Moving →
                </a>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Live Life Better Liverpool</h3>
                <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Liverpool City Council's leisure service, offering concessionary rates
                    for residents on low incomes.
                </p>
                <a href="https://www.livelifebetterliverpool.com/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the Live Life Better Liverpool website.">
                    Visit Live Life Liverpool →
                </a>
            </div>
            <div style="background: var(--bg); padding: 1.25rem; border-radius: 10px;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--accent); font-size: 1.05rem;">Parkrun UK</h3>
                <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; line-height: 1.6; color: var(--text);">
                    Free, weekly 5k events held in parks across the North West. Open to all abilities;
                    volunteers welcome. Over 60 locations in the region.
                </p>
                <a href="https://www.parkrun.org.uk/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Find a local parkrun event.">
                    Find your local parkrun →
                </a>
            </div>
        </div>
    </div>
    <!-- Authoritative Sources Card -->
    <div class="card" data-tooltip="Official sources for physical activity guidance.">
        <h2 style="margin: 0 0 1rem 0; color: var(--text); font-size: 1.4rem;">Authoritative Sources</h2>
        <p style="margin: 0 0 1.5rem 0; line-height: 1.7; color: var(--text);">
            The following organisations publish evidence-based guidance on physical activity.
            FitTrack links to them as permitted by the assignment brief; no third-party content
            is embedded directly.
        </p>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; color: var(--text);">NHS — Exercise</span>
                <a href="https://www.nhs.uk/live-well/exercise/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the NHS Exercise guidance page.">
                    Visit NHS Exercise →
                </a>
            </li>
            <li style="padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; color: var(--text);">British Heart Foundation — Physical Activity</span>
                <a href="https://www.bhf.org.uk/informationsupport/heart-health/physical-activity" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the BHF Physical Activity page.">
                    Visit BHF →
                </a>
            </li>
            <li style="padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; color: var(--text);">Sport England</span>
                <a href="https://www.sportengland.org/" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the Sport England website.">
                    Visit Sport England →
                </a>
            </li>
            <li style="padding: 1rem; background: var(--bg); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; color: var(--text);">WHO Guidelines on Physical Activity (2020)</span>
                <a href="https://www.who.int/publications/i/item/9789240015128" rel="noopener noreferrer" target="_blank"
                   style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem;" data-tooltip="Visit the WHO Physical Activity Guidelines.">
                    Visit WHO →
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- Link hover style -->
<style>
    .main-content a:hover {
        text-decoration: underline !important;
        opacity: 0.8;
    }
</style>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
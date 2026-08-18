# Walkthrough - ARISE: The Human Evolution System

I have successfully initialized and implemented **ARISE: The Human Evolution System**, a gamified self-improvement platform inspired by *Solo Leveling*.

---

## 1. Project Initialization & Auth
- Initialized a Laravel project using Composer.
- Configured Laravel Breeze with the Livewire Volt stack (functional syntax) and dark mode settings enabled.
- Extended the main application layout (`app.blade.php`) to fetch 'Orbitron' and 'Outfit' typography and styled it with obsidian dark tones.
- Custom-styled the authentication guest layout (`guest.blade.php`) to match the core interface styling (obsidian dark, glowing cyber-grid, customized center brand logos, HUD corner markings).

---

## 2. Database Architecture (Migrations & Models)

We created all required database migrations and Eloquent models with custom methods and relationships:

- **Users Extensions (`users`)**:
  - Adds `level`, `xp`, `gold`, `skill_points`, `current_streak`, `highest_streak`, and `rank` fields.
  - Implemented `addXp()`, `checkLevelUp()`, and `determineRank()` on the `User` model.
- **System Contracts (`system_contracts`)**:
  - Fields: `user_id`, `title`, `description`, `duration_days`, `difficulty`, `xp_reward`, `gold_reward`, `status`, `start_date`, `end_date`, `failed_at`.
  - Scaffolds 7-71 `contract_checkins` child rows using an Eloquent booted `created` event.
- **Contract Check-ins (`contract_checkins`)**:
  - Fields: `contract_id`, `day_number`, `completed_at`, `is_checked`.
- **Life Domains (`life_domains`)**:
  - Fields: `user_id`, `health_physical_score`, `health_mental_score`, `finance_score`, `relationship_score`, `career_score`, `spirituality_score`, `overall_life_score`.
  - Weighted average calculation triggers and translates into a Life Rank (Bronze → Monarch).
- **Elite Skills (`elite_skills`)**:
  - Fields: `user_id`, `skill_name`, `level`, `xp`, `sub_tracks_scores` (JSON).
- **Other Entities**:
  - Tables for `guilds`, `guild_members`, `achievements`, `user_achievements`, and `daily_quests`.

---

## 3. UI Design (Tailwind CSS 4 + Livewire Volt)

- **Master Homepage (`welcome.blade.php`)**:
  - Clean neon cyber-themed hero banner inviting users to accept their contract.
  - Added a **custom AI-generated Solo Leveling protagonist illustration** served directly from the public assets (`images/hunter_hero.png`).
  - Added **individual generated photographs/portraits** next to the names of the top Hunters in the Global Hunter Standings table (`sung_jin_woo.png`, `cha_hae_in.png`, `thomas_andre.png`).
  - Interactive Evolution Journey timeline built with AlpineJS, animating stages from E-Rank to Monarch Rank.
  - Skill Tree preview card showing active sub-tracks dynamically based on selected skill nodes with custom-drawn neon SVG icons.
  - Global leaderboard display highlighting top hunters with their photographs.
  - AI Shadow Guide typing-effect terminal simulation.
- **Hunter Dashboard (`/dashboard`)**:
  - **Sleek HUD Circular XP progress ring** around the hunter rank badge.
  - Custom HUD SVG icons replacing all generic emojis.
  - Diagnostic range sliders for re-evaluating Life Domain scores.
  - Check-in Matrix indicating active days, locked days, and completed days.
  - Daily Quest checklist that awards XP and Gold on completion.
- **Authentication Pages (`/login` & `/register`)**:
  - Rewritten inside our custom grid guest layout.
  - Features custom-styled form controls, dark neon labels, custom neon check-box icons, and a glowing custom authentication entry button.

---

## 4. Game Systems & Logic

- **XP Engine (`XPEngineService`)**:
  - Distributes game rewards for daily logins (+10 XP), completed quests (+25 XP), upgraded skills (+50 XP), milestones (+250 XP), and contract completions (+500 XP).
  - Rewards scale with contract difficulty (Easy, Medium, Hard, Elite) and duration (7, 21, 51, 71 days).
- **Dynamic Missed Check-In Enforcement**:
  - `hunter-dashboard.blade.php`: Patched `loadDashboardData()` to scan all active contracts for missed check-ins from previous days (`day_number < $currentDayNum` and `is_checked == false`).
  - Automatically transitions any contract with a missed check-in to `'failed'` status, resets the user's progression streak back to `0`, fires the `ContractBroken` event, and flashes the S-Rank `"MISSION FAILED"` alert message on mount/refresh.
  - Added the same consecutive validation check inside the `checkIn()` method: if a player attempts to execute a check-in on a contract with any prior missed day, the action is blocked, the contract is closed as `'failed'`, and the S-Rank `"MISSION FAILED"` alert is immediately thrown.
- **Nightly failure automation (`CheckContractFailures` command)**:
  - Scans active contracts, detects missed days, flags contracts as `failed`, resets player streaks to 0, and dispatches the `ContractBroken` event.
  - Command is scheduled nightly in `routes/console.php`.
- **AI Shadow Guide (`AIShadowGuideService`)**:
  - Modular class yielding growth assessments, strength callouts, and recommendations.

---

## 5. Verification Results

### Automated Tests
We wrote and ran a full suite of automated feature tests using Laravel's test runner. All 38 tests passed successfully:

- `XPEngineTest.php`: Verifies leveling, rank transitions, and XP/Gold distribution.
- `ContractCheckinTest.php`: Validates contract check-ins, timeline matrices, and streak calculations.
- `SchedulerContractFailureTest.php`: Simulates nightly scans, verifying that missed days fail contracts and reset streaks, while checked-in days remain active.
- `TrialPaywallTest.php`: Validates trial limits (7 days for contracts, 3 days for domains and skills), action locking/blocking, payment processing, XP/Gold distribution, and subsequent level ups.

```bash
Tests:    38 passed (134 assertions)
Duration: 21.75s
```

### Production Compilation
We ran `npm run build` to verify asset pipelines. Vite compiled Tailwind styles into optimized bundles:
```bash
public/build/manifest.json             0.33 kB │ gzip:  0.17 kB
public/build/assets/app-lbTBPL3s.css  69.17 kB │ gzip: 11.12 kB
public/build/assets/app-DMRtMKZ6.js   45.65 kB │ gzip: 17.72 kB
✓ built in 5.40s
```

---

## 6. Premium Upgrades & Legibility Enhancement
- **Visual Design Upgrades**: Replaced generic, low-contrast small texts (8px to 10px gray tags) across the Status HUD header, check-in timeline matrix, diagnostic domain grids, cumulative score trackers, and modal input panels with standard tailwind styling (`text-xs`, `text-sm`, `text-slate-400`, `text-gray-300`, `text-white/20`) for a highly polished, gorgeous, and premium RPG-console design.
- **Paywall Custom Overlays**: Implemented stunning glassmorphic neon lock overlays inside the respective sections when trials expire:
  - **Active System Contract Card**: Locks out check-ins and contracts after 7 days, prompting payment of **99 Rs**.
  - **Life Domain Scorecard Card**: Locks out re-evaluations and indicators after 3 days, prompting payment of **199 Rs**.
  - **Elite System Skills Card**: Locks out skill upgrades after 3 days, prompting payment of **299 Rs**.
- **Backend Safety Guards**: Patched the backend Livewire handler methods (`checkIn()`, `acceptContract()`, `saveDomains()`, and `spendSkillPoint()`) to verify trial status and block requests in the event of client-side bypass.
- **Trial calculation fix**: Ensured absolute integer days calculation `(int) abs($now->diffInDays(...))` to prevent signed fractional differences in Carbon v3.

---

## 7. Color Theme Adjustments (Lighter Slate Theme)
- Shifted the custom Tailwind config colors for the `obsidian` palette:
  - `dark` (background): `#0f172a` (Slate-900)
  - `DEFAULT` (card): `#1e293b` (Slate-800)
  - `light`: `#334155` (Slate-700)
  - `card`: `#1e293b` (Slate-800)
- Replaced all layout hardcoded pitch black values (`#050508`) inside `app.blade.php` and `guest.blade.php` with `#0f172a` to achieve a slightly lighter, softer, and highly legible Slate dark mode.
- Cleaned up custom backgrounds like `bg-[#171822]` inside the dashboard check-in view and replaced with standard theme class `bg-obsidian-light` for color harmony.

---

## 8. Dynamic Sub-stat Custom Metrics & Habits Tracker
- **Dynamic Database Persistence**: The `LifeDomain` model reads and writes directly from a `metrics_data` JSON column holding the detailed sub-metric names and scores.
- **Dynamic Habit Scorecard Editor**: Replaced the simple static range sliders inside the scorecard editor with an interactive habit list for each of the 6 core stats.
- **Custom Habit Integration**: Users can add new custom habits / target areas to any domain and adjust their score rating (0 to 20 pts) via sliders.
- **Metric Deletion**: Unused metrics can be dynamically deleted from any scorecard.
- **Real-Time Recalculation**: Sub-stat metric updates are dynamically summed (capped at 100 per domain) and the cumulative average recalculates the user's Overall Hunter Level & Hunter Rank (E-Rank to S-Rank).
- **Sub-stat Detail Visualizer**: In display mode, the dashboard scorecard cards display a clean, dynamic list of active target habits and their individual scores underneath the main category bar (showing up to 5 habits with a indicator for additional habits).
- **Asset Re-compilation & Verification**: Recompiled asset packages and ran all PHPUnit feature and unit tests, achieving a 100% pass rate.

---

## 9. Admin Panel Dashboard and Layout Fixes
- **Resolved ArgumentCountError**: Fixed a crash on the `/admin/users` and `/admin/users/{id}` routes by making the `$level` parameter optional in the `determineRank()` function within the `User` model, defaulting to the user's current level.
- **Fixed Rank Styling and Duplicate Labels**: Corrected double-ranking suffixes (`E-Rank-Rank` -> `E-Rank`) and properly formatted the CSS badges according to full rank names (`S-Rank`, `A-Rank`, `B-Rank`, etc.).
- **Codebase Integration**: Committed and pushed all layout fixes successfully to the GitHub repository.

---

## 10. Admin Login Page Alignment Layout Fix
- **Balanced Interface Action Items**: Reorganized the "Remember Me / Keep Session Active" checkbox block and the "User Access" link to sit on the same line in a `justify-between` flex container, ensuring they align perfectly with the boundaries of the inputs.
- **Full-Width Submit Button**: Upgraded the "Awaken Access" submit button to be full-width (`w-full`) to match the user login page layout, anchoring the form container beautifully.
- **Verified Alignment and Styling**: Verified the visually stunning and harmonized admin login interface via screenshot inspection.

---

## 11. Mobile Responsiveness & Premium Animations
- **Responsive Navigation Header**: Added a toggler hamburger button and full-screen drop-down layout in `welcome.blade.php` using AlpineJS, allowing clean navigation for mobile users.
- **Collapsible Mobile Admin Sidebar**: Redesigned `layouts/admin.blade.php` sidebar to slide off-canvas on mobile viewports with a dark backdrop overlay, triggered by a hamburger button next to the dashboard title.
- **Responsive Admin Tables**: Integrated `overflow-x-auto w-full` wrappers and minimum widths (`min-w-[700px]` / `min-w-[800px]`) on all administrative lists (Audit Logs, Users, User Details, Subscriptions, Payments, Contracts, Plans) to protect from squished columns.
- **HUD Layout Balancing**: Adapted the Current Streak and Highest Record border dividers in the hunter dashboard header to render cleanly on desktop and disappear on stacked mobile viewports.
- **RPG Circular Rank Sizing**: Fixed character length constraints in the XP circle gauge text center, scaling font sizing dynamically for long rank names (e.g. `National Rank` or `Monarch Rank`).
- **CSS Micro-Animations**: Introduced fadeInUp transitions, slow floating icons, and breathing neon glow effects in the stylesheet to add a premium gamified feeling.

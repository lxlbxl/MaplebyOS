# Sprint 3: UI/UX Enhancements

**Priority:** 3 (Medium)  
**Goal:** Implement a polished, premium aesthetic (Glassmorphism) and ensure excellent usability across devices.  
**Focus:** Design System, Dashboard, Mobile, Accessibility.

---

## 1. Design System Implementation
**Module:** `Core`  
**Type:** Enhancement to `Core` (Theme)

### user_stories
- As a user, I want a modern, visually appealing interface.
- As a user, I want clear visual cues for statuses and actions.

### tasks
- [ ] **Custom Filament Theme**
    - [ ] Generate custom theme: `php artisan filament:theme`.
    - [ ] Configure `tailwind.config.js` with brand colors and "glassmorphism" utilities.
    - [ ] Create `resources/css/filament/admin/theme.css` with custom classes.
    - [ ] Apply glass effects to Panels, Cards, and Modals using backdrop-blur and semi-transparent backgrounds.

- [ ] **Component Styling**
    - [ ] Style metric cards with gradients.
    - [ ] Style buttons with modern shadows and hover effects.
    - [ ] Customize tables (transparent headers, row hover effects).

---

## 2. Dashboard Enhancements
**Module:** `Core` / All Modules  
**Type:** Enhancement

### user_stories
- As a user, I want a dashboard tailored to my role (Sales vs Ops).
- As a user, I want to see trends, not just current numbers.

### tasks
- [ ] **Widget Enhancement**
    - [ ] Update `StatsOverviewWidget` to support date ranges (e.g. "vs last month").
    - [ ] Add description icons and trend colors (green/red arrow).

- [ ] **Role-Based Dashboards**
    - [ ] Create separate dashboard pages or conditional widgets:
        - [ ] **Sales**: Lead Conversion, My Quotes, Pipeline Value.
        - [ ] **Ops**: Active Jobs, Yard Capacity, Pending Docs.
        - [ ] **Admin**: Financial Overview, team performance.
    - [ ] Implement `canView()` logic on Widgets based on user Role.

---

## 3. Mobile Responsiveness
**Module:** `Core`  
**Type:** Enhancement

### user_stories
- As a field agent, I need to use the app on my phone easily.

### tasks
- [ ] **Layout Optimization**
    - [ ] Review all Tables: set `toggleable()` on less important columns to hide them on mobile.
    - [ ] Ensure `StatsOverview` grid collapses to 1 column on mobile.
    - [ ] Verify Sidebar navigation is collapsible and accessible.

- [ ] **Touch Targets**
    - [ ] Ensure buttons and actions have sufficient padding for touch.
    - [ ] Test form inputs for ease of use on mobile.

---

## 4. Accessibility Improvements
**Module:** `Core`  
**Type:** Enhancement

### user_stories
- As a user, I rely on keyboard navigation and clear contrast.

### tasks
- [ ] **Audit & Fix**
    - [ ] Check color contrast ratios for text (WCAG AA).
    - [ ] Ensure all form inputs have labels.
    - [ ] Ensure focus states are visible for keyboard navigation.
    - [ ] Add descriptive `alt` tags to any custom images or icons.

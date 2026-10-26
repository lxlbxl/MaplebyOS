# Sprint 2: Enhanced Features

**Priority:** 2 (High)  
**Goal:** Enhance the system with business intelligence, advanced sales tools, notification systems, and yard operations.  
**Focus:** Reporting, Quoting, Yard Management, Notifications, Search.

---

## 1. Reporting & Analytics
**Module:** `Reports` (New Module)  
**Type:** New Module

### user_stories
- As a manager, I want to see sales performance charts.
- As an admin, I want to export monthly financial reports.

### tasks
- [ ] **Module Initialization**
    - [ ] Run `php artisan module:make Reports`.

- [ ] **Report Pages**
    - [ ] Create `SalesPerformance` page in Filament.
    - [ ] Create `OperationsPerformance` page in Filament.

- [ ] **Widgets & Charts**
    - [ ] Create `RevenueTrendChart` (Line chart).
    - [ ] Create `SalesByRepChart` (Bar chart).
    - [ ] Create `JobsByStatusChart` (Bar/Pie chart).
    - [ ] Create `LeadConversionChart` (Funnel).

- [ ] **Export Functionality**
    - [ ] Implement `ExportAction` on report pages to generate PDF/CSV using `maatwebsite/excel` or `spatie/browsershot` (for PDF).

---

## 2. Advanced Quoting & Templates
**Module:** `Quoting`  
**Type:** Enhancement to `Quoting` Module

### user_stories
- As a salesperson, I want to use templates to create quotes faster.
- As a salesperson, I want to download a professional PDF quote.

### tasks
- [ ] **Quote Templates**
    - [ ] Create `QuoteTemplate` model (name, service_type, items_json, terms).
    - [ ] Create `QuoteTemplateResource` for managing templates.
    - [ ] Add "Load Template" action in `QuoteResource` form to populate line items.

- [ ] **PDF Generation**
    - [ ] Install `barryvdh/laravel-dompdf`.
    - [ ] Create methods in `Quote` model: `generatePdf()`.
    - [ ] Create Blade view `quoting::pdf.quote` with branding and layout.
    - [ ] Add "Download PDF" action to `QuoteResource` table and header.

- [ ] **Email Integration**
    - [ ] Create "Send via Email" action.
    - [ ] Create `QuoteSent` mailable.

---

## 3. Yard Management System
**Module:** `Inventory`, `Operations`  
**Type:** Enhancement to `Inventory` Module

### user_stories
- As a yard manager, I need to check vehicles into the yard.
- As a yard manager, I need to see what is currently in the yard.

### tasks
- [ ] **Yard Dashboard**
    - [ ] Create `YardDashboard` page in `Inventory` module.
    - [ ] Add filters for "In Yard" status.

- [ ] **Gate Operations**
    - [ ] Create `YardLog` model (vehicle_id, action: check-in/out, performed_by, notes).
    - [ ] Add "Gate In" and "Gate Out" actions to `VehicleResource`.
    - [ ] Update `Vehicle` status automatically on Gate actions.

- [ ] **Yard Map (Basic)**
    - [ ] Add `location` field to `Vehicle` and `Equipment` models.
    - [ ] Show location column in Inventory tables.

---

## 4. Email Notification System
**Module:** `Core`  
**Type:** Enhancement to `Core` Module

### user_stories
- As a user, I want to be notified when a new lead is assigned to me.
- As a manager, I want to be notified of overdue tasks.

### tasks
- [ ] **Notification Infrastructure**
    - [ ] Configure `config/mail.php` (SMTP).
    - [ ] Create generic Notification classes (`LeadAssigned`, `TaskOverdue`, `JobCompleted`).

- [ ] **Triggers**
    - [ ] Add observers or events to `Lead`, `Task`, `Job` models to trigger notifications.
    - [ ] Implement database notifications (in-app) alongside email.
    - [ ] Add bell icon to Admin Panel header (Filament default).

---

## 5. Advanced Search
**Module:** `Core`  
**Type:** Enhancement to `Core` Module

### user_stories
- As a user, I want to search for a client name and find all their jobs and quotes.

### tasks
- [ ] **Global Search**
    - [ ] Enable global search in `LeadResource`, `ClientResource`, `JobResource`, `QuoteResource` ($recordTitleAttribute).
    - [ ] Configure `getGlobalSearchResultDetails()` in resources to show extra context (e.g. Phone # for clients).

- [ ] **Scout Integration (Optional)**
    - [ ] If performance lags, install `laravel/scout` and `teamtnt/laravel-scout-tntsearch-driver` for robust searching.

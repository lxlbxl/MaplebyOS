# Sprint 1: Critical Missing Features

**Priority:** 1 (Critical)  
**Goal:** Implement the missing core functional pillars required for the application to be operational and meet the PRD requirements.  
**Focus:** API, Tasks, Communications, Documents, and Job Management.

---

## 1. API Integration Layer
**Module:** `Core` (Infrastructure) & `CRM`, `Inventory` (Endpoints)  
**Type:** Enhancement to existing modules

### user_stories
- As a developer, I need an API structure to handle requests from the external website.
- As a Sales Manager, I want leads from the website to arrive automatically in the CRM.
- As a Marketing Manager, I want the website inventory to stay in sync with our system.

### tasks
- [ ] **Infrastructure Setup** (`Core` Module)
    - [ ] Create `routes/api.php` and configure in `bootstrap/app.php` (if generic) or register API routes in `CoreServiceProvider`.
    - [ ] Create `ValidateApiKey` middleware in `Core/Http/Middleware`.
    - [ ] Configure `api` guard in `config/auth.php`.
    - [ ] Create `api_logs` table migration in `Core`.

- [ ] **Lead Capture Endpoint** (`CRM` Module)
    - [ ] Create `LeadApiController` in `Modules/CRM/Http/Controllers/Api`.
    - [ ] Implement `POST /v1/leads/capture` method.
    - [ ] Add validation request `CaptureLeadRequest`.
    - [ ] Implement robust error handling and logging to `api_logs`.
    - [ ] Write unit test for lead capture.

- [ ] **Inventory Sync Endpoint** (`Inventory` Module)
    - [ ] Create `InventoryApiController` in `Modules/Inventory/Http/Controllers/Api`.
    - [ ] Implement `GET /v1/inventory/sync` method.
    - [ ] Return JSON structure matching PRD specs (vehicles & equipment).
    - [ ] Implement caching (5 min TTL) for sync response.

---

## 2. Task Management System
**Module:** `Tasks` (New Module)  
**Type:** New Module

### user_stories
- As a user, I want to create tasks for myself or colleagues.
- As a user, I want to see my assigned tasks on the dashboard.
- As a manager, I want to track task completion status.

### tasks
- [ ] **Module Initialization**
    - [ ] Run `php artisan module:make Tasks`.
    - [ ] Register module in `modules_statuses.json`.

- [ ] **Database Structure**
    - [ ] Create migration `create_tasks_table` (columns: title, description, taskable_id, taskable_type, assigned_to, assigned_by, priority, status, due_date).
    - [ ] Create `Task` model in `Modules/Tasks/Entities` with `softDeletes`.
    - [ ] Define polymorphic relationships in `Lead`, `Job`, `Quote` models (`morphToMany` tasks).

- [ ] **Filament Resource**
    - [ ] Create `TaskResource` in `Modules/Tasks/Filament/Resources`.
    - [ ] Implement `form()` schema (Title, Description, Priority, Due Date, Assignee).
    - [ ] Implement `table()` columns (Title, Related To, Assignee, Due Date, Status Badge).
    - [ ] Create `MyTasksWidget` (optimize existing or move to `Tasks` module) to show user-specific tasks.

---

## 3. Communication Tracking
**Module:** `Communications` (New Module)  
**Type:** New Module

### user_stories
- As a user, I want to log emails, calls, and meetings with clients.
- As a user, I want to see a timeline of all communications for a specific lead or job.

### tasks
- [ ] **Module Initialization**
    - [ ] Run `php artisan module:make Communications`.

- [ ] **Database Structure**
    - [ ] Create migration `create_communications_table` (columns: communicable_id, communicable_type, type, direction, subject, message, contacted_by, contacted_at).
    - [ ] Create `Communication` model.

- [ ] **Filament Integration**
    - [ ] Create `CommunicationRelationManager` for attaching to Clients, Leads, Jobs.
    - [ ] Add `CommunicationRelationManager` to `ClientResource`, `LeadResource`, `JobResource`.
    - [ ] Create `AddCommunicationAction` (modal form) in the RelationManager.

---

## 4. Document Management System
**Module:** `Operations` (Primary Owner) / `Core` (Support)  
**Type:** Enhancement to `Operations` Module

### user_stories
- As a user, I need to upload PDFs and images to a Job.
- As a user, I need to categorize documents (e.g., Bill of Lading, Invoice).

### tasks
- [ ] **Infrastructure** (`Core` Module)
    - [ ] Install `spatie/laravel-medialibrary`.
    - [ ] Run migrations for media library.
    - [ ] Configure `config/media-library.php`.

- [ ] **Job Documents** (`Operations` Module)
    - [ ] Update `Job` model to implement `HasMedia` and `InteractsWithMedia`.
    - [ ] Define media collections (e.g., 'documents', 'images').
    - [ ] Create `JobDocumentsRelationManager` in `JobResource`.
    - [ ] Implement `SpatieMediaLibraryFileUpload` component in the RelationManager form.
    - [ ] Add columns to table: Name, Type, Size, Download Action.

---

## 5. Complete Job Resource Implementation
**Module:** `Operations`  
**Type:** Enhancement to `Operations` Module

### user_stories
- As a user, I need a full form to enter all details for a Job.
- As a manager, I need to track job status through specific workflows.

### tasks
- [ ] **Job Form Implementation** (`JobResource.php`)
    - [ ] Implement `form()` schema sections:
        - [ ] **Job Details**: Job Number, Client, Quote Link, Service Type.
        - [ ] **Service Specifics**: Origin/Destination (dependent on service type).
        - [ ] **Status & Assignment**: Status (options based on service type), Priority, Assignee.
        - [ ] **Financials**: Total Value, Invoice Status.
    - [ ] Implement status logic in `Job` model (`getStatusOptions($serviceType)`).

- [ ] **Job Table Implementation** (`JobResource.php`)
    - [ ] Add columns: Job Number, Client, Status (color-coded), Service Type, Assignee, Date.
    - [ ] Add Filters: Service Type, Status, Assignee, Overdue.

- [ ] **Detail View**
    - [ ] Implement RelationManagers: `DocumentsRelationManager`, `TasksRelationManager`, `CommunicationsRelationManager`.
    - [ ] Add "Status Timeline" visual widget to `ViewJob` page.

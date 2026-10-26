# Sprint 4: System Administration & Settings

**Priority:** 4 (Medium)  
**Goal:** Provide administrative controls for system configuration, user management, and data security.  
**Focus:** Settings, Users, Activity Logs, Backups.

---

## 1. Settings Management Interface
**Module:** `Core`  
**Type:** Enhancement / New Resource

### user_stories
- As an admin, I want to change company details and email settings without editing code.

### tasks
- [ ] **Data Store**
    - [ ] Create `settings` table migration (key, value, type, group).
    - [ ] Create `Setting` model.

- [ ] **Settings Page**
    - [ ] Create `ManageSettings` Filament Page.
    - [ ] Implement tabs/sections:
        - [ ] **General**: Company Name, Logo, Currency.
        - [ ] **Email**: SMTP Host, Port, User, Pass.
        - [ ] **Finance**: Tax Rate, Payment Terms.
    - [ ] Implement `save()` logic to update the database/cache.
    - [ ] Create helper `settings('key')` to retrieve values.

---

## 2. User Management & Permissions
**Module:** `Core`  
**Type:** Enhancement

### user_stories
- As an admin, I want to assign roles to users to control their access.

### tasks
- [ ] **Role Setup** (Spatie Permission)
    - [ ] Seed standard roles: Admin, Manager, Sales, Operations.
    - [ ] Define permissions (e.g., `view_leads`, `delete_jobs`).

- [ ] **User Resource**
    - [ ] Create/Update `UserResource`.
    - [ ] Add `Select::make('roles')` to User form.
    - [ ] Implement access policies (`LeadPolicy`, `JobPolicy`) to check permissions.
    - [ ] Restrict `UserResource` access to Admin only.

---

## 3. Activity Log Interface
**Module:** `Core`  
**Type:** Enhancement

### user_stories
- As an admin, I want to see who deleted a record and when.

### tasks
- [ ] **Activity Resource**
    - [ ] Create `ActivityLogResource` (read-only).
    - [ ] Table columns: Causer (User), Subject (Entity), Description, Date.
    - [ ] Filter by Causer, Subject Type, Date Range.

- [ ] **Subject Linking**
    - [ ] Ensure `ActivityLog` entries link back to the actual subject properties (e.g. show old vs new values).

---

## 4. Backup & Restore System
**Module:** `Core`  
**Type:** Enhancement

### user_stories
- As an admin, I want the system to back up my data automatically.

### tasks
- [ ] **Infrastructure**
    - [ ] Install `spatie/laravel-backup`.
    - [ ] Publish config and configure destinations (Local/S3).

- [ ] **UI Management**
    - [ ] Create `Backup` page in Filament.
    - [ ] Add "Run Backup" button.
    - [ ] List existing backups with "Download" and "Delete" actions.
    - [ ] Schedule backup command in `console.php`.

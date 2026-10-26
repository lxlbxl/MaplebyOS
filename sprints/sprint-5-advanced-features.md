# Sprint 5: Advanced Enhancements

**Priority:** 5 (Low)  
**Goal:** Implement sophisticated features that provide significant value but are not strictly critical for day-one operations.  
**Focus:** Kanban, Imaging, Automation, Integrations.

---

## 1. Lead Kanban Board
**Module:** `CRM`  
**Type:** Enhancement

### user_stories
- As a sales rep, I want a visual board to drag and drop leads between stages.

### tasks
- [ ] **Kanban Integration**
    - [ ] Install `filament/kanban` (or custom Livewire component).
    - [ ] Create `LeadBoard` page.
    - [ ] Define statuses (New, Contacted, Quote Sent, Won, Lost) config.
    - [ ] Implement `onStatusChange` logic to update Lead model.
    - [ ] Customize card to show Title, Value ($), and Assignee.

---

## 2. Inventory Image Management
**Module:** `Inventory`  
**Type:** Enhancement

### user_stories
- As a user, I want to upload multiple photos for a vehicle and reorder them.

### tasks
- [ ] **Image Logic**
    - [ ] Ensure `Inventory` models (`Vehicle`) use Spatie Media Library.
    - [ ] Enable `SpatieMediaLibraryFileUpload` to accept `multiple()`.
    - [ ] Enable `reorderable()` on file upload component.
    - [ ] Add "Set as Primary" flag (custom property on media item).

- [ ] **Gallery View**
    - [ ] Create a "Gallery" tab in `VehicleResource` detail view.
    - [ ] Display images in a grid.

---

## 3. Quote-to-Job Automation
**Module:** `Quoting` -> `Operations`  
**Type:** Cross-Module Integration

### user_stories
- As a user, I want to turn an accepted quote into a job with one click.

### tasks
- [ ] **Conversion Logic**
    - [ ] Create Action `ConvertToJob` on `QuoteResource`.
    - [ ] Logic:
        - [ ] Create new `Job`.
        - [ ] Copy Client, Service Type, Price from `Quote`.
        - [ ] Link `Job` to `Quote` (`quote_id`).
        - [ ] Set `Quote` status to 'Accepted' (if not already).
        - [ ] Redirect to `Job` edit page.

---

## 4. WhatsApp Integration (Phase 1)
**Module:** `Communications` / `CRM`  
**Type:** Enhancement

### user_stories
- As a user, I want to click a button to open a WhatsApp chat with a client.

### tasks
- [ ] **Click-to-Chat**
    - [ ] Add WhatsApp icon/action to `Client` and `Lead` tables.
    - [ ] Generate link: `https://wa.me/{phone}?text={message}`.

- [ ] **Twilio Integration (Optional)**
    - [ ] If budget allows, install Twilio SDK to send template messages programmatically.

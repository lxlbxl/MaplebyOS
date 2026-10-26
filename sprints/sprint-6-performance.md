# Sprint 6: Performance & Optimization

**Priority:** 6 (Low)  
**Goal:** Optimize system performance for scale and speed.  
**Focus:** Database, Caching, Queues.

---

## 1. Database Optimization
**Module:** Global  
**Type:** Optimization

### user_stories
- As a user, I expect pages to load in under 1 second.

### tasks
- [ ] **Indexing**
    - [ ] Create migration `add_indexes_to_tables`.
    - [ ] Add indexes for foreign keys (`client_id`, `job_id`, `user_id`).
    - [ ] Add indexes for status columns (`status`, `is_active`).
    - [ ] Add indexes for search fields (`email`, `vin`, `quote_number`).

- [ ] **Query Audit**
    - [ ] Install Laravel Debugbar.
    - [ ] Identify N+1 queries in Filament resources (use `$table->with([...])`).
    - [ ] Optimize dashboard widget queries.

---

## 2. Caching Strategy
**Module:** `Core`  
**Type:** Optimization

### user_stories
- As a user, I want reports to generate quickly.

### tasks
- [ ] **Report Caching**
    - [ ] Wrap report query results in `Cache::remember('report_x', 300, fn...)`.
    - [ ] Implement command to clear specific caches on data update events.

- [ ] **Settings Cache**
    - [ ] Cache settings on retrieval (`Setting::get('key')`).
    - [ ] Clear settings cache on update.

---

## 3. Queue System
**Module:** `Core`  
**Type:** Optimization

### user_stories
- As a user, I don't want to wait for emails to send before seeing the success message.

### tasks
- [ ] **Queue Implementation**
    - [ ] Configure `QUEUE_CONNECTION=database` (dev) or `redis` (prod).
    - [ ] Dispatch email notifications to queue (`Implements ShouldQueue`).
    - [ ] Dispatch heavy PDF generation to queue.

- [ ] **Monitoring**
    - [ ] Install Laravel Horizon (if using Redis) to monitor queues.

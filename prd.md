# ADGOD Internal Business Management Tool - Product Requirements Document

## 1. Project Overview

### 1.1 Product Vision
A comprehensive web-based business management platform that centralizes operations for ADGOD Inc., managing leads, clients, jobs, inventory, and operations from a single unified dashboard.

### 1.2 Technical Stack
- **Backend Framework:** Laravel 11.x with Filament 3.x Admin Panel
- **Database:** MySQL (primary), SQLite (fallback/development)
- **Frontend:** Tailwind CSS with Shadcn-style components
- **Authentication:** Laravel Breeze/Filament Auth
- **File Storage:** Laravel Storage (local/S3)
- **API:** Laravel API Resources for website integration

### 1.3 Design Philosophy
- **UI/UX:** Modern, glassmorphic design with smooth animations
- **Responsiveness:** Mobile-first approach with adaptive layouts
- **Accessibility:** WCAG 2.1 AA compliance
- **Performance:** Optimized queries, lazy loading, caching

---

## 2. System Architecture

### 2.1 Database Schema

#### Core Tables

**users**
```sql
- id (bigint, PK)
- name (string)
- email (string, unique)
- password (string)
- role (enum: admin, sales, operations, manager)
- avatar (string, nullable)
- is_active (boolean, default: true)
- last_login_at (timestamp, nullable)
- timestamps
- soft_deletes
```

**clients**
```sql
- id (bigint, PK)
- type (enum: buyer, shipper, consignee)
- company_name (string)
- contact_person (string)
- email (string)
- phone (string)
- secondary_phone (string, nullable)
- address (text)
- city (string)
- state_province (string)
- country (string)
- postal_code (string, nullable)
- tax_id (string, nullable)
- notes (text, nullable)
- source (enum: website, referral, whatsapp, phone, direct)
- is_active (boolean, default: true)
- created_by (bigint, FK -> users)
- timestamps
- soft_deletes
```

**leads**
```sql
- id (bigint, PK)
- client_id (bigint, FK -> clients, nullable)
- source (enum: website, referral, whatsapp, phone, direct)
- service_type (enum: vehicle_export, freight_forwarding, inbound_clearing, equipment_sale)
- status (enum: new, contacted, quote_sent, won, lost)
- priority (enum: low, medium, high, urgent)
- title (string)
- description (text)
- estimated_value (decimal, nullable)
- contact_name (string)
- contact_email (string)
- contact_phone (string)
- website_form_data (json, nullable)
- assigned_to (bigint, FK -> users, nullable)
- lost_reason (text, nullable)
- won_at (timestamp, nullable)
- lost_at (timestamp, nullable)
- next_follow_up (timestamp, nullable)
- timestamps
- soft_deletes
```

**quotes**
```sql
- id (bigint, PK)
- quote_number (string, unique)
- lead_id (bigint, FK -> leads, nullable)
- client_id (bigint, FK -> clients)
- service_type (enum: vehicle_export, freight_forwarding, inbound_clearing, equipment_sale)
- status (enum: draft, sent, viewed, accepted, rejected, expired)
- title (string)
- description (text, nullable)
- valid_until (date)
- subtotal (decimal)
- tax_amount (decimal, default: 0)
- discount_amount (decimal, default: 0)
- total_amount (decimal)
- currency (string, default: 'USD')
- template_used (string, nullable)
- notes (text, nullable)
- terms_conditions (text, nullable)
- sent_at (timestamp, nullable)
- viewed_at (timestamp, nullable)
- accepted_at (timestamp, nullable)
- rejected_at (timestamp, nullable)
- rejection_reason (text, nullable)
- created_by (bigint, FK -> users)
- timestamps
- soft_deletes
```

**quote_items**
```sql
- id (bigint, PK)
- quote_id (bigint, FK -> quotes)
- description (string)
- quantity (integer, default: 1)
- unit_price (decimal)
- subtotal (decimal)
- notes (text, nullable)
- sort_order (integer, default: 0)
- timestamps
```

**jobs**
```sql
- id (bigint, PK)
- job_number (string, unique)
- quote_id (bigint, FK -> quotes, nullable)
- client_id (bigint, FK -> clients)
- service_type (enum: vehicle_export, freight_forwarding, inbound_clearing, equipment_sale)
- status (string) // Dynamic based on service_type
- title (string)
- description (text, nullable)
- origin (string, nullable)
- destination (string, nullable)
- estimated_completion (date, nullable)
- actual_completion (date, nullable)
- total_value (decimal, nullable)
- invoice_number (string, nullable)
- invoice_status (enum: pending, sent, paid, overdue, cancelled)
- payment_status (enum: unpaid, partial, paid)
- assigned_to (bigint, FK -> users, nullable)
- priority (enum: low, medium, high, urgent)
- notes (text, nullable)
- timestamps
- soft_deletes
```

**job_documents**
```sql
- id (bigint, PK)
- job_id (bigint, FK -> jobs)
- document_type (string) // Bill of Sale, Title, Bill of Lading, etc.
- file_name (string)
- file_path (string)
- file_size (integer)
- mime_type (string)
- uploaded_by (bigint, FK -> users)
- notes (text, nullable)
- timestamps
```

**job_status_history**
```sql
- id (bigint, PK)
- job_id (bigint, FK -> jobs)
- old_status (string, nullable)
- new_status (string)
- changed_by (bigint, FK -> users)
- notes (text, nullable)
- created_at (timestamp)
```

**vehicles**
```sql
- id (bigint, PK)
- vin (string, unique)
- make (string)
- model (string)
- year (integer)
- color (string, nullable)
- mileage (integer, nullable)
- mileage_unit (enum: miles, kilometers)
- body_type (string, nullable)
- engine_size (string, nullable)
- transmission (enum: automatic, manual, cvt)
- fuel_type (enum: gasoline, diesel, electric, hybrid)
- purchase_price (decimal, nullable)
- selling_price (decimal)
- currency (string, default: 'USD')
- status (enum: in_stock, sold, at_yard, shipped, reserved)
- condition (enum: excellent, good, fair, poor)
- location (string, nullable)
- notes (text, nullable)
- is_published (boolean, default: false)
- featured (boolean, default: false)
- published_at (timestamp, nullable)
- sold_at (timestamp, nullable)
- job_id (bigint, FK -> jobs, nullable)
- timestamps
- soft_deletes
```

**equipment**
```sql
- id (bigint, PK)
- serial_number (string, unique)
- type (string) // Excavator, Forklift, etc.
- manufacturer (string)
- model (string)
- year (integer, nullable)
- hours (integer, nullable)
- condition (enum: excellent, good, fair, poor)
- purchase_price (decimal, nullable)
- selling_price (decimal)
- currency (string, default: 'USD')
- status (enum: in_stock, sold, at_yard, shipped, reserved)
- location (string, nullable)
- specifications (json, nullable)
- notes (text, nullable)
- is_published (boolean, default: false)
- featured (boolean, default: false)
- published_at (timestamp, nullable)
- sold_at (timestamp, nullable)
- job_id (bigint, FK -> jobs, nullable)
- timestamps
- soft_deletes
```

**inventory_images**
```sql
- id (bigint, PK)
- imageable_type (string) // Vehicle or Equipment
- imageable_id (bigint)
- file_path (string)
- file_name (string)
- is_primary (boolean, default: false)
- sort_order (integer, default: 0)
- timestamps
```

**yard_logs**
```sql
- id (bigint, PK)
- inventory_type (string) // Vehicle or Equipment
- inventory_id (bigint)
- action (enum: gate_in, gate_out, inspection, maintenance)
- status (string, nullable) // Waiting for Docs, Ready to Ship, etc.
- notes (text, nullable)
- performed_by (bigint, FK -> users)
- performed_at (timestamp)
- timestamps
```

**tasks**
```sql
- id (bigint, PK)
- title (string)
- description (text, nullable)
- taskable_type (string, nullable) // Lead, Job, Quote, etc.
- taskable_id (bigint, nullable)
- assigned_to (bigint, FK -> users)
- assigned_by (bigint, FK -> users)
- priority (enum: low, medium, high, urgent)
- status (enum: pending, in_progress, completed, cancelled)
- due_date (date, nullable)
- completed_at (timestamp, nullable)
- timestamps
- soft_deletes
```

**activities**
```sql
- id (bigint, PK)
- subject_type (string) // Lead, Job, Client, etc.
- subject_id (bigint)
- causer_type (string, nullable)
- causer_id (bigint, nullable)
- description (text)
- properties (json, nullable)
- created_at (timestamp)
```

**communications**
```sql
- id (bigint, PK)
- communicable_type (string) // Lead, Client, Job
- communicable_id (bigint)
- type (enum: email, phone, whatsapp, meeting, other)
- subject (string, nullable)
- message (text)
- direction (enum: inbound, outbound)
- contacted_by (bigint, FK -> users)
- contacted_at (timestamp)
- timestamps
```

**quote_templates**
```sql
- id (bigint, PK)
- name (string)
- service_type (enum: vehicle_export, freight_forwarding, inbound_clearing, equipment_sale)
- description (text, nullable)
- default_items (json)
- terms_conditions (text, nullable)
- is_active (boolean, default: true)
- created_by (bigint, FK -> users)
- timestamps
```

**settings**
```sql
- id (bigint, PK)
- key (string, unique)
- value (text, nullable)
- type (enum: string, integer, boolean, json)
- group (string)
- description (text, nullable)
- timestamps
```

**api_logs**
```sql
- id (bigint, PK)
- endpoint (string)
- method (enum: GET, POST, PUT, DELETE)
- request_data (json, nullable)
- response_data (json, nullable)
- status_code (integer)
- ip_address (string)
- user_agent (text, nullable)
- created_at (timestamp)
```

---

## 3. Functional Requirements

### 3.1 Module 1: Main Dashboard

#### 3.1.1 Dashboard Layout
- **Top Navigation Bar:**
  - ADGOD logo/branding
  - Global search bar (clients, jobs, leads, inventory)
  - Quick-add dropdown (New Lead, New Shipment, New Quote)
  - Notifications icon with badge
  - User avatar with dropdown menu

- **Sidebar Navigation:**
  - Dashboard (home icon)
  - CRM (users icon)
  - Operations (briefcase icon)
  - Quotes (document icon)
  - Inventory (package icon)
  - Reports (chart icon)
  - Settings (gear icon - admin only)

#### 3.1.2 Dashboard Components

**KPI Widgets (Card Grid):**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ New Leads   │ Pending     │ Active      │ Inbound     │
│             │ Quotes      │ Shipments   │ Cargos      │
│    15       │    8        │    23       │    5        │
│ +3 today    │ $45K value  │ 12 delayed  │ 2 arriving  │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

Each card displays:
- Primary metric (large number)
- Secondary info (trend, count, or value)
- Subtle icon
- Hover effect with glassmorphic shadow
- Click to navigate to filtered view

**My Tasks Section:**
- List of tasks assigned to logged-in user
- Checkbox to mark complete
- Due date indicator (color-coded: green=future, yellow=today, red=overdue)
- Priority badge
- Quick view modal on click

**Quick Actions:**
- Floating action button (FAB) in bottom-right
- Expandable menu: New Lead, New Quote, New Job, New Shipment
- Smooth animation on expand/collapse

**Live Activity Feed:**
- Timeline-style feed (max 20 recent items)
- Format: "[User] [action] [entity] - [time ago]"
- Examples:
  - "John created Lead #L-1234 - 5 mins ago"
  - "Sarah updated Job #J-5678 to 'Shipped' - 1 hour ago"
- Auto-refresh every 30 seconds
- "View All" link to full activity log

**Charts (Manager/Admin only):**
- Line chart: Revenue last 30 days
- Donut chart: Leads by status
- Bar chart: Jobs by service type

#### 3.1.3 Role-Based Dashboard Views

**Sales Role:**
- Focus on Leads KPI, Quote KPI
- "My Leads" quick link
- "My Quotes" quick link
- Lead conversion rate widget

**Operations Role:**
- Focus on Active Jobs, Yard Inventory
- "Jobs Requiring Action" list
- "Yard Status" overview
- Job completion time metric

**Manager/Admin Role:**
- All KPIs visible
- Full activity feed
- All charts and analytics
- Team performance metrics

---

### 3.2 Module 2: CRM (Lead & Client Management)

#### 3.2.1 Lead Management

**Lead List View:**
- Filterable table with columns:
  - Lead ID
  - Title
  - Client/Contact
  - Service Type (badge with color)
  - Source (icon with tooltip)
  - Status (colorful badge)
  - Assigned To (avatar)
  - Next Follow-up (date)
  - Created Date
  - Actions (view, edit, delete)
- Advanced filters sidebar:
  - Status (multi-select)
  - Service Type (multi-select)
  - Source (multi-select)
  - Assigned To (multi-select)
  - Date range (created, next follow-up)
  - Priority
- Bulk actions: Assign, Update Status, Delete
- Export to CSV/Excel

**Lead Kanban Board:**
- Five columns: New | Contacted | Quote Sent | Won | Lost
- Drag-and-drop between columns
- Each card shows:
  - Lead title
  - Client name
  - Service type icon
  - Estimated value
  - Assigned user avatar
  - Days in current stage
- Filter by assigned user, service type, date range
- "Add Lead" button in each column

**Lead Detail/Edit Form:**
- Tabbed interface:
  
  **Tab 1: Overview**
  - Lead information fields (title, description, service type, status, priority)
  - Client selection (searchable dropdown or "Create New Client" button)
  - Contact details (if no client linked yet)
  - Source, estimated value
  - Assigned to (user selector)
  - Next follow-up date picker
  
  **Tab 2: Communications**
  - Timeline of all communications (emails, calls, messages)
  - "Add Communication" button → modal form
  - Each entry shows: type icon, subject/message, direction, user, timestamp
  
  **Tab 3: Quotes**
  - List of all quotes related to this lead
  - "Create Quote" button
  - Table: Quote #, Date, Amount, Status, Actions
  
  **Tab 4: Activity Log**
  - Full history of changes to this lead
  - System-generated entries: status changes, assignments, etc.

**Automatic Lead Capture (Website Integration):**
- API endpoint: `/api/leads/capture`
- Accepts JSON payload from website forms:
  ```json
  {
    "source": "website",
    "service_type": "vehicle_export",
    "form_data": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "message": "Interested in exporting a 2015 Honda Civic",
      "additional_fields": {}
    }
  }
  ```
- Creates new lead with status "new"
- Creates or links to existing client based on email
- Sends email notification to assigned sales team
- Stores raw form data in `website_form_data` JSON field

**Manual Lead Entry:**
- Quick-add form from dashboard FAB
- Full form in CRM → Leads → Create
- Required fields: title, service type, contact details
- Optional: description, estimated value, priority

**Lead Status Automation:**
- When quote is created from lead → status = "quote_sent"
- When quote is accepted → status = "won", create job automatically
- When lead is marked "lost" → prompt for reason

#### 3.2.2 Client Management

**Client List View:**
- Filterable table with columns:
  - Client ID
  - Company Name
  - Type (badge: Buyer, Shipper, Consignee)
  - Contact Person
  - Email
  - Phone
  - City/Country
  - Source
  - # of Jobs
  - Total Value
  - Status (Active/Inactive toggle)
  - Actions
- Filters: Type, Country, Source, Status
- Search: Company name, email, phone
- Export to CSV/Excel

**Client Detail View (Unified View):**
- Header section:
  - Company name (large)
  - Type badge
  - Contact info (email, phone with click-to-call/email)
  - Address
  - Edit button
  - Status toggle

- Summary cards row:
  - Total Jobs (count)
  - Total Revenue ($)
  - Active Jobs (count)
  - Last Contact (date)

- Tabbed sections:
  
  **Tab 1: Overview**
  - All client details
  - Notes section (rich text editor)
  - Created date, created by
  - Last modified

  **Tab 2: Leads (if any)**
  - Table of all leads for this client
  - Columns: Lead #, Title, Status, Assigned To, Date
  - Click to view lead details

  **Tab 3: Quotes**
  - Table of all quotes sent to this client
  - Columns: Quote #, Service Type, Amount, Status, Valid Until, Date
  - Quick actions: View, Convert to Job (if accepted)

  **Tab 4: Jobs**
  - Table of all jobs for this client
  - Columns: Job #, Service Type, Status, Value, Completion Date
  - Color-coded status badges
  - Click to view job details

  **Tab 5: Communications**
  - Timeline of all interactions
  - Filter by type (email, phone, whatsapp, meeting)
  - "Add Communication" button

  **Tab 6: Documents**
  - Uploaded documents related to this client
  - Categorized by type
  - Upload new document button

  **Tab 7: Activity Log**
  - Complete audit trail of all changes

**Client Create/Edit Form:**
- Company information
- Contact details (primary + secondary)
- Address (with country/state dropdowns)
- Type selection (can select multiple: e.g., Buyer + Shipper)
- Source
- Tax ID / Business registration
- Notes
- Save button with "Save & Add Another" option

---

### 3.3 Module 3: Operations & Job Management

#### 3.3.1 Job List View
- Filterable table:
  - Job Number (clickable)
  - Client Name
  - Service Type (badge with icon)
  - Status (progress badge)
  - Assigned To (avatar)
  - Origin → Destination
  - Value
  - Est. Completion
  - Priority
  - Actions
- Filters sidebar:
  - Service Type
  - Status
  - Assigned To
  - Date Range (created, completion)
  - Priority
  - Invoice Status
  - Payment Status
- Views: All Jobs, My Jobs, Overdue, Completed This Month
- Bulk actions: Assign, Update Status, Export

#### 3.3.2 Job Detail View

**Header Section:**
- Job number (large, with copy icon)
- Client name (link to client detail)
- Service type badge
- Status dropdown (change status inline)
- Priority badge
- Edit button
- Delete button (with confirmation)

**Status Timeline:**
- Horizontal progress tracker showing workflow stages
- Current stage highlighted
- Completed stages checked
- Future stages grayed out
- Click on stage to view history/notes

**Main Content Tabs:**

**Tab 1: Overview**
- Job details form:
  - Title, description
  - Origin, destination
  - Estimated completion date
  - Actual completion date
  - Total value
  - Assigned to
  - Priority
  - Notes (rich text)
- "Convert from Quote" info (if applicable)
- Created by, created date

**Tab 2: Documents**
- Grid or list view of uploaded documents
- Organized by document type
- Upload button with drag-and-drop zone
- Each document card shows:
  - File name
  - Type badge
  - Uploaded by (avatar)
  - Upload date
  - Download icon
  - Delete icon (with confirmation)
- Required documents checklist (based on service type)

**Tab 3: Financial**
- Invoice section:
  - Invoice number field
  - Invoice status dropdown
  - Payment status dropdown
  - Amount fields (subtotal, tax, total)
  - Invoice date
  - Due date
  - Payment terms
  - "Mark as Paid" button
- Payment history table:
  - Date, Amount, Method, Reference, Recorded By

**Tab 4: Communications**
- Timeline of all communications related to this job
- "Add Communication" button
- Filter by type

**Tab 5: Activity Log**
- Complete history of changes to job
- Status changes highlighted
- Document uploads logged
- System and user actions

#### 3.3.3 Service-Specific Workflows

**A. Vehicle/Equipment Export**

Status options:
- Awaiting Payment
- Payment Received
- In Yard
- Documentation Pending
- Ready to Ship
- Shipped
- In Transit
- Delivered
- Completed

Required documents:
- Bill of Sale
- Title/Certificate of Origin
- Bill of Lading
- Export Declaration
- Insurance Certificate
- Customs Documents

Additional fields in job form:
- VIN/Serial Number (link to inventory)
- Container number
- Vessel name
- Booking number
- ETD (Estimated Time of Departure)
- ETA (Estimated Time of Arrival)

**B. Freight Forwarding**

Status options:
- Booking Confirmed
- Cargo Received
- Documentation Complete
- In Transit
- Customs Cleared
- Out for Delivery
- Delivered
- Completed

Required documents:
- Packing List
- Commercial Invoice
- Certificate of Origin
- Bill of Lading / Air Waybill
- Insurance Certificate
- Customs Declaration

Additional fields:
- Cargo type
- Weight (kg/lbs)
- Dimensions
- Incoterms
- Container type/size
- Carrier name
- Tracking number
- Booking reference

**C. Inbound Clearing**

Status options:
- Documents Received
- Submitted to CBSA
- Duty Assessment Received
- Awaiting Payment
- Payment Confirmed
- Released
- Delivered to Client
- Completed

Required documents:
- B3 Form
- Customs Invoice
- Commercial Invoice
- Packing List
- Certificate of Origin
- NAFTA Certificate (if applicable)
- Release Order

Additional fields:
- Entry number
- CBSA reference
- Duty amount
- GST/VAT amount
- Broker reference
- Port of entry

#### 3.3.4 Shipping Yard Management

**Yard Dashboard:**
- Grid/list view of all items currently in yard
- Each item card shows:
  - Type (Vehicle/Equipment)
  - Make/Model or Equipment Type
  - VIN/Serial Number
  - Status badge
  - Days in yard
  - Associated job (link)
  - Location in yard
  - Thumbnail image
  - Quick action buttons
- Filters:
  - Item type
  - Status
  - Location
  - Date received (range)
- "Gate In" and "Gate Out" action buttons

**Gate In/Out Log:**
- Table view of all yard activity
- Columns:
  - Date/Time
  - Action (Gate In/Out/Inspection/Maintenance)
  - Item (VIN/Serial, Make/Model)
  - Job Number
  - Status After Action
  - Performed By
  - Notes
- Filter by date range, action type, item
- Export to PDF/CSV

**Yard Map View (Optional enhancement):**
- Visual grid representing yard layout
- Click on slot to see item details
- Drag items to move between slots
- Color-coded by status

---

### 3.4 Module 4: Quoting & Estimating

#### 3.4.1 Quote List View
- Table with columns:
  - Quote Number
  - Client Name
  - Service Type
  - Total Amount
  - Status (badge)
  - Valid Until
  - Created By
  - Created Date
  - Actions
- Filters: Status, Service Type, Date Range, Created By
- Status badges color-coded:
  - Draft (gray)
  - Sent (blue)
  - Viewed (purple)
  - Accepted (green)
  - Rejected (red)
  - Expired (orange)
- Quick actions: View, Edit, Duplicate, Convert to Job, Send, Download PDF

#### 3.4.2 Quote Builder

**Create Quote Form:**

**Step 1: Quote Details**
- Quote number (auto-generated, editable)
- Select client (searchable dropdown)
- Link to lead (optional, searchable dropdown)
- Service type selection
- Title field
- Description (rich text)
- Valid until date picker
- Template selector (optional - loads pre-defined items)

**Step 2: Line Items**
- Add Item button
- Each line item:
  - Description field
  - Quantity
  - Unit Price
  - Subtotal (auto-calculated)
  - Notes (optional)
  - Delete icon
  - Drag handle for reordering
- "Add from Template" button
- "Add Custom Item" button
- Live calculation panel on right:
  - Subtotal
  - Discount (% or fixed amount)
  - Tax (% or fixed amount)
  - **Total** (large, highlighted)

**Step 3: Terms & Notes**
- Internal notes (not visible in PDF)
- Terms & conditions (rich text, shows in PDF)
- Default T&C can be loaded from template

**Step 4: Review & Actions**
- Preview of quote (PDF-style layout)
- Save as Draft button
- Save & Send button
  - Sends email to client with PDF attachment
  - Marks status as "Sent"
  - Logs activity

**Quote Templates:**
- List view of saved templates
- Each template card:
  - Template name
  - Service type
  - Preview of items
  - Edit, Duplicate, Delete actions
- Create Template form:
  - Name
  - Service type
  - Default line items (same interface as quote builder)
  - Default terms & conditions
  - Active toggle

#### 3.4.3 Quote Detail View

**Header:**
- Quote number
- Status badge (with dropdown to change status)
- Client name (link)
- Actions: Edit, Duplicate, Download PDF, Send Email, Convert to Job

**Quote Information:**
- All quote details displayed in clean layout
- Line items table
- Pricing breakdown
- Terms & conditions

**Related Information:**
- Link to lead (if applicable)
- Link to job (if converted)

**Actions Panel:**
- Mark as Sent
- Mark as Accepted (prompts to create job)
- Mark as Rejected (prompt for reason)
- Mark as Expired
- Extend Validity Date
- Send Reminder Email

**Activity Timeline:**
- Quote created
- Quote sent (with email preview)
- Quote viewed (track when client opens email)
- Status changes
- Emails sent

#### 3.4.4 Quote to Job Conversion

**"Convert to Job" Flow:**
1. Click "Convert to Job" button on accepted quote
2. Modal appears:
   - Pre-filled job details from quote
   - Service type (from quote)
   - Initial status selection
   - Assign to user
   - Estimated completion date
   - Confirm button
3. On confirm:
   - Create new job with quote_id linked
   - Copy quote amount to job value
   - Quote status remains "accepted" but shows link to job
   - Activity logged in both quote and job
   - Redirect to new job detail page

---

### 3.5 Module 5: Inventory Management

#### 3.5.1 Inventory Dashboard

**Summary Cards:**
- Total Inventory Value ($)
- Items in Stock (#)
- Items at Yard (#)
- Items Sold This Month (#)
- Featured Items (#)
- Published to Website (#)

**View Toggles:**
- Vehicles / Equipment / All
- Grid view / Table view toggle

#### 3.5.2 Vehicle Inventory

**Vehicle List View (Grid):**
- Each card shows:
  - Primary image (placeholder if none)
  - Make, Model, Year
  - VIN (truncated with tooltip)
  - Price (large, formatted)
  - Status badge
  - Location
  - Icons: 
    - Published (globe icon if is_published=true)
    - Featured (star icon if featured=true)
  - Quick actions: View, Edit, Delete

**Vehicle List View (Table):**
- Columns: Image, VIN, Year, Make, Model, Color, Mileage, Price, Status, Location, Published, Actions
- Sortable columns
- Filters sidebar:
  - Status
  - Year range
  - Make (multi-select)
  - Body type
  - Transmission
  - Fuel type
  - Price range
  - Location
  - Published status
- Bulk actions: Update Status, Publish/Unpublish, Feature/Unfeature, Delete

**Vehicle Detail/Edit Form:**

**Tab 1: Basic Information**
- VIN (unique validation)
- Make, Model, Year
- Body type (dropdown: Sedan, SUV, Truck, Van, Coupe, etc.)
- Color (with color picker option)
- Mileage + Unit (miles/km toggle)
- Engine size
- Transmission (dropdown)
- Fuel type (dropdown)
- Condition (dropdown with description)

**Tab 2: Pricing & Status**
- Purchase price (optional, internal only)
- Selling price (public)
- Currency selector
- Status dropdown
- Location field
- Link to job (if status is sold/shipped)
- Sold date (auto-filled when status=sold)

**Tab 3: Images**
- Drag-and-drop upload zone
- Image grid with:
  - Thumbnail preview
  - "Set as Primary" button
  - Delete button
  - Drag handles for reordering
- Max 10 images
- Accepted formats: JPG, PNG, WebP
- Auto-resize/optimize on upload

**Tab 4: Website Publishing**
- Publish to Website toggle (prominent switch)
- Featured toggle (marks as featured on website)
- SEO fields:
  - Meta title (auto-generated from make/model/year)
  - Meta description
  - Keywords
- Preview button (shows how it will appear on website)
- Published date (auto-filled when published)
- Sync status indicator

**Tab 5: Notes & History**
- Internal notes (rich text)
- Activity log
- Related job history

#### 3.5.3 Equipment Inventory

**Equipment List View (Grid):**
- Each card shows:
  - Primary image
  - Equipment type icon
  - Manufacturer, Model
  - Serial number (truncated)
  - Price (large)
  - Status badge
  - Hours (if applicable)
  - Icons: Published, Featured
  - Quick actions

**Equipment List View (Table):**
- Columns: Image, Serial #, Type, Manufacturer, Model, Year, Hours, Condition, Price, Status, Location, Published, Actions
- Filters:
  - Status
  - Type (multi-select)
  - Manufacturer
  - Condition
  - Year range
  - Hours range
  - Price range
  - Location
  - Published status

**Equipment Detail/Edit Form:**

**Tab 1: Basic Information**
- Serial number (unique validation)
- Equipment type (dropdown: Excavator, Forklift, Bulldozer, Crane, Loader, etc.)
- Manufacturer
- Model
- Year
- Hours (meter reading)
- Condition dropdown
- Specifications (JSON key-value editor):
  - Weight
  - Engine power
  - Capacity
  - Dimensions
  - Attachments
  - Custom fields

**Tab 2: Pricing & Status**
- Same as vehicle pricing tab
- Additional: rental rate fields (optional)

**Tab 3: Images**
- Same as vehicle images tab

**Tab 4: Website Publishing**
- Same as vehicle publishing tab
- Additional: specification display options

**Tab 5: Notes & History**
- Same as vehicle notes tab

#### 3.5.4 Website Sync API

**Publish Endpoint:** `/api/inventory/sync`

**Response Format:**
```json
{
  "vehicles": [
    {
      "id": 123,
      "type": "vehicle",
      "vin": "1HGBH41JXMN109186",
      "make": "Honda",
      "model": "Civic",
      "year": 2015,
      "price": 12500,
      "currency": "USD",
      "condition": "excellent",
      "mileage": 45000,
      "mileage_unit": "miles",
      "color": "Blue",
      "transmission": "automatic",
      "fuel_type": "gasoline",
      "images": [
        "https://storage.example.com/vehicles/123/primary.jpg",
        "https://storage.example.com/vehicles/123/side.jpg"
      ],
      "featured": true,
      "published_at": "2025-11-15T10:30:00Z"
    }
  ],
  "equipment": [
    {
      "id": 456,
      "type": "equipment",
      "serial_number": "EX12345",
      "equipment_type": "Excavator",
      "manufacturer": "Caterpillar",
      "model": "320D",
      "year": 2018,
      "price": 85000,
      "currency": "USD",
      "hours": 3200,
      "condition": "good",
      "specifications": {
        "weight": "20 tons",
        "engine_power": "122 HP",
        "bucket_capacity": "1.2 m³"
      },
      "images": [
        "https://storage.example.com/equipment/456/main.jpg"
      ],
      "featured": false,
      "published_at": "2025-11-20T14:00:00Z"
    }
  ],
  "last_sync": "2025-12-06T12:00:00Z"
}
```

**Webhook Configuration:**
- Setting to configure website webhook URL
- Send POST request when:
  - Item is published/unpublished
  - Published item is updated
  - Item is deleted
- Include authentication token

---

### 3.6 Module 6: Reporting & Analytics

#### 3.6.1 Report Dashboard

**Layout:**
- Date range selector (prominent, top-right)
  - Presets: Today, Yesterday, This Week, Last Week, This Month, Last Month, This Quarter, Last Quarter, This Year, Custom
- Export button (PDF, Excel)
- Print button

**Report Categories (Tabs):**
1. Sales Performance
2. Operations Performance
3. Lead Analytics
4. Financial Overview
5. Team Performance

#### 3.6.2 Sales Report

**Key Metrics Cards:**
- Total Revenue (with period-over-period %)
- Number of Quotes Sent
- Quote Acceptance Rate (%)
- Average Quote Value
- Number of Jobs Won
- Average Deal Size

**Charts:**

**Revenue by Service Type (Donut Chart)**
- Vehicle Export
- Freight Forwarding
- Inbound Clearing
- Equipment Sales

**Revenue Trend (Line Chart)**
- X-axis: Time (daily/weekly/monthly based on range)
- Y-axis: Revenue
- Multiple lines for different service types (toggleable)

**Sales by Rep (Bar Chart)**
- X-axis: Sales rep names
- Y-axis: Revenue
- Color-coded bars
- Show count of deals above each bar

**Quote Funnel (Funnel Chart)**
- Stages: Drafted → Sent → Viewed → Accepted
- Show count and conversion rate at each stage

**Data Table: Top Clients by Revenue**
- Columns: Client Name, # of Jobs, Total Revenue, Avg Job Value
- Top 10, with "View All" link

#### 3.6.3 Operations Report

**Key Metrics Cards:**
- Active Jobs (count)
- Completed Jobs This Period
- Average Job Completion Time (days)
- On-Time Completion Rate (%)
- Jobs Overdue (count)
- Yard Inventory Count

**Charts:**

**Jobs by Status (Horizontal Bar Chart)**
- Each status with count
- Color-coded by service type

**Job Completion Trend (Line Chart)**
- X-axis: Time
- Y-axis: Number of completed jobs
- Compare to previous period

**Average Completion Time by Service Type (Bar Chart)**
- Show average days to complete
- Target line for benchmark

**Yard Activity (Timeline/Calendar View)**
- Gate In/Out activity
- Highlight busy days

**Data Table: Jobs by Service Type**
- Service Type, Active, Completed, Avg Duration, Success Rate

**Data Table: Longest Active Jobs**
- Job #, Client, Service Type, Days Active, Status
- Alert icon for overdue jobs

#### 3.6.4 Lead Analytics

**Key Metrics Cards:**
- New Leads This Period
- Lead Conversion Rate (%)
- Average Time to Convert
- Leads by Status (breakdown)
- Lead Response Time (average)

**Charts:**

**Leads by Source (Pie Chart)**
- Website, Referral, WhatsApp, Phone, Direct
- Show percentage for each

**Lead Conversion Funnel**
- New → Contacted → Quote Sent → Won
- Show drop-off at each stage

**Leads Trend (Area Chart)**
- X-axis: Time
- Y-axis: Number of leads
- Stacked by status

**Conversion Rate by Source (Bar Chart)**
- Show which sources convert best

**Data Table: Lead Performance by Assigned User**
- User, # Leads, # Converted, Conversion Rate, Avg Response Time

#### 3.6.5 Financial Overview

**Key Metrics Cards:**
- Total Invoiced Amount
- Total Paid
- Outstanding Receivables
- Overdue Invoices (count + amount)
- Payment Collection Rate (%)

**Charts:**

**Invoice Status Breakdown (Donut Chart)**
- Paid, Pending, Overdue, Cancelled

**Revenue vs. Collections (Line Chart)**
- Two lines: Invoiced amount, Collected amount
- Show gap

**Aging Report (Stacked Bar Chart)**
- Current, 1-30 days, 31-60 days, 61-90 days, 90+ days
- Each bar shows count and amount

**Data Table: Top Outstanding Invoices**
- Job #, Client, Invoice #, Amount, Due Date, Days Overdue

**Data Table: Payment Collection by Month**
- Month, Invoiced, Collected, Collection Rate

#### 3.6.6 Team Performance

**Key Metrics Cards:**
- Total Team Members
- Most Active User
- Average Tasks per User
- Team Task Completion Rate (%)

**Charts:**

**Activity by User (Bar Chart)**
- Show leads created, quotes sent, jobs completed per user

**Task Completion Rate by User (Progress Bars)**
- Each user with completion percentage

**Response Time by User (Bar Chart)**
- Average lead response time

**Data Table: Individual Performance**
- User, Leads, Quotes, Jobs, Revenue Generated, Tasks Completed, Avg Response Time

---

### 3.7 Module 7: Admin & Settings

#### 3.7.1 User Management

**User List View:**
- Table columns: Avatar, Name, Email, Role, Status, Last Login, Created Date, Actions
- Filters: Role, Status (Active/Inactive)
- Search by name or email
- Add User button

**User Create/Edit Form:**
- Profile picture upload (crop to circle)
- Name (required)
- Email (unique, required)
- Password (create only)
- Role dropdown (Admin, Manager, Sales, Operations)
- Is Active toggle
- Save button

**Roles & Permissions:**

**Admin:**
- Full access to all modules
- User management
- System settings
- All CRUD operations
- View all data

**Manager:**
- Full access to Dashboard, CRM, Operations, Quotes, Inventory, Reports
- View team performance
- Assign tasks and jobs
- Cannot manage users or system settings
- View all data

**Sales:**
- Access to Dashboard, CRM, Quotes, limited Inventory view
- Create/edit own leads and quotes
- View own jobs
- Cannot delete jobs
- Cannot access reports or settings

**Operations:**
- Access to Dashboard, Operations, Inventory (full), Yard Management
- Create/edit jobs
- Update job status
- Manage yard activity
- Limited CRM access (view only)
- Cannot access quotes or settings

#### 3.7.2 System Settings

**General Settings Tab:**
- Company name
- Company logo upload
- Company address
- Contact email
- Contact phone
- Timezone selector
- Date format (MM/DD/YYYY, DD/MM/YYYY, YYYY-MM-DD)
- Currency default
- Language (future enhancement)

**Email Settings Tab:**
- SMTP configuration
  - Host
  - Port
  - Username
  - Password
  - Encryption (TLS/SSL)
  - From address
  - From name
- Test email button
- Email templates editor (for notifications)

**Notification Settings Tab:**
- Configure which events trigger notifications
- Checkboxes for:
  - New lead assigned
  - Quote viewed by client
  - Quote accepted
  - Job status changed
  - Task assigned
  - Task overdue
  - Payment received
  - Document uploaded
- Configure recipients by role

**API Settings Tab:**
- Website integration section:
  - API Key (generate/regenerate button)
  - Webhook URL for website
  - Webhook secret
  - Test connection button
- API logs link (view recent API calls)
- Rate limiting settings

**Invoice Settings Tab:**
- Invoice number format (prefix, padding)
- Default payment terms
- Default tax rate (%)
- Invoice footer text
- Bank account details for invoices

**Backup Settings Tab:**
- Database backup configuration
- Schedule automated backups
- Manual backup button
- List of available backups with restore option
- Cloud storage integration (optional)

#### 3.7.3 Quote Templates Management

**Template List:**
- Grid view of all templates
- Each card: Name, Service Type, # Items, Created By, Last Used
- Filter by service type
- Create Template button

**Template Editor:**
- Same interface as quote builder
- Save as reusable template
- Can be selected when creating new quote

#### 3.7.4 Audit Logs

**System Activity Log:**
- Comprehensive log of all system activities
- Table columns: Timestamp, User, Action, Entity Type, Entity ID, IP Address, Details
- Advanced filters:
  - Date range
  - User
  - Action type (Created, Updated, Deleted, Viewed, etc.)
  - Entity type
- Search functionality
- Export to CSV
- Auto-archival of logs older than 12 months

---

## 4. Technical Implementation Details

### 4.1 Laravel/Filament Setup

**Installation:**
```bash
composer create-project laravel/laravel adgod-management
cd adgod-management
composer require filament/filament:"^3.0"
php artisan filament:install --panels
composer require filament/spatie-laravel-translatable-plugin
composer require spatie/laravel-activitylog
composer require spatie/laravel-medialibrary
```

**Filament Resources to Create:**
- LeadResource (with Kanban view)
- ClientResource
- QuoteResource
- JobResource
- VehicleResource
- EquipmentResource
- TaskResource
- UserResource (admin only)

**Filament Pages:**
- Dashboard (custom)
- Reports (custom)
- Settings (custom)

**Filament Widgets:**
- StatsOverviewWidget (KPI cards)
- ChartWidget (various charts)
- TableWidget (activity feed, tasks)

### 4.2 Database Configuration

**config/database.php modifications:**
```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'adgod_management'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => 'InnoDB',
    ],
    
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => database_path('database.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
],
```

**Automatic Fallback Logic:**
```php
// config/database.php or AppServiceProvider
try {
    DB::connection('mysql')->getPdo();
    $defaultConnection = 'mysql';
} catch (\Exception $e) {
    $defaultConnection = 'sqlite';
    Log::warning('MySQL unavailable, falling back to SQLite');
}

config(['database.default' => $defaultConnection]);
```

### 4.3 Key Laravel Packages

**Required:**
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "filament/filament": "^3.2",
    "spatie/laravel-activitylog": "^4.7",
    "spatie/laravel-medialibrary": "^11.0",
    "spatie/laravel-permission": "^6.0",
    "barryvdh/laravel-dompdf": "^2.0",
    "maatwebsite/excel": "^3.1",
    "intervention/image": "^3.0"
  }
}
```

### 4.4 Model Relationships

**Client Model:**
```php
public function leads(): HasMany
public function quotes(): HasMany
public function jobs(): HasMany
public function communications(): MorphMany
public function activities(): MorphMany
```

**Lead Model:**
```php
public function client(): BelongsTo
public function assignedTo(): BelongsTo (User)
public function quotes(): HasMany
public function communications(): MorphMany
public function tasks(): MorphToMany
public function activities(): MorphMany
```

**Quote Model:**
```php
public function client(): BelongsTo
public function lead(): BelongsTo
public function items(): HasMany (QuoteItem)
public function job(): HasOne
public function createdBy(): BelongsTo (User)
public function activities(): MorphMany
```

**Job Model:**
```php
public function client(): BelongsTo
public function quote(): BelongsTo
public function documents(): HasMany (JobDocument)
public function statusHistory(): HasMany (JobStatusHistory)
public function assignedTo(): BelongsTo (User)
public function vehicle(): BelongsTo (nullable)
public function equipment(): BelongsTo (nullable)
public function communications(): MorphMany
public function tasks(): MorphToMany
public function activities(): MorphMany
```

**Vehicle/Equipment Models:**
```php
public function images(): MorphMany (InventoryImage)
public function job(): BelongsTo (nullable)
public function yardLogs(): MorphMany (YardLog)
public function activities(): MorphMany
```

**User Model:**
```php
public function assignedLeads(): HasMany (Lead)
public function assignedJobs(): HasMany (Job)
public function assignedTasks(): HasMany (Task)
public function createdQuotes(): HasMany (Quote)
public function activities(): MorphMany
```

### 4.5 Key Features Implementation

#### Automatic Number Generation:
```php
// In respective models
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        if (empty($model->lead_number)) {
            $model->lead_number = 'L-' . str_pad(
                static::max('id') + 1, 
                4, 
                '0', 
                STR_PAD_LEFT
            );
        }
    });
}
```

#### Activity Logging:
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Lead extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

#### File Upload Handling:
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Vehicle extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
             ->useDisk('public')
             ->registerMediaConversions(function (Media $media) {
                 $this->addMediaConversion('thumb')
                      ->width(300)
                      ->height(300);
                      
                 $this->addMediaConversion('large')
                      ->width(1200)
                      ->height(800);
             });
    }
}
```

### 4.6 API Implementation

**routes/api.php:**
```php
Route::prefix('v1')->group(function () {
    Route::post('/leads/capture', [LeadApiController::class, 'capture'])
         ->middleware('api.key');
    
    Route::get('/inventory/sync', [InventoryApiController::class, 'sync'])
         ->middleware('api.key');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('leads', LeadApiController::class);
        Route::apiResource('clients', ClientApiController::class);
        Route::apiResource('jobs', JobApiController::class);
    });
});
```

**API Middleware for Key Validation:**
```php
// app/Http/Middleware/ValidateApiKey.php
public function handle($request, Closure $next)
{
    $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');
    
    if (!$apiKey || $apiKey !== config('services.api.key')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    return $next($request);
}
```

---

## 5. UI/UX Design Guidelines

### 5.1 Design System

**Color Palette:**
```css
:root {
  /* Primary Colors */
  --primary-50: #eff6ff;
  --primary-100: #dbeafe;
  --primary-500: #3b82f6;
  --primary-600: #2563eb;
  --primary-700: #1d4ed8;
  
  /* Neutral Colors */
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  
  /* Status Colors */
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  
  /* Glassmorphism */
  --glass-bg: rgba(255, 255, 255, 0.1);
  --glass-border: rgba(255, 255, 255, 0.2);
  --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}
```

**Typography:**
```css
/* Font Family */
--font-sans: 'Inter', 'SF Pro Display', system-ui, sans-serif;

/* Font Sizes */
--text-xs: 0.75rem;      /* 12px */
--text-sm: 0.875rem;     /* 14px */
--text-base: 1rem;       /* 16px */
--text-lg: 1.125rem;     /* 18px */
--text-xl: 1.25rem;      /* 20px */
--text-2xl: 1.5rem;      /* 24px */
--text-3xl: 1.875rem;    /* 30px */
--text-4xl: 2.25rem;     /* 36px */

/* Font Weights */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

**Spacing System:**
```css
/* Following Tailwind's spacing scale */
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-3: 0.75rem;   /* 12px */
--space-4: 1rem;      /* 16px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-12: 3rem;     /* 48px */
```

### 5.2 Component Styles

**Cards:**
```css
.card {
  @apply bg-white dark:bg-gray-800 rounded-xl shadow-lg;
  @apply border border-gray-200 dark:border-gray-700;
  @apply transition-all duration-300;
  @apply hover:shadow-2xl hover:-translate-y-1;
}

.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}
```

**Buttons:**
```css
.btn-primary {
  @apply bg-gradient-to-r from-primary-600 to-primary-700;
  @apply text-white font-medium px-6 py-3 rounded-lg;
  @apply shadow-md hover:shadow-xl;
  @apply transition-all duration-300;
  @apply hover:scale-105 active:scale-95;
}

.btn-secondary {
  @apply bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200;
  @apply border-2 border-gray-300 dark:border-gray-600;
  @apply px-6 py-3 rounded-lg font-medium;
  @apply hover:border-primary-500 hover:text-primary-600;
  @apply transition-all duration-300;
}
```

**Badges:**
```css
.badge {
  @apply inline-flex items-center px-3 py-1 rounded-full;
  @apply text-xs font-semibold;
  @apply transition-all duration-200;
}

.badge-success {
  @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
}

.badge-warning {
  @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200;
}

.badge-danger {
  @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
}

.badge-info {
  @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200;
}
```

**Input Fields:**
```css
.input-field {
  @apply w-full px-4 py-3 rounded-lg;
  @apply border-2 border-gray-300 dark:border-gray-600;
  @apply bg-white dark:bg-gray-800;
  @apply focus:border-primary-500 focus:ring-4 focus:ring-primary-100;
  @apply transition-all duration-200;
  @apply placeholder:text-gray-400;
}
```

**Modals:**
```css
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50;
  @apply backdrop-blur-sm;
  @apply flex items-center justify-center;
  @apply z-50 p-4;
  animation: fadeIn 0.3s ease-out;
}

.modal-content {
  @apply bg-white dark:bg-gray-800 rounded-2xl;
  @apply shadow-2xl max-w-2xl w-full;
  @apply max-h-[90vh] overflow-auto;
  animation: slideUp 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
```

### 5.3 Interactive Elements

**Hover Effects:**
```css
.interactive-card {
  @apply transition-all duration-300 ease-out;
  @apply hover:shadow-2xl hover:-translate-y-2;
  @apply cursor-pointer;
}

.hover-glow:hover {
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
}
```

**Loading States:**
```css
.skeleton {
  @apply bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200;
  @apply animate-pulse rounded;
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}
```

**Smooth Scrolling:**
```css
html {
  scroll-behavior: smooth;
}

.scrollbar-custom::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.scrollbar-custom::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
  border-radius: 10px;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
  @apply bg-gray-400 dark:bg-gray-600;
  border-radius: 10px;
  @apply hover:bg-gray-500;
}
```

**Gradient Backgrounds:**
```css
.gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-success {
  background: linear-gradient(135deg, #0093E9 0%, #80D0C7 100%);
}

.gradient-sunset {
  background: linear-gradient(135deg, #FA8BFF 0%, #2BD2FF 50%, #2BFF88 100%);
}
```

### 5.4 Responsive Breakpoints

```css
/* Mobile First Approach */
/* xs: 0-639px (default) */
/* sm: 640px+ */
@media (min-width: 640px) { }

/* md: 768px+ */
@media (min-width: 768px) { }

/* lg: 1024px+ */
@media (min-width: 1024px) { }

/* xl: 1280px+ */
@media (min-width: 1280px) { }

/* 2xl: 1536px+ */
@media (min-width: 1536px) { }
```

### 5.5 Dark Mode Support

```css
/* Automatic dark mode based on system preference */
@media (prefers-color-scheme: dark) {
  :root {
    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
  }
}

/* Toggle dark mode with class */
.dark {
  --bg-primary: #111827;
  --bg-secondary: #1f2937;
  --text-primary: #f9fafb;
  --text-secondary: #d1d5db;
}
```

---

## 6. Security Requirements

### 6.1 Authentication & Authorization
- Laravel Sanctum for API authentication
- Filament's built-in authentication
- Role-based access control (RBAC)
- Two-factor authentication (optional enhancement)
- Password requirements: min 8 chars, uppercase, lowercase, number
- Session timeout: 2 hours of inactivity
- Login attempt throttling: 5 attempts per minute

### 6.2 Data Security
- All sensitive data encrypted at rest
- HTTPS enforced in production
- SQL injection prevention via Eloquent ORM
- XSS protection via Laravel's Blade templating
- CSRF protection on all forms
- File upload validation and sanitization
- Secure file storage with restricted access

### 6.3 API Security
- API key authentication
- Rate limiting: 60 requests per minute per IP
- IP whitelisting option for webhook endpoints
- Request signing for sensitive operations
- API versioning
- Comprehensive API logging

### 6.4 Audit & Compliance
- Complete activity logging for all user actions
- Soft deletes for data recovery
- Regular automated backups
- GDPR compliance features:
  - Data export functionality
  - Right to be forgotten (data deletion)
  - Consent management
- Audit trail for financial data

---

## 7. Performance Requirements

### 7.1 Response Times
- Page load: < 2 seconds
- API responses: < 500ms
- Search queries: < 1 second
- Report generation: < 5 seconds
- File uploads: Progress indicator for files > 1MB

### 7.2 Optimization Strategies
- Database query optimization with indexes
- Eager loading for relationships
- Laravel query caching for reports
- Image optimization and lazy loading
- Asset minification and bundling
- CDN for static assets (production)
- Redis/Memcached for session and cache

### 7.3 Scalability
- Support for 100+ concurrent users
- Database connection pooling
- Queue system for heavy operations:
  - Email sending
  - Report generation
  - Bulk imports
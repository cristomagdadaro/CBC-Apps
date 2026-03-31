# Unified Emailing Module Implementation Plan

## Document Purpose
- This document defines a unified, agent-ready email and notification architecture for CBC-Apps.
- It covers transactional email delivery for generated certificates, form builder responses, equipment log lifecycle notifications, supply checkout notifications, and future module-level updates.
- The goal is to replace scattered `Mail::to(...)->send(...)` usage with a centralized, queue-first, policy-driven notification layer.

## Implementation Status
- Core notification orchestration is now implemented through shared config, recipient resolution, queued dispatch jobs, and `notification_logs` auditing.
- Certificate recipient delivery, form-response notifications, outgoing inventory notifications, and equipment-log lifecycle notifications are now routed through the unified notification layer.
- Recipient resolution now treats `Option` values as user-backed selections and resolves the actual email addresses from the `users` table.
- Remaining follow-through is mainly admin UX for selecting recipients, plus broader automated coverage across every notification domain.

## Current Baseline
- Certificates are emailed from [`app/Jobs/ProcessCertificateBatchJob.php`](/home/cris/dev/CBC-Apps/app/Jobs/ProcessCertificateBatchJob.php) using [`app/Mail/GeneratedCertificateMail.php`](/home/cris/dev/CBC-Apps/app/Mail/GeneratedCertificateMail.php).
- Form builder response notifications are sent directly from [`app/Observers/EventSubformResponseObserver.php`](/home/cris/dev/CBC-Apps/app/Observers/EventSubformResponseObserver.php) using [`app/Mail/EventSubformResponseNotification.php`](/home/cris/dev/CBC-Apps/app/Mail/EventSubformResponseNotification.php).
- Outgoing inventory transaction notifications are sent directly from [`app/Observers/TransactionObserver.php`](/home/cris/dev/CBC-Apps/app/Observers/TransactionObserver.php) using [`app/Mail/OutgoingTransactionNotification.php`](/home/cris/dev/CBC-Apps/app/Mail/OutgoingTransactionNotification.php).
- Recipient configuration is partly driven through `OptionRepo`, which is useful but not yet expressive enough for domain-specific recipients, escalation paths, or template control.
- Mail usage is inconsistent:
- some sends are queued
- some sends are immediate
- observer logic directly decides delivery
- there is no unified audit or status model for notification attempts

## Target Outcomes
- One consistent notification pipeline for all app emails.
- Queue-first delivery with retry, auditability, and failure handling.
- Per-module recipient rules and template ownership.
- Shared notification preferences and options without scattering logic across observers.
- Clear separation between domain events, notification decisions, and mail rendering.
- Reusable enough to support future SMS, in-app, and websocket notification fan-out.

## Design Principles
- Domain events describe what happened; notification orchestrators decide who gets notified and through which channels.
- Mailables should focus on rendering, not business rules.
- Notifications should be queued by default.
- Recipient resolution must be centralized and testable.
- Notification content should be template-driven and grouped by domain.
- Sensitive data must be minimized; emails should include only what each audience genuinely needs.
- Email sending should be idempotent where duplicate dispatch is possible.

## Recommended Target Architecture

### Core Layers
- Domain events
- Notification orchestrators
- Recipient resolver
- Notification templates
- Delivery audit

### Recommended Folder Structure
- `app/Listeners/*` for orchestration
- `app/Notifications/*` for Laravel notification classes where multi-channel support is useful
- `app/Mail/*` for mail-specific renderers that remain worth keeping
- `app/Services/Notifications/*` for recipient resolution, template selection, and dispatch coordination
- `app/Support/Notifications/*` for DTOs or value objects such as notification context, recipient group, and template data

## Why Move Toward Laravel Notifications
- Laravel Notifications provide a clean upgrade path from email-only to email plus database plus broadcast later.
- They centralize `via()`, queuing behavior, and recipient-specific formatting.
- They fit well with Reverb if in-app notifications are added later.
- Mailables can still be used under the hood where attachment-heavy rendering is convenient, especially for certificates.

## Domain Coverage

### 1. Certificates
- Use case: send generated certificate attachments to recipients after successful batch processing.
- Keep attachment generation in the batch job, but move delivery decision-making into a notification dispatcher or service.
- Add support for:
- batch summary email to authorized staff
- recipient delivery retries
- optional failure summary to staff when a batch partially fails

### 2. Form Builder Responses
- Use case: notify configured recipients when a new event subform response is received.
- Requirements:
- event-specific recipients should override global fallback recipients
- support different templates by subform type if needed
- queue delivery
- optionally digest high-volume forms later

### 3. Equipment Log Lifecycle
- Use case: notify specific people when an equipment log is created, completed, or becomes overdue.
- Events to support:
- equipment log created or check-out
- equipment log completed or check-in
- equipment log overdue
- Recipient rules may include:
- laboratory managers
- ICT managers
- assigned custodians or owners if modeled
- department-specific notification lists

### 4. Supply Checkout / Outgoing Inventory
- Use case: notify designated inventory staff when supply checkout is triggered.
- Distinguish between public outgoing requests and internal stock adjustments.
- Include enough context to act quickly without exposing unnecessary personal information.

## Notification Policy Model

### Recommended Configuration Categories
- global mail sender defaults
- per-module enable or disable flags
- per-module recipient groups
- per-event escalation rules
- digest versus immediate delivery mode
- severity routing for overdue and failed actions

### Suggested Config Sources
- keep defaults in config files for versioned behavior
- allow overrides in system options for runtime and admin control
- avoid magic option keys scattered through `OptionRepo`

### Suggested New Config File
- [`config/notifications.php`](/home/cris/dev/CBC-Apps/config/notifications.php)

### Example Domains in Config
- `certificates`
- `forms.responses`
- `inventory.checkout`
- `laboratory.logs`
- `ict.logs`
- `jobs.failures`

## Recipient Resolution Strategy

### Recommended Recipient Sources
- explicitly configured email addresses in system options
- role-based users
- event owners or coordinators
- module managers
- fallback global notification mailbox

### Implement a Dedicated Resolver
- Suggested service:
- `App\Services\Notifications\RecipientResolver`
- Responsibilities:
- load module-specific configured emails
- resolve role-based recipients from users
- de-duplicate recipients
- validate active users and valid addresses
- return recipient collections grouped by purpose such as `to`, `cc`, and `bcc`

## Template Strategy

### Recommended Template Groups
- certificates
- form responses
- equipment log lifecycle
- supply checkout
- operational alerts and failures

### Rendering Guidance
- Use a shared branded layout for all system emails.
- Keep subject line conventions predictable.
- Make emails mobile-friendly and skim-friendly.
- Include direct authorized links back to the relevant module page.
- Avoid embedding private data that the recipient can instead view after authentication.

## Delivery Audit and Observability

### Add a Notification Log Model
- Suggested table: `notification_logs`
- Suggested fields:
- `id`
- `domain`
- `event_key`
- `notifiable_type`
- `notifiable_id`
- `recipient_email`
- `channel`
- `status`
- `queued_at`
- `sent_at`
- `failed_at`
- `failure_reason`
- `payload_meta`
- `correlation_id`

### Benefits
- trace who was notified
- support troubleshooting
- prevent silent failures
- create admin reporting later

## Queueing and Reliability
- All operational emails should be queued by default.
- Define dedicated queue names where helpful:
- `mail`
- `notifications`
- `certificates`
- Large certificate batches should queue recipient deliveries in a way that avoids one huge job doing too much synchronous work.
- Add retry or backoff policies for transient SMTP or provider failures.
- Add failure hooks for staff summary alerts when an important notification pipeline repeatedly fails.

## Recommended Refactor Path by Current Module

### Certificates
- Keep `ProcessCertificateBatchJob` responsible for generation and manifest parsing.
- Extract email dispatch into a dedicated service, for example `CertificateNotificationDispatcher`.
- Continue using `GeneratedCertificateMail` initially, then evaluate converting to a Notification wrapper if multi-channel delivery is wanted.
- Add batch-level summary notification to staff with counts and any failures.

### Event Subform Responses
- Replace direct `Mail::send()` inside `EventSubformResponseObserver` with dispatch of a domain event such as `EventResponseCreated`.
- Handle email in a queued listener or notification dispatcher.
- Support event-level recipient override and global fallback.

### Outgoing Transactions / Supply Checkout
- Replace direct send in `TransactionObserver` with a domain event such as `SupplyCheckoutTriggered` or `InventoryTransactionCreated`.
- Route notifications only for outgoing or supply-checkout-relevant records.
- Add clear inventory recipient resolution rather than reusing the event response mailbox.

### Equipment Logs
- Introduce domain events from `LaboratoryLogService` and any ICT-equivalent flow.
- Add notification listeners:
- `SendEquipmentLogCreatedNotification`
- `SendEquipmentLogCompletedNotification`
- `SendEquipmentLogOverdueNotification`
- Overdue notifications should also be triggered from the existing overdue command path, not only from UI actions.

## Unified Notification Service Layer

### Suggested Services
- `NotificationPreferenceService`
- `RecipientResolver`
- `NotificationDispatchService`
- `NotificationAuditService`
- `NotificationTemplateContextFactory`

### Suggested Responsibilities
- `NotificationPreferenceService`
  - read config, system options, and feature flags
- `RecipientResolver`
  - determine recipients by domain and event
- `NotificationDispatchService`
  - send or queue notifications and coordinate audit logging
- `NotificationAuditService`
  - persist send lifecycle
- `NotificationTemplateContextFactory`
  - produce normalized mail-view data

## Integration with Realtime
- Notification domain events should be reusable for both email and websocket fan-out.
- Example:
- `EquipmentLogMarkedOverdue` can trigger:
- staff websocket update through Reverb
- email notification to managers
- dashboard counter refresh
- This keeps �what happened� separate from �how we notify�.

## Security and Compliance Requirements
- Do not send sensitive raw payload dumps by email.
- Keep certificate attachments only for intended recipients and authorized staff summaries.
- Prefer links to authenticated pages over including full internal notes.
- Sanitize public-originated submissions before forwarding to internal recipients.
- Ensure notification recipients are explicit and reviewable.

## Testing Strategy

### Backend Tests
- Notification dispatch tests for each domain.
- Recipient resolution tests for options, role-based recipients, and deduplication.
- Queueing assertions instead of synchronous delivery assertions.
- Audit log persistence tests.
- Failure handling tests for mail transport exceptions.

### Integration Tests
- certificate batch success or failure summary emails
- event subform response creates one queued notification with correct recipient resolution
- outgoing supply checkout triggers inventory notifications only
- equipment overdue command triggers overdue email notification once

## Rollout Strategy

### Milestone 1
- Introduce shared notification config, resolver, and audit model.
- Migrate form response and outgoing inventory notification flows first.

### Milestone 2
- Migrate certificate recipient delivery and add staff batch summaries.

### Milestone 3
- Implement equipment log created, completed, and overdue notifications.

### Milestone 4
- Add admin UI or system-options controls for per-module recipients and toggles if needed.

## Implementation Checklist
- [x] Create `config/notifications.php`.
- [x] Add notification preferences and recipient resolution service layer.
- [x] Add `notification_logs` migration, model, and audit service.
- [x] Standardize queue-first notification dispatch.
- [x] Refactor form response emails off direct observer `Mail::send()`.
- [x] Refactor outgoing inventory or supply checkout emails off direct observer `Mail::send()`.
- [x] Extract certificate email dispatch into a dedicated notification dispatcher.
- [x] Add staff-facing certificate batch summary notifications.
- [x] Add laboratory equipment log lifecycle notification events and listeners.
- [x] Add ICT equipment log lifecycle notification events and listeners if separate domain logic exists.
- [x] Wire overdue command processing to notification dispatch.
- [x] Add recipient deduplication and validation safeguards.
- [x] Add feature tests for recipient resolution and dispatch flow.
- [x] Add operational failure logging and retry guidance.
- [x] Update Copilot instructions and manuals after implementation.
- [ ] Add or refine admin UX for selecting notification recipients from the `users` table without raw JSON editing.

## Agent Handoff Notes
- Start by centralizing recipient resolution and audit logging before converting every mail flow.
- Preserve current templates initially where they already work; unify orchestration first, redesign visuals second.
- Prefer queued listeners or notifications over direct observer mail sends.
- If new gaps or risks appear during implementation, record them in [`docs/codebase-analysis-report-2026-03-25.md`](/home/cris/dev/CBC-Apps/docs/codebase-analysis-report-2026-03-25.md) before proceeding.


## Special Note
Allow the administrator to chose who will get system notifications could be multiple emails one notification. Use Option Model to set who will receive notifications it must be from the users table email only.

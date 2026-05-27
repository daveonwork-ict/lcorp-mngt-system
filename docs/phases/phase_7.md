```markdown
# RC STORE RMS — PHASE 7
# ANNOUNCEMENT, COMMUNICATION & CHAT
# BUILD → VALIDATE → FIX → GATEWAY PROMPT

STRICTLY FOLLOW global_master.md.

DO NOT SKIP.
DO NOT HALLUCINATE.
DO NOT CREATE PLACEHOLDER IMPLEMENTATIONS.
DO NOT BREAK EXISTING LOGIC.
DO NOT HARD CODE VALUES.
DO NOT IGNORE MULTI-BRANCH SUPPORT.
DO NOT IGNORE RESPONSIVENESS.
DO NOT IGNORE SECURITY.
DO NOT IGNORE AUDIT LOGGING.
DO NOT IGNORE NOTIFICATION INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Announcement, Communication, and Chat Module for RC Store RMS.

This phase must centralize internal communication between:

- Owner
- Management
- Branch Managers
- Cashiers
- Inventory Staff
- Accounting Staff
- Auditors
- Other authorized users

The goal is to reduce scattered communication, improve traceability, and provide an organized internal communication platform for branch operations.

This module must support:

- Company-wide announcements
- Branch-specific announcements
- Role-specific announcements
- Urgent notices
- Internal chat
- Private messages
- Group chat
- Read receipts
- File/image sharing
- Notification alerts

---

# BUILD SECTION

## 1. ANNOUNCEMENT MODULE

Create announcement management.

Announcement types:

- Company-wide announcement
- Branch-specific announcement
- Role-specific announcement
- Department/group announcement
- Urgent notice
- Policy update
- Promotion update
- Inventory notice
- Maintenance notice
- System notice

Features:

- Create announcement
- Edit announcement
- View announcement
- Archive announcement
- Publish announcement
- Schedule announcement
- Set expiration date
- Pin announcement
- Mark as urgent
- Attach files/images
- Target audience
- Track read acknowledgment

---

## 2. ANNOUNCEMENT FIELDS

Announcement fields:

- announcement_number
- title
- content
- announcement_type
- priority_level
- target_scope
- publish_start_at
- publish_end_at
- is_pinned
- is_urgent
- status
- created_by
- approved_by nullable
- published_at nullable

Priority levels:

- Normal
- Important
- Urgent
- Critical

Statuses:

- Draft
- Scheduled
- Published
- Expired
- Archived
- Cancelled

---

## 3. ANNOUNCEMENT TARGETING

Support announcement targeting by:

- All users
- Specific branch
- Multiple branches
- Specific role
- Multiple roles
- Specific users
- Management only
- Cashiers only
- Inventory staff only
- Accounting only

Target fields:

- announcement_id
- target_type
- target_id nullable

Rules:

- Users can only see announcements targeted to them
- Branch users can only see allowed branch announcements
- Owner can view all announcements
- Urgent announcements must be highlighted
- Expired announcements must not appear as active

---

## 4. ANNOUNCEMENT ATTACHMENTS

Support attachments.

Allowed files:

- JPG
- JPEG
- PNG
- PDF
- DOC
- DOCX
- XLS
- XLSX

Attachment fields:

- announcement_id
- file_name
- file_path
- file_type
- file_size
- uploaded_by

Rules:

- Validate file type
- Validate file size
- Store securely
- Authorized preview/download only
- Do not expose confidential attachments publicly

---

## 5. ANNOUNCEMENT READ ACKNOWLEDGMENT

Create announcement read tracking.

Track:

- announcement_id
- user_id
- branch_id nullable
- read_at
- acknowledgment_status

Acknowledgment statuses:

- Unread
- Read
- Acknowledged

Features:

- Mark as read
- Require acknowledgment for urgent announcements
- View who has read
- View who has not read
- Filter read status by branch
- Filter read status by role

---

## 6. CHAT MODULE

Create internal chat system.

Chat types:

- Private message
- Branch group chat
- Management group chat
- Inventory group chat
- Accounting group chat
- Operations group chat
- Custom group chat

Features:

- Create chat room
- Rename chat room
- Add members
- Remove members
- Assign group admin
- Archive chat room
- Send messages
- Edit own message
- Delete own message if allowed
- Reply to message
- Mention users
- Attach image/file
- Read receipts
- Unread count

---

## 7. CHAT ROOM MANAGEMENT

Chat room fields:

- room_number
- room_name
- room_type
- branch_id nullable
- created_by
- status

Room types:

- Private
- Branch
- Management
- Inventory
- Accounting
- Operations
- Custom

Statuses:

- Active
- Archived
- Disabled

Chat room member fields:

- chat_room_id
- user_id
- role_in_room
- joined_at
- status

Member roles:

- Admin
- Member
- Viewer

Rules:

- Users can only access rooms where they are members
- Branch chat rooms must be branch-restricted
- Private messages must only be visible to participants
- Archived rooms cannot accept new messages unless reopened

---

## 8. CHAT MESSAGE MANAGEMENT

Message fields:

- chat_room_id
- sender_id
- branch_id nullable
- message_body nullable
- message_type
- parent_message_id nullable
- edited_at nullable
- deleted_at nullable
- status

Message types:

- Text
- Image
- File
- System

Statuses:

- Sent
- Edited
- Deleted

Rules:

- Message cannot be empty unless attachment exists
- Sender must be a chat room member
- Users cannot edit other users’ messages unless authorized
- Deleted messages must be soft-deleted
- Sensitive attachments must be access-controlled

---

## 9. CHAT ATTACHMENTS

Support message attachments.

Allowed files:

- JPG
- JPEG
- PNG
- PDF
- DOC
- DOCX
- XLS
- XLSX

Attachment fields:

- chat_message_id
- file_name
- file_path
- file_type
- file_size
- uploaded_by

Rules:

- Validate file type
- Validate file size
- Store securely
- Authorized preview/download only
- File downloads must be permission-protected

---

## 10. CHAT READ RECEIPTS

Create message read tracking.

Fields:

- chat_message_id
- user_id
- read_at

Features:

- Mark messages as read
- Show unread count
- Show read receipt indicator
- Track unread messages by room
- Update notification count

---

## 11. NOTIFICATION CENTER ENHANCEMENT

Enhance notification system to support:

- Announcement notifications
- Chat notifications
- Mention notifications
- Urgent announcement alerts
- Branch-targeted notifications
- Role-targeted notifications
- User-targeted notifications

Notification features:

- Notification bell
- Unread count
- Mark as read
- Notification list
- Redirect to reference page
- Notification category

---

## 12. COMMUNICATION DASHBOARD

Create communication dashboard.

Cards:

- Active announcements
- Urgent announcements
- Unread announcements
- Active chat rooms
- Unread messages
- Pending acknowledgments
- Recent announcements
- Recent messages

Tables:

- Latest announcements
- Urgent notices
- Unread announcement users
- Active chat rooms

---

## 13. REAL-TIME READINESS

Prepare communication architecture for real-time usage.

Options:

- Laravel broadcasting readiness
- WebSocket readiness
- Polling fallback
- PWA push notification readiness

Do not implement unstable real-time code if infrastructure is not available.

Prepare clean upgrade-ready structure.

---

# DATABASE REQUIREMENTS

Create or update migrations:

- announcements
- announcement_targets
- announcement_attachments
- announcement_reads
- chat_rooms
- chat_room_members
- chat_messages
- chat_message_attachments
- chat_message_reads
- communication_notifications

Relationships:

- Announcement belongs to creator/user
- Announcement has many targets
- Announcement has many attachments
- Announcement has many read records
- Chat room has many members
- Chat room has many messages
- Chat message belongs to sender/user
- Chat message may belong to parent message
- Chat message has many attachments
- Chat message has many read records
- User has many chat rooms through membership

---

# BACKEND REQUIREMENTS

Create controllers:

- AnnouncementController
- AnnouncementTargetController
- AnnouncementAttachmentController
- AnnouncementReadController
- ChatRoomController
- ChatRoomMemberController
- ChatMessageController
- ChatAttachmentController
- ChatReadController
- NotificationCenterController
- CommunicationDashboardController

Create services:

- AnnouncementService
- AnnouncementTargetService
- AnnouncementReadService
- ChatRoomService
- ChatMessageService
- ChatAttachmentService
- ChatReadService
- NotificationService
- CommunicationPermissionService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Communication dashboard
- Announcement list
- Announcement create/edit form
- Announcement details page
- Announcement read tracking page
- Chat room list
- Chat conversation screen
- Chat member management page
- Notification center

UI requirements:

- Announcement cards
- Priority badges
- Urgent labels
- Pinned announcement section
- Chat bubble layout
- Responsive message panel
- Mobile-friendly chat input
- Attachment preview
- Unread count badges
- Read receipt indicators
- Branch and role filters

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_announcements
- create_announcement
- edit_announcement
- publish_announcement
- archive_announcement
- view_announcement_reads
- access_chat
- create_chat_room
- manage_chat_room_members
- send_chat_message
- edit_chat_message
- delete_chat_message
- view_notification_center

Rules:

- Users can only view announcements targeted to them
- Users can only access chat rooms where they are members
- Branch users can only access assigned branch communications
- Announcement publishing is restricted
- Chat room management is restricted
- Attachments are access-controlled
- Message deletion is permission-based
- Owner can view all communications if allowed by configuration

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Announcement created
- Announcement edited
- Announcement published
- Announcement archived
- Announcement deleted
- Announcement attachment uploaded
- Announcement viewed
- Chat room created
- Chat room updated
- Chat member added
- Chat member removed
- Message sent
- Message edited
- Message deleted
- File shared
- Notification read

Audit must include:

- user_id
- branch_id
- module_name
- action_type
- before_value
- after_value
- ip_address
- user_agent
- created_at

---

# NOTIFICATION REQUIREMENTS

Generate notifications for:

- New announcement
- Urgent announcement
- Scheduled announcement published
- Announcement requiring acknowledgment
- User mention
- New private message
- New group message
- File shared
- Unread urgent notice reminder

---

# VALIDATE SECTION

Validate:

## Announcements
- Announcement creation works
- Announcement editing works
- Publishing works
- Scheduling works
- Expiration works
- Targeting works
- Attachments work
- Read acknowledgment works

## Chat
- Chat room creation works
- Member management works
- Sending message works
- Editing message works
- Deleting message works
- Replying works
- Mentions work
- Attachments work
- Read receipts work

## Notifications
- Announcement notifications work
- Chat notifications work
- Mention notifications work
- Unread count works
- Mark as read works

## Security
- Announcement visibility is restricted
- Chat room access is restricted
- Attachment access is protected
- Branch restrictions work
- Permissions work

## UI
- Announcement pages are responsive
- Chat pages are responsive
- Message panel works on mobile
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix announcement targeting
- Fix unauthorized visibility
- Fix read acknowledgment
- Fix attachment access
- Fix notification count
- Fix chat room permission leaks
- Fix message sending errors
- Fix mention notifications
- Fix mobile chat layout
- Fix slow message loading
- Refactor duplicated communication logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 7 complete, verify:

- global_master.md is followed
- announcement module is complete
- announcement targeting works
- announcement attachments are secure
- read acknowledgment works
- chat room module is complete
- private messaging works
- group messaging works
- chat attachments are secure
- read receipts work
- notification center works
- branch restrictions are enforced
- audit logs are complete
- no unauthorized announcement access
- no unauthorized chat room access
- no insecure file access
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 7, provide:

- Complete announcement module
- Complete announcement targeting
- Complete announcement attachments
- Complete announcement read acknowledgment
- Complete chat room module
- Complete private messaging
- Complete group messaging
- Complete chat attachments
- Complete message read receipts
- Complete notification center enhancement
- Complete communication dashboard
- Real-time-ready communication architecture
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive communication UI

PHASE 7 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
```

# RC STORE RMS — PHASE 17

# STAFF MANAGEMENT, SCHEDULING, PAYROLL & LOANS MANAGEMENT

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
DO NOT IGNORE PHILIPPINE LABOR AND PAYROLL REQUIREMENTS.

---

# PHASE OBJECTIVE

Build a complete Human Resource Management System (HRMS) module integrated with RC Store RMS.

This module shall centralize:

* Employee records
* Branch assignments
* Scheduling
* Attendance
* Selfie Time-In / Time-Out
* GPS Attendance Validation
* Leave Monitoring
* Overtime Monitoring
* Payroll Processing
* Government Contributions
* Cash Advances
* Employee Loans
* Loan Deductions
* Payroll Approval Workflow
* Payslip Generation
* HR Analytics

---

# MODULE 1 — STAFF PROFILE MANAGEMENT

Create complete employee records management.

Employee Information:

* Employee Number
* Full Name
* Photo
* Mobile Number
* Email Address
* Address
* Birthdate
* Gender
* Civil Status
* Emergency Contact
* Employment Date
* Employment Type
* Position
* Department
* Branch Assignment
* Employment Status
* Salary Type
* Salary Rate

Employment Types:

* Regular
* Probationary
* Contractual
* Project-Based
* Part-Time
* Casual

Employment Status:

* Active
* Inactive
* Resigned
* Terminated
* On Leave

Features:

* Employee Masterlist
* Employee Profile
* Employee Documents
* Branch Assignment
* Position History
* Employment History

---

# MODULE 2 — POSITION & SALARY MANAGEMENT

Create Position Management.

Fields:

* Position Code
* Position Name
* Department
* Default Salary Rate
* Salary Type

Salary Types:

* Monthly
* Daily
* Hourly

Features:

* Create Position
* Edit Position
* Salary Rate Setup
* Branch-Based Position Assignment

---

# MODULE 3 — STAFF SCHEDULING

Create Branch Schedule Management.

Schedule Types:

* Fixed Schedule
* Rotating Schedule
* Flexible Schedule

Fields:

* Branch
* Employee
* Schedule Date
* Time In
* Time Out
* Break Start
* Break End
* Rest Day Flag

Features:

* Weekly Schedule
* Monthly Schedule
* Shift Assignment
* Rest Day Assignment
* Schedule Copying

---

# MODULE 4 — LEAVE MANAGEMENT

Leave Types:

* Vacation Leave
* Sick Leave
* Emergency Leave
* Maternity Leave
* Paternity Leave
* Bereavement Leave
* Leave Without Pay

Workflow:

Employee
→ Leave Request
→ Manager Review
→ HR Review
→ Approval/Rejection

Features:

* Leave Credits
* Leave Balances
* Leave Calendar
* Leave History

---

# MODULE 5 — SELFIE TIME-IN / TIME-OUT

Create Attendance Monitoring.

Attendance Methods:

* Selfie Check-In
* Selfie Check-Out

Capture:

* Selfie Image
* Timestamp
* Device Information
* GPS Coordinates
* IP Address

Features:

* Mobile Attendance
* PWA Attendance
* Branch Attendance Validation
* Attendance Verification

Rules:

* Selfie Required
* Timestamp Required
* Device Information Required
* GPS Optional Based On Configuration

---

# MODULE 6 — ATTENDANCE MANAGEMENT

Attendance Fields:

* Employee
* Branch
* Date
* Schedule
* Time In
* Time Out
* Late Minutes
* Undertime Minutes
* Overtime Minutes
* Attendance Status

Statuses:

* Present
* Late
* Absent
* Undertime
* Overtime
* Leave
* Holiday

---

# MODULE 7 — OVERTIME MANAGEMENT

Workflow:

Employee Request
→ Manager Approval
→ HR Validation
→ Payroll Inclusion

Fields:

* Date
* Hours
* Reason
* Approved By

Rules:

* Payroll Integration Required
* Overtime Rate Configurable

---

# MODULE 8 — PHILIPPINE PAYROLL SETUP

Payroll Period Types:

* Weekly
* Semi-Monthly
* Monthly

Payroll Components:

Earnings

* Basic Pay
* Overtime
* Allowances
* Holiday Pay
* Night Differential
* Incentives

Deductions

* SSS
* PhilHealth
* Pag-IBIG
* Withholding Tax
* Loans
* Cash Advances
* Other Deductions

---

# MODULE 9 — PH GOVERNMENT CONTRIBUTIONS

Create configurable contribution tables.

SSS

* Employer Share
* Employee Share
* MSC Table

PhilHealth

* Employee Share
* Employer Share
* Premium Table

Pag-IBIG

* Employee Share
* Employer Share

Withholding Tax

* BIR Tax Table
* TRAIN Law Ready

Rules:

* Tables must be configurable.
* No hardcoded rates.

---

# MODULE 10 — PAYROLL COMPUTATION

Process:

Attendance
→ Salary Computation
→ Government Contributions
→ Loans
→ Cash Advances
→ Deductions
→ Net Pay

Payroll Status:

* Draft
* Pending Approval
* Approved
* Released
* Cancelled

---

# MODULE 11 — CASH ADVANCES

Features:

* Request
* Approval
* Balance Monitoring
* Payroll Deduction

Workflow:

Employee
→ Request
→ Approval
→ Deduction Schedule

---

# MODULE 12 — LOAN MANAGEMENT

Loan Types:

* Company Loan
* Salary Loan
* Emergency Loan
* SSS Loan
* Pag-IBIG Loan
* Other Loan

Fields:

* Loan Number
* Employee
* Principal Amount
* Interest
* Installment
* Remaining Balance

Workflow:

Employee Request
→ Approval
→ Loan Release
→ Payroll Deduction

Rules:

* Loan Creation Requires Approval
* Loan Adjustment Requires Approval

---

# MODULE 13 — LOAN PAYMENT SCHEDULE

Features:

* Installment Schedule
* Remaining Balance
* Loan History
* Payroll Deduction Tracking

Statuses:

* Active
* Paid
* Defaulted
* Cancelled

---

# MODULE 14 — PAYROLL APPROVAL WORKFLOW

Process:

Payroll Generated
→ HR Review
→ Manager Approval
→ Owner Approval
→ Payroll Release

Rules:

* Payroll cannot be released without approval.
* Payroll modifications must be logged.

---

# MODULE 15 — PAYSLIP MANAGEMENT

Generate Payslips.

Payslip Content:

* Employee Details
* Payroll Period
* Earnings
* Deductions
* Government Contributions
* Loan Deductions
* Net Pay

Export:

* PDF
* Print

---

# MODULE 16 — REPORTS

Reports:

HR Reports

* Employee Masterlist
* Employment Status Report
* Branch Staffing Report

Attendance Reports

* Daily Attendance
* Monthly Attendance
* Late Report
* Overtime Report

Payroll Reports

* Payroll Register
* Payslip Register
* Government Contributions Report
* Loan Deduction Report

Loan Reports

* Active Loans
* Paid Loans
* Outstanding Balances

---

# MODULE 17 — HR DASHBOARD

Cards:

* Total Employees
* Active Employees
* Employees Per Branch
* Pending Leave Requests
* Pending Payroll
* Active Loans
* Attendance Today
* Absent Today

Charts:

* Attendance Trend
* Payroll Trend
* Loan Trend
* Branch Staffing Analysis

---

# SECURITY REQUIREMENTS

Branch Managers

* View Assigned Branch Staff Only

Payroll Users

* Payroll Access Only

HR

* Full Employee Access

Selfie Attendance Must Store:

* Image
* Timestamp
* Device Information
* GPS Coordinates
* IP Address

Loan Creation

* Approval Required

Loan Adjustment

* Approval Required

Payroll Changes

* Audit Logged

Payslip Access

* Restricted

---

# AUDIT TRAIL REQUIREMENTS

Log:

* Employee Created
* Employee Updated
* Schedule Updated
* Attendance Recorded
* Leave Approved
* Payroll Generated
* Payroll Approved
* Payroll Released
* Loan Created
* Loan Adjusted
* Cash Advance Released
* Payslip Generated

---

# EXPECTED OUTPUT

At the end of Phase 17 provide:

* Complete HR Management Module
* Complete Staff Profile Management
* Complete Branch Assignment
* Complete Scheduling
* Complete Leave Management
* Complete Selfie Attendance
* Complete GPS Attendance
* Complete Attendance Monitoring
* Complete Payroll Management
* Complete Government Contributions
* Complete Cash Advance Management
* Complete Loan Management
* Complete Payroll Approval Workflow
* Complete Payslip Generation
* Complete HR Reports
* Complete HR Dashboard
* Complete Security Controls
* Complete Audit Trail

PHASE 17 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.

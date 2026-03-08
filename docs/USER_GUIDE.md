# Daily Time Record — User Guide

## Overview

The Daily Time Record (DTR) system tracks employee attendance through a mobile check-in app and provides an admin web dashboard for monitoring and analytics.

## Getting Started

### Login

Navigate to `/login` and sign in with your admin credentials.

- **Default admin**: `admin@example.com` / `password`

### Dashboard

After login, you land on the **Dashboard** which shows:

- **Summary Cards** — Total employees, present today, absent today, average hours worked
- **Monthly Attendance Trend** — A visual bar chart showing daily attendance for the current month
- **Recent Check-ins** — The last 10 check-ins across all employees

### Employees

Navigate to **Employees** from the sidebar to see:

- A searchable, paginated table of all employees
- Click an employee's ID number to view their detail page
- Each employee page shows:
  - Profile info (ID, department, position, status)
  - Attendance history for the current month (time in, time out, hours)
  - Recent check-in log with GPS coordinates

### Attendance

Navigate to **Attendance** from the sidebar:

- View daily attendance for all employees on a specific date
- Use the date picker to change the date
- Each row shows: employee name, time in, time out, hours worked, and present/absent status

## How Attendance Works

- **Time In**: The first check-in of the day
- **Time Out**: The last check-in of the day (only if there are 2+ check-ins)
- **Total Hours**: Time Out minus Time In
- If an employee only checks in once, Time Out and Hours are blank

## Seed Data

The system comes pre-seeded with:

- **3 employees**: Juan Dela Cruz (EMP-001), Maria Santos (EMP-002), Jose Reyes (EMP-003)
- **1 month of check-in data**: February 2026, weekdays only, morning (07:30–09:00) and evening (16:30–18:00)

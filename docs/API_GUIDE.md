# Daily Time Record — API Developer Guide

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All endpoints (except token issuance) require a Bearer token in the `Authorization` header.

### Obtain a Token

```
POST /api/v1/auth/token
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "my-phone"
}
```

**Response (201):**

```json
{
  "token": "1|abc123...",
  "user": { "id": 1, "name": "Admin User", "email": "admin@example.com" }
}
```

Use the token in all subsequent requests:

```
Authorization: Bearer 1|abc123...
```

### Revoke Token

```
DELETE /api/v1/auth/token
Authorization: Bearer {token}
```

---

## Employees

### List Employees

```
GET /api/v1/employees?search={term}&per_page=15&active_only=true
```

Query parameters:
- `search` — Filter by name or ID number
- `per_page` — Results per page (default: 15)
- `active_only` — Boolean, show only active employees

### Create Employee

```
POST /api/v1/employees
Content-Type: application/json

{
  "id_number": "EMP-004",
  "first_name": "Anna",
  "last_name": "Garcia",
  "department": "Marketing",
  "position": "Designer"
}
```

Required fields: `id_number`, `first_name`, `last_name`

### Show Employee

```
GET /api/v1/employees/{id}
```

### Update Employee

```
PUT /api/v1/employees/{id}
Content-Type: application/json

{
  "first_name": "Updated Name"
}
```

Only include fields you want to change.

### Deactivate Employee

```
DELETE /api/v1/employees/{id}
```

Sets `is_active` to `false` (soft-delete).

### Employee Attendance

```
GET /api/v1/employees/{id}/attendance?start_date=2026-02-01&end_date=2026-02-28
```

Required: `start_date`, `end_date`

Returns an array of daily attendance records:

```json
{
  "data": [
    {
      "date": "2026-02-10",
      "time_in": "08:00:00",
      "time_out": "17:00:00",
      "total_hours": 9.0
    }
  ]
}
```

---

## Check-ins

### Create Check-in

```
POST /api/v1/checkins
Content-Type: multipart/form-data

Fields:
  id_number:     EMP-001          (required, must exist in employees)
  latitude:      14.5547          (required, -90 to 90)
  longitude:     121.0244         (required, -180 to 180)
  location_name: Manila Office    (optional)
  selfie:        [file]           (required, image, max 5MB)
  captured_at:   2026-03-08 08:00:00  (required, datetime)
```

**Note:** This endpoint uses `multipart/form-data` because it includes a file upload.

### List Check-ins

```
GET /api/v1/checkins?date=2026-02-10&employee_id=1&id_number=EMP-001&per_page=15
```

All query parameters are optional. Results are ordered by most recent first.

---

## Attendance

### Daily Attendance

```
GET /api/v1/attendance?date=2026-02-10&start_date=2026-02-01&end_date=2026-02-28&employee_id=1
```

Returns computed attendance for all active employees:

```json
{
  "data": [
    {
      "employee": { "id": 1, "id_number": "EMP-001", "full_name": "Juan Dela Cruz" },
      "date": "2026-02-10",
      "time_in": "08:00:00",
      "time_out": "17:00:00",
      "total_hours": 9.0
    }
  ]
}
```

### Attendance Summary (Dashboard Stats)

```
GET /api/v1/attendance/summary?date=2026-02-10
```

```json
{
  "data": {
    "date": "2026-02-10",
    "total_employees": 3,
    "present_today": 3,
    "absent_today": 0,
    "average_hours": 8.5,
    "recent_checkins": [...]
  }
}
```

---

## Error Handling

All errors return JSON with the following structure:

**Validation Error (422):**

```json
{
  "message": "The id number field is required.",
  "errors": {
    "id_number": ["The id number field is required."]
  }
}
```

**Unauthorized (401):**

```json
{
  "message": "Unauthenticated."
}
```

**Not Found (404):**

```json
{
  "message": "No query results for model [App\\Models\\Employee] 999"
}
```

---

## Postman Collection

Import `postman_collection.json` from the project root into Postman. The collection includes:

1. Set the `base_url` variable (default: `http://localhost:8000/api/v1`)
2. Run **Auth > Issue Token** first
3. Copy the returned `token` value into the collection variable `token`
4. All other requests will use this token automatically

## Attendance Logic

- **Time In** = first check-in of the day for an employee
- **Time Out** = last check-in of the day (only when 2+ check-ins exist)
- **Total Hours** = difference between Time In and Time Out
- Single check-in days show `time_out: null` and `total_hours: null`

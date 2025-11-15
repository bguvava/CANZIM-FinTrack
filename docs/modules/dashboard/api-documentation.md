# Dashboard API Documentation

## Base URL

```
/api/v1/dashboard
```

All dashboard endpoints require authentication via Laravel Sanctum. Include the bearer token in the Authorization header.

## Authentication

All requests must include:

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Endpoints

### 1. Get Dashboard Data

Retrieves role-specific dashboard data for the authenticated user.

**Endpoint**: `GET /api/v1/dashboard`

**Authentication**: Required

**Request Headers**:

```http
GET /api/v1/dashboard HTTP/1.1
Host: localhost
Authorization: Bearer {token}
Accept: application/json
```

**Response (Programs Manager)**:

```json
{
    "kpis": {
        "total_budget": 0,
        "ytd_spending": 0,
        "available_funds": 0,
        "pending_approvals": 0
    },
    "charts": {
        "budget_utilization": {
            "labels": ["Project 1", "Project 2", "Project 3", "Project 4"],
            "datasets": [
                {
                    "label": "Budget Utilization",
                    "data": [0, 0, 0, 0],
                    "backgroundColor": [
                        "#1E40AF",
                        "#2563EB",
                        "#60A5FA",
                        "#93C5FD"
                    ]
                }
            ]
        },
        "expense_trends": {
            "labels": [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec"
            ],
            "datasets": [
                {
                    "label": "Actual Expenses",
                    "data": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    "borderColor": "#1E40AF",
                    "backgroundColor": "rgba(30, 64, 175, 0.1)",
                    "tension": 0.4
                }
            ]
        },
        "donor_allocation": {
            "labels": [],
            "datasets": [
                {
                    "label": "Donor Contributions",
                    "data": [],
                    "backgroundColor": "#1E40AF"
                }
            ]
        },
        "cash_flow_projection": {
            "labels": [
                "Month 1",
                "Month 2",
                "Month 3",
                "Month 4",
                "Month 5",
                "Month 6"
            ],
            "datasets": [
                {
                    "label": "Projected Cash Flow",
                    "data": [0, 0, 0, 0, 0, 0],
                    "borderColor": "#2563EB",
                    "backgroundColor": "rgba(37, 99, 235, 0.1)",
                    "tension": 0.4
                }
            ]
        }
    },
    "recent_activity": [],
    "user": {
        "id": 1,
        "name": "Programs Manager",
        "email": "pm@example.com",
        "role": "programs-manager"
    }
}
```

**Response (Finance Officer)**:

```json
{
    "kpis": {
        "monthly_budget": 0,
        "actual_expenses": 0,
        "pending_expenses": 0,
        "cash_balance": 0
    },
    "charts": {
        "budget_vs_actual": {
            "labels": ["Personnel", "Travel", "Equipment", "Supplies", "Other"],
            "datasets": [
                {
                    "label": "Budget",
                    "data": [0, 0, 0, 0, 0],
                    "backgroundColor": "#1E40AF"
                },
                {
                    "label": "Actual",
                    "data": [0, 0, 0, 0, 0],
                    "backgroundColor": "#60A5FA"
                }
            ]
        },
        "expense_categories": {
            "labels": [],
            "datasets": [
                {
                    "label": "Expenses by Category",
                    "data": [],
                    "backgroundColor": [
                        "#1E40AF",
                        "#2563EB",
                        "#60A5FA",
                        "#93C5FD",
                        "#DBEAFE"
                    ]
                }
            ]
        }
    },
    "recent_transactions": [],
    "pending_purchase_orders": [],
    "user": {
        "id": 2,
        "name": "Finance Officer",
        "email": "fo@example.com",
        "role": "finance-officer"
    }
}
```

**Response (Project Officer)**:

```json
{
    "kpis": {
        "project_budget": 0,
        "budget_used": 0,
        "remaining_budget": 0,
        "activities_completed": 0
    },
    "assigned_projects": [],
    "my_tasks": [],
    "project_timeline": [],
    "user": {
        "id": 3,
        "name": "Project Officer",
        "email": "po@example.com",
        "role": "project-officer"
    }
}
```

**Status Codes**:

- `200 OK` - Dashboard data retrieved successfully
- `401 Unauthorized` - Missing or invalid authentication token
- `500 Internal Server Error` - Server error occurred

---

### 2. Get Notifications

Retrieves notifications for the authenticated user.

**Endpoint**: `GET /api/v1/dashboard/notifications`

**Authentication**: Required

**Request Headers**:

```http
GET /api/v1/dashboard/notifications HTTP/1.1
Host: localhost
Authorization: Bearer {token}
Accept: application/json
```

**Response**:

```json
{
    "notifications": [],
    "unread_count": 0
}
```

**Status Codes**:

- `200 OK` - Notifications retrieved successfully
- `401 Unauthorized` - Missing or invalid authentication token

---

### 3. Mark Notification as Read

Marks a specific notification as read for the authenticated user.

**Endpoint**: `POST /api/v1/dashboard/notifications/{notification}/read`

**Authentication**: Required

**URL Parameters**:

- `notification` (integer, required) - The ID of the notification to mark as read

**Request Headers**:

```http
POST /api/v1/dashboard/notifications/123/read HTTP/1.1
Host: localhost
Authorization: Bearer {token}
Accept: application/json
```

**Response**:

```json
{
    "message": "Notification marked as read"
}
```

**Status Codes**:

- `200 OK` - Notification marked as read successfully
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Notification not found
- `500 Internal Server Error` - Server error occurred

---

## Data Structures

### KPI Object (Programs Manager)

```typescript
{
  total_budget: number,      // Total organizational budget
  ytd_spending: number,      // Year-to-date spending
  available_funds: number,   // Remaining available funds
  pending_approvals: number  // Count of pending approval items
}
```

### KPI Object (Finance Officer)

```typescript
{
  monthly_budget: number,    // Current month budget allocation
  actual_expenses: number,   // Actual expenses this month
  pending_expenses: number,  // Pending expense approvals
  cash_balance: number       // Current cash balance
}
```

### KPI Object (Project Officer)

```typescript
{
  project_budget: number,         // Total project budget
  budget_used: number,            // Budget already utilized
  remaining_budget: number,       // Budget remaining
  activities_completed: number    // Count of completed activities
}
```

### Chart Data Object (Chart.js Compatible)

```typescript
{
  labels: string[],           // X-axis labels
  datasets: [
    {
      label: string,          // Dataset label
      data: number[],         // Data points
      borderColor?: string,   // Line/border color
      backgroundColor: string | string[], // Fill/bar colors
      tension?: number        // Line smoothness (0-1)
    }
  ]
}
```

### User Object

```typescript
{
  id: number,
  name: string,
  email: string,
  role: string  // "programs-manager" | "finance-officer" | "project-officer"
}
```

---

## Caching

Dashboard data is cached for 5 minutes per role and user:

- **Cache Key Format**: `dashboard_{role}_{userId}`
- **TTL**: 300 seconds (5 minutes)
- **Cache Invalidation**: Automatic on TTL expiration

**Example Cache Keys**:

```
dashboard_programs-manager_1
dashboard_finance-officer_2
dashboard_project-officer_3
```

---

## Error Responses

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 500 Internal Server Error

```json
{
    "message": "An error occurred while loading dashboard data",
    "error": "Error details..."
}
```

---

## Rate Limiting

Dashboard endpoints are subject to standard API rate limits:

- **Limit**: 60 requests per minute per user
- **Header**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

---

## Example Usage (JavaScript)

### Using Axios

```javascript
import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost/api/v1",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

// Set bearer token
api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

// Fetch dashboard data
try {
    const response = await api.get("/dashboard");
    console.log("Dashboard KPIs:", response.data.kpis);
    console.log("Dashboard Charts:", response.data.charts);
} catch (error) {
    console.error("Error loading dashboard:", error);
}

// Fetch notifications
try {
    const response = await api.get("/dashboard/notifications");
    console.log("Notifications:", response.data.notifications);
    console.log("Unread count:", response.data.unread_count);
} catch (error) {
    console.error("Error loading notifications:", error);
}

// Mark notification as read
try {
    await api.post(`/dashboard/notifications/${notificationId}/read`);
    console.log("Notification marked as read");
} catch (error) {
    console.error("Error marking notification:", error);
}
```

### Using Dashboard Store (Pinia)

```javascript
import { useDashboardStore } from "@/stores/dashboardStore";

const dashboardStore = useDashboardStore();

// Fetch all dashboard data
await dashboardStore.fetchDashboardData();

// Access data
const kpis = dashboardStore.kpis;
const charts = dashboardStore.charts;

// Fetch notifications
await dashboardStore.fetchNotifications();
const notifications = dashboardStore.notifications;
const unreadCount = dashboardStore.unreadCount;

// Mark notification as read
await dashboardStore.markNotificationRead(notificationId);

// Start auto-refresh (30 seconds)
dashboardStore.startAutoRefresh();

// Stop auto-refresh
dashboardStore.stopAutoRefresh();
```

---

## Security Considerations

1. **Authentication**: All endpoints require valid Sanctum token
2. **Authorization**: Users only see data for their assigned role
3. **Rate Limiting**: Standard API rate limits apply
4. **CORS**: Configure allowed origins in `config/cors.php`
5. **Token Expiration**: Tokens expire based on Sanctum configuration

---

## Performance Tips

1. **Caching**: Dashboard data is cached for 5 minutes - avoid unnecessary refreshes
2. **Auto-Refresh**: 30-second polling is optimal for real-time updates
3. **Cleanup**: Always stop auto-refresh on component unmount
4. **Parallel Requests**: Fetch dashboard data and notifications in parallel

```javascript
// Efficient parallel loading
await Promise.all([
    dashboardStore.fetchDashboardData(),
    dashboardStore.fetchNotifications(),
]);
```

---

## Testing

Run dashboard tests:

```bash
php artisan test --filter=DashboardTest
```

Expected: 10/10 tests passing

---

## Version History

- **v1.0** (Current) - Initial implementation with role-based dashboards, KPIs, charts, and notifications

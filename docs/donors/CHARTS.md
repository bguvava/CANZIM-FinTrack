# Donor Charts & Visualizations - Documentation

**Module:** Donor & Funding Management  
**Feature:** Charts & Visualizations (Phase 7)  
**Last Updated:** November 17, 2025

---

## Overview

The Donor Charts feature provides visual analytics for donor funding data through interactive Chart.js visualizations. This feature helps Programs Managers and Finance Officers quickly understand funding patterns, donor contributions, and trends over time.

---

## Features

### 1. Funding Distribution Pie Chart

**Purpose:** Visualize the breakdown of restricted vs unrestricted funding

**Data Source:**

```sql
SELECT
  SUM(funding_amount) WHERE is_restricted = true AS restricted,
  SUM(funding_amount) WHERE is_restricted = false AS unrestricted
FROM project_donors
```

**Chart Configuration:**

- **Type:** Pie Chart
- **Colors:** CANZIM Blue palette (#1E40AF, #60A5FA)
- **Labels:** "Restricted Funding", "Unrestricted Funding"
- **Tooltips:** Currency formatting with percentages
- **Legend:** Bottom positioned

**Example Output:**

```
Restricted Funding: $250,000 (62.5%)
Unrestricted Funding: $150,000 (37.5%)
```

---

### 2. Top 10 Donors Bar Chart

**Purpose:** Identify and visualize the highest-contributing donors

**Data Source:**

```sql
SELECT donors.name, SUM(project_donors.funding_amount) as total_funding
FROM donors
JOIN project_donors ON donors.id = project_donors.donor_id
GROUP BY donors.id, donors.name
ORDER BY total_funding DESC
LIMIT 10
```

**Chart Configuration:**

- **Type:** Horizontal Bar Chart
- **Color:** CANZIM Blue (#2563EB)
- **X-Axis:** USD amounts with currency formatting
- **Y-Axis:** Donor names
- **Tooltips:** Currency formatting
- **Sorting:** Descending by funding amount

**Example Output:**

```
USAID                    $500,000
African Union            $350,000
World Bank               $300,000
...
```

---

### 3. Funding Timeline Line Chart

**Purpose:** Track monthly funding trends over the last 12 months

**Data Source:**

```sql
SELECT
  YEAR(created_at) as year,
  MONTH(created_at) as month,
  SUM(funding_amount) as total
FROM project_donors
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY year, month
ORDER BY year, month
```

**Chart Configuration:**

- **Type:** Line Chart with area fill
- **Color:** CANZIM Blue (#2563EB) with 20% transparency
- **X-Axis:** Month labels (e.g., "Jan 2025", "Feb 2025")
- **Y-Axis:** USD amounts with currency formatting
- **Fill:** True (area under line)
- **Tension:** 0.4 (smooth curves)
- **Border Width:** 2px

**Example Output:**

```
Nov 2024: $25,000
Dec 2024: $30,000
Jan 2025: $45,000
...
```

---

## Architecture

### Backend

#### DonorController Method

**Endpoint:** `GET /api/v1/donors/chart-data`

**Authorization:** Programs Manager and Finance Officer only

**Response Format:**

```json
{
  "success": true,
  "data": {
    "funding_distribution": {
      "labels": ["Restricted Funding", "Unrestricted Funding"],
      "datasets": [
        {
          "data": [250000, 150000],
          "backgroundColor": ["#1E40AF", "#60A5FA"]
        }
      ]
    },
    "top_donors": {
      "labels": ["USAID", "African Union", "World Bank", ...],
      "datasets": [
        {
          "label": "Total Funding",
          "data": [500000, 350000, 300000, ...],
          "backgroundColor": "#2563EB"
        }
      ]
    },
    "funding_timeline": {
      "labels": ["Nov 2024", "Dec 2024", "Jan 2025", ...],
      "datasets": [
        {
          "label": "Monthly Funding",
          "data": [25000, 30000, 45000, ...],
          "backgroundColor": "rgba(37, 99, 235, 0.2)",
          "borderColor": "#2563EB",
          "borderWidth": 2,
          "tension": 0.4,
          "fill": true
        }
      ]
    }
  }
}
```

#### DonorService Methods

```php
public function generateChartData(): array
{
    return [
        'funding_distribution' => $this->getFundingDistributionData(),
        'top_donors' => $this->getTopDonorsData(),
        'funding_timeline' => $this->getFundingTimelineData(),
    ];
}

protected function getFundingDistributionData(): array
{
    $restricted = DB::table('project_donors')
        ->where('is_restricted', true)
        ->sum('funding_amount');

    $unrestricted = DB::table('project_donors')
        ->where('is_restricted', false)
        ->sum('funding_amount');

    return [
        'labels' => ['Restricted Funding', 'Unrestricted Funding'],
        'datasets' => [
            [
                'data' => [(float) $restricted, (float) $unrestricted],
                'backgroundColor' => ['#1E40AF', '#60A5FA'],
            ],
        ],
    ];
}

protected function getTopDonorsData(): array
{
    $topDonors = DB::table('donors')
        ->join('project_donors', 'donors.id', '=', 'project_donors.donor_id')
        ->select('donors.name', DB::raw('SUM(project_donors.funding_amount) as total_funding'))
        ->groupBy('donors.id', 'donors.name')
        ->orderByDesc('total_funding')
        ->limit(10)
        ->get();

    return [
        'labels' => $topDonors->pluck('name')->toArray(),
        'datasets' => [
            [
                'label' => 'Total Funding',
                'data' => $topDonors->pluck('total_funding')->map(fn($v) => (float) $v)->toArray(),
                'backgroundColor' => '#2563EB',
            ],
        ],
    ];
}

protected function getFundingTimelineData(): array
{
    // Generate last 12 months
    $months = collect();
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $months->push([
            'month' => $date->format('M Y'),
            'year' => $date->year,
            'month_num' => $date->month,
        ]);
    }

    // Fetch actual funding data
    $fundingByMonth = DB::table('project_donors')
        ->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(funding_amount) as total')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->get()
        ->keyBy(fn($item) => $item->year . '-' . $item->month);

    // Map to months array with zero-filling
    $monthlyData = $months->map(function ($month) use ($fundingByMonth) {
        $key = $month['year'] . '-' . $month['month_num'];
        return (float) ($fundingByMonth->get($key)?->total ?? 0);
    });

    return [
        'labels' => $months->pluck('month')->toArray(),
        'datasets' => [
            [
                'label' => 'Monthly Funding',
                'data' => $monthlyData->toArray(),
                'backgroundColor' => 'rgba(37, 99, 235, 0.2)',
                'borderColor' => '#2563EB',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => true,
            ],
        ],
    ];
}
```

---

### Frontend

#### DonorCharts.vue Component

**Location:** `resources/js/components/donors/DonorCharts.vue`

**Props:** None (fetches data internally)

**Emits:** None

**Exposed Methods:**

- `refresh()` - Manually refresh chart data

**Features:**

- Collapsible charts section with smooth transitions
- Loading state with spinner
- Error state with message
- Empty state when no data
- Responsive 2-column grid (timeline full-width)
- Auto-fetch on mount

**Usage:**

```vue
<template>
    <DonorCharts ref="chartsRef" />
</template>

<script setup>
import { ref } from "vue";
import DonorCharts from "@/components/donors/DonorCharts.vue";

const chartsRef = ref(null);

// Manual refresh
const refreshCharts = () => {
    chartsRef.value?.refresh();
};
</script>
```

#### donorStore Action

```javascript
async fetchChartData() {
  this.loading = true;
  this.error = null;

  try {
    const response = await axios.get('/api/v1/donors/chart-data');

    if (response.data.success) {
      return response.data.data;
    }

    // Return empty structure if no success
    return {
      funding_distribution: { labels: [], datasets: [] },
      top_donors: { labels: [], datasets: [] },
      funding_timeline: { labels: [], datasets: [] },
    };
  } catch (error) {
    this.error = error.response?.data?.message || 'Failed to fetch chart data';
    throw error;
  } finally {
    this.loading = false;
  }
}
```

---

## UI Integration

### DonorsList.vue

Charts are integrated into the main donors list page with:

1. **Collapsible Section:**
    - Toggle button with chevron icon
    - Smooth expand/collapse transitions
    - Hidden by default (can be changed)

2. **Responsive Layout:**
    - Mobile: Single column stack
    - Tablet/Desktop: 2-column grid
    - Timeline chart: Full width across columns

3. **Visual Design:**
    - White background cards
    - Subtle shadows and borders
    - CANZIM blue accent icons
    - Info icons with tooltips

**Example:**

```vue
<div class="mb-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold text-gray-900">
      <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
      Funding Analytics
    </h2>
    <button @click="toggleChartsVisibility" class="text-sm text-blue-600 hover:text-blue-800">
      <i :class="showCharts ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
      {{ showCharts ? 'Hide' : 'Show' }} Charts
    </button>
  </div>

  <transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 transform -translate-y-2"
    enter-to-class="opacity-100 transform translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 transform translate-y-0"
    leave-to-class="opacity-0 transform -translate-y-2"
  >
    <DonorCharts v-if="showCharts" ref="chartsRef" />
  </transition>
</div>
```

---

## Chart.js Components

### Reusable Chart Components

The implementation uses existing Chart.js wrapper components:

1. **PieChart.vue** - `resources/js/components/charts/PieChart.vue`
2. **BarChart.vue** - `resources/js/components/charts/BarChart.vue`
3. **LineChart.vue** - `resources/js/components/charts/LineChart.vue`

These components handle:

- Chart initialization and lifecycle
- Responsive resizing
- Data reactivity (watch for updates)
- Cleanup on unmount
- Default styling and tooltips

---

## Color Palette

**CANZIM Blue Theme:**

- Primary: `#1E40AF` - Dark blue (restricted funding, primary elements)
- Secondary: `#2563EB` - Medium blue (bar charts, borders)
- Accent: `#60A5FA` - Light blue (unrestricted funding, highlights)
- Transparency: `rgba(37, 99, 235, 0.2)` - For area fills

---

## Performance Considerations

### Backend Optimization

1. **Database Queries:**
    - Uses direct DB queries (not Eloquent) for aggregations
    - Indexed columns: `donor_id`, `created_at`, `is_restricted`
    - Efficient GROUP BY and SUM operations

2. **Data Processing:**
    - Minimal PHP processing (database does aggregation)
    - Zero-filling for missing months in timeline
    - Float casting for consistent number types

### Frontend Optimization

1. **Lazy Loading:**
    - Charts only fetch data when visible
    - Collapsible section reduces initial load

2. **Caching:**
    - Could implement 5-minute cache in future
    - No auto-refresh (manual only)

3. **Rendering:**
    - Chart.js handles canvas optimization
    - Responsive resize without re-fetching data

---

## Error Handling

### Backend Errors

```json
{
    "success": false,
    "message": "Failed to fetch chart data: [error details]"
}
```

**HTTP Status:** 500 (Internal Server Error)

### Frontend Errors

**Error State Display:**

```html
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
    <i class="fas fa-exclamation-circle mr-2"></i>
    {{ error }}
</div>
```

**Common Errors:**

- Network timeout
- Authorization failure (403)
- Server error (500)
- No data available (shows empty state, not error)

---

## Testing

### Manual Testing Checklist

- [ ] Charts load correctly on page load
- [ ] Toggle button expands/collapses charts smoothly
- [ ] Pie chart shows correct restricted/unrestricted split
- [ ] Bar chart displays top 10 donors in descending order
- [ ] Line chart shows 12 months of data
- [ ] Tooltips display correctly formatted USD amounts
- [ ] Empty state shows when no funding data exists
- [ ] Error state shows when API fails
- [ ] Loading state shows during data fetch
- [ ] Charts are responsive on mobile/tablet/desktop
- [ ] Only Programs Manager and Finance Officer can access

### Test Data Requirements

For visual testing, ensure:

- At least 10 donors with varying funding amounts
- Mix of restricted and unrestricted funding
- Funding spread across last 12 months
- Some months with zero funding (to test zero-filling)

---

## Future Enhancements

### Planned Features

1. **Date Range Filters:**
    - Custom date range selection
    - Preset ranges (Last Month, Last Quarter, Last Year)

2. **Donor-Specific Charts:**
    - Individual donor funding timeline
    - Project-specific funding breakdown

3. **Export Options:**
    - Export charts as PNG/PDF
    - Export data as CSV

4. **Additional Charts:**
    - Funding by sector/region
    - In-kind vs cash contributions
    - Donor retention rate

5. **Interactive Features:**
    - Click chart to drill down
    - Hover to see detailed tooltips
    - Legend filtering (hide/show datasets)

6. **Performance:**
    - 5-minute cache layer
    - Real-time updates with WebSockets

---

## Changelog

### Phase 7 (November 17, 2025)

- ✅ Created DonorCharts.vue component
- ✅ Implemented funding distribution pie chart
- ✅ Implemented top 10 donors bar chart
- ✅ Implemented funding timeline line chart
- ✅ Added chartData endpoint to DonorController
- ✅ Implemented generateChartData in DonorService
- ✅ Added fetchChartData action to donorStore
- ✅ Integrated charts into DonorsList.vue
- ✅ Added collapsible section with transitions
- ✅ Implemented CANZIM blue color scheme

---

## Related Documentation

- [API Documentation](./API.md) - Complete API endpoint reference
- [Progress Tracking](./PROGRESS.md) - Module completion status
- [PDF Reports](./REPORTS.md) - Financial report generation
- [TODO List](./TODO.md) - Remaining tasks and phases

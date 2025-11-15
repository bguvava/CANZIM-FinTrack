# Cash Flow Projections

## Overview

Cash Flow Projections provide forecasting capabilities based on historical transaction data. The system analyzes the last 6 months of cash flow history to project future cash positions for the next 3-12 months.

## How Projections Work

### Data Analysis

1. **Historical Period**: Analyzes last 6 months of transactions
2. **Average Calculation**: Computes monthly average inflows and outflows
3. **Projection Period**: Forecasts 3-12 months into the future
4. **Balance Projection**: Calculates projected end-of-month balances

### Calculation Method

```
Average Monthly Inflow = Total Inflows (last 6 months) / 6
Average Monthly Outflow = Total Outflows (last 6 months) / 6
Net Monthly Cash Flow = Average Inflow - Average Outflow

For each future month:
  Projected Inflow = Average Monthly Inflow
  Projected Outflow = Average Monthly Outflow
  Projected Balance = Previous Month Balance + Projected Net Cash Flow
```

## API Endpoint

### Get Cash Flow Projections

```http
GET /api/v1/cash-flows/projections
```

**Query Parameters:**

- `bank_account_id` (integer): Optional - Project for specific account
- `months` (integer): Number of months to project (3-12, default: 6)

**Response:**

```json
{
    "projection_period": "6 months",
    "based_on_months": 6,
    "current_balance": "150000.00",
    "average_monthly_inflow": "83333.33",
    "average_monthly_outflow": "58333.33",
    "projections": [
        {
            "month": "December 2025",
            "month_number": 1,
            "projected_inflow": "83333.33",
            "projected_outflow": "58333.33",
            "net_cash_flow": "25000.00",
            "projected_balance": "175000.00"
        },
        {
            "month": "January 2026",
            "month_number": 2,
            "projected_inflow": "83333.33",
            "projected_outflow": "58333.33",
            "net_cash_flow": "25000.00",
            "projected_balance": "200000.00"
        },
        {
            "month": "February 2026",
            "month_number": 3,
            "projected_inflow": "83333.33",
            "projected_outflow": "58333.33",
            "net_cash_flow": "25000.00",
            "projected_balance": "225000.00"
        }
        // ... up to requested number of months
    ],
    "summary": {
        "total_projected_inflows": "500000.00",
        "total_projected_outflows": "350000.00",
        "net_projected_change": "150000.00",
        "final_projected_balance": "300000.00"
    }
}
```

## Use Cases

### Financial Planning

- **Budget Preparation**: Use projections for annual budget planning
- **Cash Reserve Planning**: Identify months with potential cash shortfalls
- **Grant Proposal**: Show projected cash needs for funding proposals
- **Investment Decisions**: Determine timing for investments based on projected surplus

### Scenario Planning

- **What-If Analysis**: Adjust current spending based on projections
- **Contingency Planning**: Prepare for projected low-balance periods
- **Growth Planning**: Identify capacity for new projects
- **Funding Gaps**: Highlight when additional funding may be needed

### Stakeholder Communication

- **Board Reports**: Present financial outlook to board members
- **Donor Updates**: Show sustainability and financial health
- **Management Decisions**: Support strategic decisions with data
- **Audit Preparation**: Demonstrate financial planning processes

## Interpretation Guide

### Understanding the Output

#### Current Metrics

- **Current Balance**: Starting point for projections
- **Average Monthly Inflow**: Historical average of money received
- **Average Monthly Outflow**: Historical average of money spent

#### Monthly Projections

- **Projected Inflow**: Expected receipts for the month
- **Projected Outflow**: Expected payments for the month
- **Net Cash Flow**: Difference between inflow and outflow
- **Projected Balance**: Expected end-of-month balance

#### Summary Metrics

- **Total Projected Inflows**: Sum of all projected inflows
- **Total Projected Outflows**: Sum of all projected outflows
- **Net Projected Change**: Overall change in cash position
- **Final Projected Balance**: Balance at end of projection period

### Warning Signs

🔴 **Critical Alerts** (Immediate Action Required):

- Projected balance falls below 10% of average monthly outflow
- Any month showing negative balance
- Continuous declining trend for 3+ months

🟡 **Caution Indicators** (Monitor Closely):

- Projected balance below 1 month of operating expenses
- Net cash flow decreasing trend
- Outflows growing faster than inflows

🟢 **Healthy Indicators**:

- Projected balance increasing
- Positive net cash flow each month
- Balance above 3 months of operating expenses

## Limitations & Considerations

### Accuracy Factors

1. **Historical Data Quality**: Projections are only as good as historical data
2. **Seasonality**: May not account for seasonal variations
3. **One-Time Events**: Large one-time transactions skew averages
4. **External Factors**: Cannot predict unexpected events
5. **Grant Cycles**: May not align with irregular grant disbursements

### Best Practices

#### Data Preparation

- Ensure all transactions are recorded
- Reconcile accounts before running projections
- Remove or note unusual one-time transactions
- Update projections monthly

#### Analysis

- Run projections for different time periods (3, 6, 12 months)
- Compare projections to actual results monthly
- Adjust planning based on variance analysis
- Consider multiple scenarios (optimistic, realistic, pessimistic)

#### Decision Making

- Use projections as a guide, not absolute predictions
- Combine with qualitative factors and known upcoming events
- Update projections when circumstances change
- Document assumptions and limitations

## User Interface

### Projection Dashboard

- **Time Range Selector**: Choose 3, 6, or 12 months
- **Account Filter**: View all accounts or specific account
- **Chart Visualization**: Line chart showing projected balances
- **Table View**: Detailed monthly breakdown
- **Export Options**: PDF and Excel export

### Visualization Features

- **Balance Trend Line**: Shows projected balance trajectory
- **Inflow/Outflow Bars**: Monthly inflow vs outflow comparison
- **Net Cash Flow Indicator**: Color-coded positive/negative
- **Warning Zones**: Highlighted months with low balances

## Reporting

### Standard Reports

#### Executive Summary Report

- High-level projection overview
- Key metrics and trends
- Warning indicators
- Recommendations

#### Detailed Projection Report

- Month-by-month breakdown
- Historical comparison
- Variance analysis
- Assumptions documentation

#### Cash Position Report

- Current vs projected balances
- Runway analysis (months of operating cash)
- Liquidity ratios
- Risk assessment

## Advanced Features

### Scenario Modeling

Future enhancement to allow:

- Adjusting projected inflow/outflow percentages
- Adding known future transactions
- Modeling grant award impacts
- Testing "what-if" scenarios

### Integration with Budgets

Future enhancement to:

- Compare projections with budgets
- Show budget vs actual vs projected
- Identify budget variance trends
- Alert on budget overruns

## Examples

### Example 1: Healthy Organization

```
Current Balance: $200,000
Avg Monthly Inflow: $50,000
Avg Monthly Outflow: $35,000
Net Monthly: +$15,000

Projection shows steady growth:
Month 1: $215,000
Month 6: $290,000
Month 12: $380,000

✅ Status: Healthy - positive trend
```

### Example 2: Warning Signs

```
Current Balance: $100,000
Avg Monthly Inflow: $40,000
Avg Monthly Outflow: $45,000
Net Monthly: -$5,000

Projection shows decline:
Month 1: $95,000
Month 6: $70,000
Month 12: $40,000

⚠️ Status: Declining - action needed
```

### Example 3: Critical Situation

```
Current Balance: $50,000
Avg Monthly Inflow: $30,000
Avg Monthly Outflow: $50,000
Net Monthly: -$20,000

Projection shows crisis:
Month 1: $30,000
Month 2: $10,000
Month 3: -$10,000 (NEGATIVE!)

🔴 Status: Critical - immediate intervention required
```

## Action Items Based on Projections

### Positive Projections

- Consider expanding programs
- Plan for capital investments
- Build emergency reserves
- Increase project capacity

### Declining Projections

- Review and reduce expenses
- Accelerate fundraising efforts
- Delay non-essential spending
- Seek bridge financing

### Critical Projections

- Implement immediate cost cuts
- Emergency fundraising campaign
- Renegotiate payment terms
- Seek emergency grants

## Troubleshooting

**Problem**: Projections show negative balance
**Solution**: This is a warning sign. Review expenses, accelerate fundraising, or delay non-essential spending.

**Problem**: Projections seem inaccurate
**Solution**: Check historical data for completeness. Remove one-time large transactions that skew averages.

**Problem**: Cannot access projections
**Solution**: Ensure you have Finance Officer role. Projections require financial data access permissions.

**Problem**: Projections don't match budget
**Solution**: Projections are based on historical actuals, not budgets. Variance is normal and should be analyzed.

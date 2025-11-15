# Expense Management Module - User Guide

## Overview

The Expense Management module enables project staff to record, track, and manage project-related expenses through a comprehensive three-tier approval workflow. This module ensures proper financial controls while maintaining transparency and accountability.

## Key Features

- **Multi-tier Approval Workflow**: Draft → Submitted → Reviewed → Approved → Paid
- **Role-based Access Control**: Different permissions for Project Officers, Finance Officers, and Programs Managers
- **Receipt Management**: Upload and attach expense receipts (PDF, JPG, PNG)
- **Budget Integration**: Expenses linked to specific budget items
- **Real-time Tracking**: Monitor expense status and approval progress
- **Notification System**: Automatic notifications at each workflow stage
- **Comprehensive Auditing**: Complete approval history and timeline

## User Roles & Permissions

### Project Officer

- Create and submit expense claims
- Upload supporting receipts
- View own expenses
- Edit/delete draft expenses only
- Cannot approve expenses

### Finance Officer

- Review submitted expenses
- Approve or reject at review stage
- Mark approved expenses as paid
- View all expenses
- Cannot create expense claims

### Programs Manager

- Final approval authority
- Approve or reject reviewed expenses
- View all expenses
- Cannot create expense claims

## Workflow Stages

### 1. Draft

- **Who**: Project Officer
- **Actions**: Create, edit, delete, submit
- **Next**: Submit for review

### 2. Submitted

- **Who**: Project Officer (submitted)
- **Actions**: View only, cannot modify
- **Next**: Finance Officer reviews

### 3. Under Review / Reviewed

- **Who**: Finance Officer
- **Actions**: Approve (moves to Reviewed) or Reject (returns to Project Officer)
- **Next**: Programs Manager approval

### 4. Approved

- **Who**: Programs Manager (approved)
- **Actions**: Finance Officer marks as paid
- **Next**: Payment processing

### 5. Paid

- **Who**: Finance Officer (marked paid)
- **Actions**: View only, cannot modify
- **Status**: Final

### Rejected

- **Who**: Finance Officer or Programs Manager
- **Actions**: Project Officer can view rejection reason
- **Status**: Terminal (can create new expense)

## Creating an Expense

### Step-by-Step Process

1. **Navigate to Expenses**
    - Click "Expenses" in the sidebar
    - Click "New Expense" button (top right)

2. **Fill Basic Information**
    - Select Project (required)
    - Select Budget Item (required - filtered by project)
    - Select Expense Category (required)
    - Enter Expense Date (required)
    - Enter Amount (required)
    - Provide Description (required - detailed explanation)

3. **Upload Receipt (Optional)**
    - Click "Upload a file" or drag and drop
    - Supported formats: PDF, JPG, JPEG, PNG
    - Maximum size: 5MB
    - Recommended: Always attach receipts for audit trail

4. **Save Options**
    - **Save as Draft**: Save without submitting (can edit later)
    - **Save & Submit for Review**: Save and immediately submit to Finance Officer

5. **After Submission**
    - Cannot edit or delete once submitted
    - Finance Officer receives notification
    - Track progress in expense details page

## Reviewing Expenses (Finance Officer)

### Review Process

1. **Access Pending Reviews**
    - Click "Expenses" → "Pending Review" in sidebar
    - Badge shows count of pending items

2. **Review Expense Details**
    - Click "View Details" on any expense
    - Verify all information:
        - Project and budget item validity
        - Amount accuracy
        - Receipt attached (if applicable)
        - Description clarity

3. **Make Decision**
    - **Approve**: Click "Approve Review"
        - Add optional comments
        - Expense moves to Programs Manager
    - **Reject**: Click "Reject"
        - Provide rejection reason (required)
        - Project Officer receives notification

### Review Checklist

- [ ] Expense relates to approved project
- [ ] Budget item has sufficient funds
- [ ] Amount is reasonable and justified
- [ ] Receipt attached (if required by policy)
- [ ] Description is clear and detailed
- [ ] Complies with organizational policies

## Final Approval (Programs Manager)

### Approval Process

1. **Access Pending Approvals**
    - Click "Expenses" → "Pending Approval" in sidebar
    - Badge shows count of pending items

2. **Review Details & Finance Comments**
    - View complete expense information
    - Check Finance Officer's review comments
    - Verify strategic alignment with project goals

3. **Make Final Decision**
    - **Approve**: Click "Approve"
        - Add optional comments
        - Expense ready for payment
    - **Reject**: Click "Reject"
        - Provide rejection reason (required)
        - Project Officer receives notification

### Approval Considerations

- Strategic alignment with project objectives
- Budget availability and utilization
- Compliance with donor requirements
- Timing and project phase appropriateness
- Overall financial health of project

## Payment Processing (Finance Officer)

### Marking as Paid

1. **Access Approved Expenses**
    - Filter expenses by status: "Approved"
    - Or view from main expenses list

2. **Process Payment**
    - Click "View Details" on approved expense
    - Click "Mark as Paid"

3. **Enter Payment Details**
    - Payment Reference (e.g., CHQ-12345, TXN-67890)
    - Payment Method (e.g., Bank Transfer, Cheque)
    - Payment Notes (optional additional information)

4. **Confirm**
    - Click "Mark as Paid"
    - Status changes to "Paid"
    - Project Officer receives notification

## Searching & Filtering

### Available Filters

- **Search**: Expense number or description keyword
- **Status**: Draft, Submitted, Under Review, Reviewed, Approved, Rejected, Paid
- **Project**: Filter by specific project
- **Category**: Filter by expense category
- **Date Range**: Filter by expense date (if needed)

### Quick Actions

- **Clear Filters**: Remove all active filters
- **Export**: Generate reports (coming in next phase)
- **Bulk Actions**: Process multiple expenses (admin only)

## Status Badges

| Status       | Color   | Meaning                   |
| ------------ | ------- | ------------------------- |
| Draft        | Gray    | Not yet submitted         |
| Submitted    | Blue    | Awaiting finance review   |
| Under Review | Yellow  | Finance Officer reviewing |
| Reviewed     | Purple  | Awaiting final approval   |
| Approved     | Green   | Approved, pending payment |
| Rejected     | Red     | Rejected at any stage     |
| Paid         | Emerald | Payment processed         |

## Notifications

Users receive email notifications when:

- **Project Officer**:
    - Expense reviewed (approved/rejected)
    - Final approval granted/denied
    - Payment processed

- **Finance Officer**:
    - New expense submitted for review
    - Expense requires payment processing

- **Programs Manager**:
    - Reviewed expense awaiting final approval

## Best Practices

### For Project Officers

1. **Be Detailed**: Provide comprehensive descriptions
2. **Attach Receipts**: Always upload supporting documentation
3. **Check Budget**: Verify budget availability before creating expense
4. **Timely Submission**: Submit expenses promptly after incurring
5. **Follow Up**: Monitor approval progress regularly

### For Finance Officers

1. **Prompt Review**: Process reviews within 24-48 hours
2. **Clear Communication**: Provide specific feedback when rejecting
3. **Documentation Check**: Ensure all required documents attached
4. **Budget Verification**: Confirm budget item has sufficient funds
5. **Policy Compliance**: Verify adherence to financial policies

### For Programs Managers

1. **Strategic Review**: Consider broader project implications
2. **Timely Decisions**: Process approvals within 48-72 hours
3. **Clear Rationale**: Provide detailed comments when rejecting
4. **Trend Analysis**: Monitor expense patterns across projects
5. **Budget Oversight**: Ensure expenses align with strategic goals

## Troubleshooting

### Common Issues

**Cannot Submit Expense**

- Ensure all required fields are filled
- Check that budget item has available funds
- Verify receipt file size (max 5MB)
- Confirm you have Project Officer role

**Cannot See Pending Reviews**

- Verify you have Finance Officer role
- Check if any expenses are actually submitted
- Refresh the page
- Clear browser cache

**Cannot Approve Expense**

- Verify you have Programs Manager role
- Ensure expense status is "Reviewed"
- Check that you're not trying to approve your own expense
- Refresh and try again

**Receipt Upload Failed**

- Check file format (PDF, JPG, JPEG, PNG only)
- Verify file size is under 5MB
- Try a different browser
- Contact support if issue persists

## Support

For technical assistance or questions:

- **Email**: support@can-zimbabwe.org
- **Phone**: +263-XXX-XXXX
- **Help Desk**: Available Monday-Friday, 8AM-5PM CAT

## Next Steps

After mastering expense management:

1. Explore Report Generation features
2. Learn about Cash Flow Management
3. Review Financial Analytics Dashboard
4. Understand Purchase Order Workflow

---

**Module Version**: 1.0.0  
**Last Updated**: November 15, 2025  
**Document Owner**: Finance Department, CAN-Zimbabwe

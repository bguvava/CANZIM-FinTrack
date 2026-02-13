<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $expense->expense_number }} - Expense Details</title>
    @include('pdf.partials.styles')
</head>
<body>
    {{-- Header --}}
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64 ?? '',
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Expense Details Report'
    ])

    {{-- Content --}}
    <div class="content">
        {{-- Expense Summary --}}
        <h2 class="text-primary mb-10">Expense Information</h2>
        <table class="summary-table mb-20">
            <tr>
                <td>Expense Number:</td>
                <td><strong>{{ $expense->expense_number }}</strong></td>
            </tr>
            <tr>
                <td>Status:</td>
                <td><span class="badge badge-{{ strtolower($expense->status) }}">{{ $expense->status }}</span></td>
            </tr>
            <tr>
                <td>Project:</td>
                <td>{{ $expense->project->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Budget Item:</td>
                <td>{{ $expense->budgetItem->description ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Category:</td>
                <td>{{ $expense->category->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Expense Date:</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td>Amount:</td>
                <td class="currency">${{ number_format($expense->amount, 2) }}</td>
            </tr>
            <tr>
                <td>Description:</td>
                <td>{{ $expense->description }}</td>
            </tr>
            @if($expense->notes)
            <tr>
                <td>Notes:</td>
                <td>{{ $expense->notes }}</td>
            </tr>
            @endif
        </table>

        {{-- Submitter Information --}}
        <div class="section-divider"></div>
        <h3 class="mb-10">Submitted By</h3>
        <table class="summary-table mb-20">
            <tr>
                <td>Name:</td>
                <td>{{ $expense->submitter->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $expense->submitter->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Submitted At:</td>
                <td>{{ $expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('M d, Y h:i A') : 'N/A' }}</td>
            </tr>
        </table>

        {{-- Approval Workflow --}}
        @if($expense->status !== 'Draft' && $expense->approvals->count() > 0)
        <div class="section-divider"></div>
        <h3 class="mb-10">Approval History</h3>
        <table class="data-table mb-20">
            <thead>
                <tr>
                    <th>Approver</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Comments</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expense->approvals as $approval)
                <tr>
                    <td>{{ $approval->user->full_name ?? 'N/A' }}</td>
                    <td>{{ $approval->user->role->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $approval->action === 'approved' ? 'approved' : 'rejected' }}">
                            {{ ucfirst($approval->action) }}
                        </span>
                    </td>
                    <td>{{ $approval->comments ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($approval->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Payment Information --}}
        @if($expense->status === 'Paid')
        <div class="section-divider"></div>
        <h3 class="mb-10">Payment Information</h3>
        <table class="summary-table mb-20">
            <tr>
                <td>Payment Reference:</td>
                <td><strong>{{ $expense->payment_reference ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td>{{ $expense->payment_method ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Bank Account:</td>
                <td>{{ $expense->cashFlow->bankAccount->account_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Paid By:</td>
                <td>{{ $expense->payer->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Paid At:</td>
                <td>{{ $expense->paid_at ? \Carbon\Carbon::parse($expense->paid_at)->format('M d, Y h:i A') : 'N/A' }}</td>
            </tr>
            @if($expense->payment_notes)
            <tr>
                <td>Payment Notes:</td>
                <td>{{ $expense->payment_notes }}</td>
            </tr>
            @endif
        </table>
        @endif

        {{-- Rejection Information --}}
        @if($expense->status === 'Rejected')
        <div class="error-box">
            <h4>Rejection Details</h4>
            <p><strong>Rejected By:</strong> {{ $expense->rejector->full_name ?? 'N/A' }}</p>
            <p><strong>Rejected At:</strong> {{ $expense->rejected_at ? \Carbon\Carbon::parse($expense->rejected_at)->format('M d, Y h:i A') : 'N/A' }}</p>
            <p><strong>Reason:</strong> {{ $expense->rejection_reason ?? 'No reason provided' }}</p>
        </div>
        @endif

        {{-- Receipt Information --}}
        @if($expense->receipt_path)
        <div class="section-divider"></div>
        <div class="info-box">
            <p><strong>Receipt Attached:</strong> {{ basename($expense->receipt_path) }}</p>
            <p style="font-size: 9px; color: #6B7280;">Note: Receipt file is stored separately and not included in this PDF.</p>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    @include('pdf.partials.footer', [
        'generatedBy' => auth()->user()->full_name ?? 'System',
        'userRole' => auth()->user()->role->name ?? 'Administrator',
        'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        'year' => now()->year,
        'organizationName' => 'Climate Action Network Zimbabwe'
    ])
</body>
</html>

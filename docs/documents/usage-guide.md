# Document Management - Usage Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [User Workflows](#user-workflows)
3. [Frontend Component Usage](#frontend-component-usage)
4. [Common Operations](#common-operations)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)
7. [FAQs](#faqs)

---

## Getting Started

### Prerequisites

- Active CANZIM FinTrack account
- Appropriate user role (Project Officer, Finance Officer, or Programs Manager)
- Documents to upload (PDF, Word, Excel, or Image files under 5MB)
- Associated projects, budgets, expenses, or donors in the system

### Accessing Document Management

1. Log in to CANZIM FinTrack
2. Navigate to **Documents** in the sidebar menu
3. You'll see the Document Manager interface

---

## User Workflows

### Workflow 1: Upload a Document for a Project

**Scenario:** Project Officer needs to upload a project implementation plan.

**Steps:**

1. Click **Upload Document** button
2. In the upload modal:
    - **Select File:** Click "Choose File" and browse for your PDF
    - **Title:** Enter "Q1 Implementation Plan"
    - **Description:** (Optional) Enter "Detailed roadmap for Q1 2025 activities"
    - **Category:** Select "Project Reports" from dropdown
    - **Attach To:** Select "Project" type and choose your project from the dropdown
3. Click **Upload** button
4. Wait for success notification: "Document uploaded successfully"
5. Document appears in your documents list

**Expected Result:**

- Document visible in your documents list
- Activity logged: "Uploaded document: Q1 Implementation Plan"
- Version number: 1
- Status: Current

---

### Workflow 2: Upload Expense Receipts

**Scenario:** Finance Officer needs to upload multiple expense receipts.

**Steps:**

1. Navigate to the Expense detail page
2. Scroll to **Documents** section
3. Click **Upload Receipt**
4. For each receipt:
    - **Select File:** Choose JPG/PNG image of receipt
    - **Title:** "Receipt - Office Supplies Purchase"
    - **Category:** Select "Expense Receipts"
    - **Attach To:** Auto-filled with current expense
5. Click **Upload**
6. Repeat for additional receipts

**Pro Tip:** You can also bulk upload by clicking "Upload Multiple" and selecting several files at once.

---

### Workflow 3: Search and Filter Documents

**Scenario:** Programs Manager needs to find all budget documents from January 2025.

**Steps:**

1. Go to **Documents** page
2. Use the filter panel:
    - **Search:** Leave blank (want all documents)
    - **Category:** Select "Budget Documents"
    - **File Type:** Select "PDF"
    - **Date Range:**
        - From: 2025-01-01
        - To: 2025-01-31
3. Click **Apply Filters**
4. View filtered results (e.g., 15 documents found)
5. Export results if needed

**Search Tips:**

- Search queries look in: title, description, filename
- Use specific keywords for better results
- Combine multiple filters for precision
- Clear filters with "Reset" button

---

### Workflow 4: Download and View Documents

**Scenario:** User needs to review a project report document.

**View in Browser (Inline):**

1. Locate document in list
2. Click **View** button (eye icon)
3. Document opens in new browser tab
4. Review content directly in browser

**Download to Computer:**

1. Locate document in list
2. Click **Download** button (download icon)
3. File downloads with original filename
4. Open with your preferred application

**Activity Tracking:**

- Both view and download actions are logged
- Can see who accessed the document and when
- Useful for audit trails and compliance

---

### Workflow 5: Update Document Information

**Scenario:** User uploaded a document with an incorrect title.

**Steps:**

1. Locate the document in your list
2. Click **Edit** button (pencil icon)
3. In the edit modal:
    - **Title:** Change to correct title
    - **Description:** Update or add description
    - **Category:** Change category if needed
4. Click **Update** button
5. See success notification: "Document updated successfully"

**Note:** You can only edit documents you own or if you're a Programs Manager.

---

### Workflow 6: Replace Document with New Version

**Scenario:** Project plan has been revised, need to upload updated version.

**Steps:**

1. Locate the original document
2. Click **Replace** button (refresh icon)
3. In the replace modal:
    - **Select New File:** Choose updated PDF
    - Review warning: "This will archive the current version"
4. Click **Replace Document**
5. System creates Version 2:
    - Old file moved to archive
    - New file becomes current
    - Version number increments
6. See success notification with version info

**What Happens:**

- **Old Version:** Moved to `/documents/archive/`
- **New Version:** Stored in `/documents/`
- **Version Record:** Created for history tracking
- **Activity Log:** "Replaced document" entry created

---

### Workflow 7: View Document Version History

**Scenario:** User wants to see all previous versions of a budget document.

**Steps:**

1. Locate the document
2. Click **Versions** button (clock icon)
3. Version history modal opens showing:
    - **Version 3** (Current) - uploaded by John Doe on Jan 16
    - **Version 2** (Archived) - replaced on Jan 15
    - **Version 1** (Archived) - replaced on Jan 10
4. Click **Download** next to any version to retrieve old file
5. See who replaced each version and when

**Use Cases:**

- Audit trail for document changes
- Retrieve older versions if needed
- Track who made changes and when
- Compliance and record-keeping

---

### Workflow 8: Delete a Document

**Scenario:** Document was uploaded by mistake and needs to be removed.

**Steps:**

1. Locate the document
2. Click **Delete** button (trash icon)
3. SweetAlert confirmation appears:
    - **Title:** "Are you sure?"
    - **Text:** "This will permanently delete the document and all its versions"
    - **Warning:** "This action cannot be undone!"
4. Click **Yes, Delete It!**
5. System performs soft delete
6. Success notification: "Document deleted successfully"
7. Document removed from your list

**Important Notes:**

- Only owners or Programs Managers can delete
- Soft delete (can be restored by admin if needed)
- All versions are deleted together
- Activity logged for accountability

---

## Frontend Component Usage

### DocumentsManager.vue Component

The main Vue component for document management.

**Features:**

- Document upload with drag-and-drop
- List view with pagination
- Search and filtering
- Version history viewer
- Download and replace functionality
- Role-based action buttons

**Props:**

```javascript
{
  entityType: String,      // Optional: 'project', 'budget', 'expense', 'donor'
  entityId: Number,        // Optional: Entity ID to filter documents
  showUploadButton: Boolean, // Default: true
  allowFilters: Boolean,   // Default: true
}
```

**Usage in Parent Component:**

```vue
<template>
    <documents-manager
        entity-type="project"
        :entity-id="projectId"
        :show-upload-button="true"
        :allow-filters="true"
    />
</template>

<script>
import DocumentsManager from "@/components/Documents/DocumentsManager.vue";

export default {
    components: { DocumentsManager },
    data() {
        return {
            projectId: 123,
        };
    },
};
</script>
```

---

## Common Operations

### 1. Bulk Document Upload

**For Multiple Related Documents:**

```javascript
// Example: Upload 5 expense receipts at once
const files = [
    { file: receipt1.pdf, title: "Receipt 1", category: "expense-receipts" },
    { file: receipt2.pdf, title: "Receipt 2", category: "expense-receipts" },
    // ... more files
];

files.forEach(async (item) => {
    await uploadDocument(item);
});
```

**Frontend Implementation:**

- Use `Promise.all()` for parallel uploads
- Show progress bar for each file
- Handle individual success/failure
- Display summary notification at end

---

### 2. Document Organization Strategy

**By Category:**

- **Budget Documents:** Budget proposals, allocations, amendments
- **Expense Receipts:** All receipts, invoices, proof of payment
- **Project Reports:** Deliverables, progress reports, final reports
- **Donor Agreements:** Contracts, MOUs, amendments, correspondence
- **Other:** Training materials, policies, templates

**Naming Conventions:**

- Use descriptive titles: "Q1 2025 Education Budget Proposal"
- Include dates: "2025-01-15 Expense Receipt - Office Supplies"
- Avoid generic names: "Document1.pdf" ❌
- Be specific: "2025 Q1 Project Implementation Plan v2" ✅

---

### 3. Access Control Best Practices

**For Project Officers:**

- Only upload documents for projects you created
- Request Programs Manager to change categories if needed
- Keep documents organized by category

**For Finance Officers:**

- Focus on budget and expense documents
- Ensure all expense receipts are uploaded promptly
- Verify document titles match expense descriptions

**For Programs Managers:**

- Can access ALL documents across the system
- Responsible for document audits and cleanup
- Can update/delete any document if needed
- Should review document categories periodically

---

### 4. File Size Optimization

**If Document Exceeds 5MB:**

**For PDFs:**

1. Use online compression: Smallpdf.com, iLovePDF.com
2. Reduce image quality in PDF settings
3. Remove unnecessary pages or images
4. Use PDF optimization tools

**For Images:**

1. Resize to smaller dimensions (e.g., 1920x1080 max)
2. Use JPEG instead of PNG for photos
3. Compress with TinyPNG.com or similar
4. Reduce quality to 70-80% (still looks good)

**For Word/Excel:**

1. Compress embedded images
2. Remove unused styles and formatting
3. Save as .docx or .xlsx (not .doc or .xls)
4. Consider converting to PDF

---

## Best Practices

### 1. Document Upload Guidelines

✅ **DO:**

- Use descriptive, specific titles
- Add relevant descriptions
- Select correct category
- Attach to correct project/budget/expense
- Verify file before uploading
- Keep files under 5MB

❌ **DON'T:**

- Upload sensitive personal data (unless necessary)
- Use generic filenames like "Document.pdf"
- Upload wrong file type
- Forget to select category
- Upload duplicate documents

---

### 2. Version Management

**When to Create New Version:**

- Document content has changed significantly
- Budget amounts have been revised
- Project plan has been updated
- Errors in original document need correction

**When NOT to Create New Version:**

- Just fixing a typo in title (use Update instead)
- Changing category (use Update instead)
- Adding description (use Update instead)

**Version Naming Strategy:**

- Maintain consistent filename across versions
- Use descriptions to explain changes
- Example: "Updated budget allocation based on donor feedback"

---

### 3. Search Optimization

**For Better Search Results:**

- Use specific keywords in titles and descriptions
- Include dates in document titles
- Tag with proper categories
- Use consistent terminology
- Add descriptions even if optional

**Example Good Title:**

```
2025 Q1 Education Program Budget Proposal - Revised
```

**Example Poor Title:**

```
Budget.pdf
```

---

### 4. Compliance and Audit

**Document Retention:**

- All documents are retained with soft delete
- Version history is permanent
- Activity logs track all operations
- Admins can restore deleted documents

**Audit Trail Includes:**

- Who uploaded the document
- When it was uploaded
- All downloads (who and when)
- All updates (who and when)
- All replacements (version history)
- Deletions (who and when)

**Compliance Checklist:**

- ✅ Upload all required project documents
- ✅ Ensure expense receipts are attached
- ✅ Verify donor agreements are current
- ✅ Maintain budget documentation
- ✅ Review and update descriptions

---

## Troubleshooting

### Problem 1: "File upload failed"

**Possible Causes:**

1. File size exceeds 5MB
2. Unsupported file type
3. Network connection issue
4. Server storage full

**Solutions:**

1. Compress the file to under 5MB
2. Convert to supported format (PDF, DOCX, XLSX, JPG, PNG)
3. Check internet connection and retry
4. Contact system administrator if problem persists

---

### Problem 2: "Unauthorized to access this document"

**Possible Causes:**

1. Document belongs to another user
2. You don't have access to the parent entity (project/budget)
3. Your role doesn't permit access

**Solutions:**

1. Verify you have permission to the related project/budget/expense
2. Request Programs Manager to grant access
3. Check if document owner can share it with you
4. Verify your user role is correct

---

### Problem 3: "Document not found"

**Possible Causes:**

1. Document has been deleted
2. Incorrect document ID in URL
3. Database synchronization issue

**Solutions:**

1. Check if document was recently deleted (check activity log)
2. Return to documents list and find the document
3. Contact administrator if document should exist

---

### Problem 4: "Version replacement failed"

**Possible Causes:**

1. New file is invalid or corrupted
2. Storage permission issue
3. File type doesn't match original

**Solutions:**

1. Verify the new file opens correctly on your computer
2. Ensure file type matches original (e.g., PDF to PDF)
3. Try a different file
4. Contact administrator for server issues

---

### Problem 5: Download doesn't work

**Possible Causes:**

1. File has been moved or deleted from storage
2. Browser download restrictions
3. Network issues

**Solutions:**

1. Try "View" instead to open inline
2. Check browser download settings
3. Disable popup blockers temporarily
4. Try a different browser
5. Contact administrator if file is missing from server

---

## FAQs

### General Questions

**Q: What file types can I upload?**  
A: PDF, Word (.doc, .docx), Excel (.xls, .xlsx), and Images (.jpg, .jpeg, .png)

**Q: What's the maximum file size?**  
A: 5MB per document. Use compression tools if your file is larger.

**Q: Can I upload multiple files at once?**  
A: Yes, use the bulk upload feature or drag-and-drop multiple files.

**Q: How long are documents stored?**  
A: Indefinitely. Even deleted documents can be restored by administrators.

---

### Access and Permissions

**Q: Who can see my uploaded documents?**  
A:

- **You** (the uploader)
- **Project creators** (for project documents)
- **Budget creators** (for budget documents)
- **Expense submitters** (for expense documents)
- **All users** (for donor documents)
- **Programs Managers** (for all documents)

**Q: Can I share a document with specific users?**  
A: Not directly. Documents inherit access from their parent entity (project/budget/expense). To share, add the user to the parent entity.

**Q: Can I restrict who downloads my documents?**  
A: No, anyone with view access can also download. Access control is at the entity level.

---

### Version Management

**Q: How many versions can a document have?**  
A: Unlimited. Each replacement creates a new version.

**Q: Can I delete a specific version?**  
A: No, all versions are retained for audit purposes. You can only delete the entire document (all versions).

**Q: Can I restore an old version as current?**  
A: Yes, download the old version and use "Replace" to upload it as a new version.

**Q: What happens to old versions when I replace?**  
A: Old versions are moved to the archive folder and a version record is created. They remain accessible via version history.

---

### Categories and Organization

**Q: Can I create custom categories?**  
A: No, categories are system-defined. Use "Other" for miscellaneous documents.

**Q: Can I change a document's category after upload?**  
A: Yes, use the "Edit" function to update the category.

**Q: Can a document belong to multiple categories?**  
A: No, each document has one category. Create separate documents if needed.

---

### Search and Filtering

**Q: What fields does search look at?**  
A: Title, description, and original filename.

**Q: Can I search by file content?**  
A: No, only metadata (title, description, filename) is searchable.

**Q: Can I save my filter settings?**  
A: Not currently. You'll need to reapply filters each session.

**Q: How do I search for documents from a specific date?**  
A: Use the date range filters (From Date / To Date).

---

### Technical Questions

**Q: Where are documents physically stored?**  
A: Server storage at `/storage/app/public/documents/YYYY/MM/`

**Q: Are documents encrypted?**  
A: Files are stored with UUID-based filenames for security. Consider enabling server-side encryption for sensitive data.

**Q: What happens if I upload a virus-infected file?**  
A: The system doesn't currently scan for viruses. Only upload files from trusted sources.

**Q: Can I access documents via API?**  
A: Yes, see the [API Documentation](./api-endpoints.md) for details.

---

### Activity Logging

**Q: Are downloads tracked?**  
A: Yes, all downloads are logged with user ID and timestamp.

**Q: Can I see who viewed my document?**  
A: Yes, check the Activity Log module and filter by document ID.

**Q: How long are activity logs kept?**  
A: Indefinitely, for compliance and audit purposes.

---

## Related Documentation

- [Overview](./overview.md) - Module architecture and features
- [API Endpoints](./api-endpoints.md) - Complete API reference
- Activity Log Module - For viewing document access logs
- User Management - For role and permission details

---

## Support

**For Technical Issues:**

- Contact: support@canzim.org
- Developer: bguvava (https://bguvava.com)

**For Access Issues:**

- Contact your Programs Manager
- Email: admin@canzim.org

---

**Last Updated:** November 20, 2025  
**Module Version:** 1.0

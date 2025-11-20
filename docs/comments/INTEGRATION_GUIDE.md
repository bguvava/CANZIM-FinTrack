# Comments Module - Integration Guide

## Purpose

This guide provides step-by-step instructions for integrating the Comments & Collaboration System into Projects, Budgets, and Expenses modules.

---

## Prerequisites

✅ Backend components complete (API, Models, Services, Policies)  
✅ Frontend components complete (Vue components)  
✅ Database migrations run  
✅ Laravel Sanctum configured  
✅ TailwindCSS 4.x configured  
✅ SweetAlert2 installed

---

## Integration Steps

### Step 1: Add CommentsSection to Project Detail View

#### 1.1 Create/Update Project Show/Detail Component

Create `resources/js/pages/Projects/ProjectShow.vue` (or update existing):

```vue
<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Project Details Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <!-- Project info here -->
            </div>

            <!-- Comments Section -->
            <CommentsSection
                commentableType="Project"
                :commentableId="projectId"
            />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import CommentsSection from "@/components/comments/CommentsSection.vue";

const route = useRoute();
const projectId = ref(route.params.id);

// Fetch project details...
</script>
```

#### 1.2 Add Route

Update `resources/js/router/index.js`:

```javascript
{
    path: '/projects/:id',
    name: 'project.show',
    component: () => import('@/pages/Projects/ProjectShow.vue'),
    meta: { requiresAuth: true, roles: ['Programs Manager', 'Finance Officer', 'Project Officer'] }
}
```

---

### Step 2: Add CommentsSection to Budget Detail View

#### 2.1 Update Budget Show Component

Update `resources/js/pages/Budgets/BudgetShow.vue`:

```vue
<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Budget Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <!-- Budget info here -->
            </div>

            <!-- Comments Section -->
            <CommentsSection
                commentableType="Budget"
                :commentableId="budgetId"
            />
        </div>
    </DashboardLayout>
</template>

<script setup>
import CommentsSection from "@/components/comments/CommentsSection.vue";
// Other imports...

const budgetId = ref(route.params.id);
</script>
```

---

### Step 3: Add CommentsSection to Expense Detail View

#### 3.1 Update Expense Show Component

Create or update `resources/js/pages/Expenses/ExpenseShow.vue`:

```vue
<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Expense Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <!-- Expense info here -->
            </div>

            <!-- Comments Section -->
            <CommentsSection
                commentableType="Expense"
                :commentableId="expenseId"
            />
        </div>
    </DashboardLayout>
</template>

<script setup>
import CommentsSection from "@/components/comments/CommentsSection.vue";
// Other imports...

const expenseId = ref(route.params.id);
</script>
```

---

### Step 4: Update Sidebar Navigation

#### 4.1 Add Comments Menu Item

Update `resources/js/layouts/DashboardLayout.vue` sidebar menu:

```vue
<!-- In sidebar menu items -->
<template v-if="authStore.user">
    <!-- Existing menu items... -->

    <!-- Comments Menu Item (All Roles) -->
    <li>
        <router-link
            to="/comments"
            class="flex items-center px-4 py-2.5 text-gray-300 hover:bg-blue-900 hover:text-white transition-colors duration-150 rounded-lg"
            :class="{ 'bg-blue-900 text-white': isActiveRoute('/comments') }"
        >
            <i class="fas fa-comments w-5 mr-3"></i>
            <span>Comments</span>
            <span
                v-if="commentCount > 0"
                class="ml-auto px-2 py-0.5 bg-red-500 text-white text-xs rounded-full"
            >
                {{ commentCount }}
            </span>
        </router-link>
    </li>
</template>
```

#### 4.2 Add Comment Count (Optional)

If you want a global comment indicator:

```javascript
import { ref, onMounted } from "vue";
import axios from "axios";

const commentCount = ref(0);

const fetchUnreadCommentCount = async () => {
    try {
        const response = await axios.get("/api/v1/comments/unread-count");
        commentCount.value = response.data.count;
    } catch (error) {
        console.error("Failed to fetch comment count:", error);
    }
};

onMounted(() => {
    fetchUnreadCommentCount();
});
```

---

### Step 5: Install Required Dependencies

Ensure `date-fns` is installed for date formatting:

```bash
npm install date-fns
```

---

### Step 6: Test Integration

#### 6.1 Verify API Routes

```bash
php artisan route:list --name=comments
```

Expected output:

```
GET|HEAD  api/v1/comments .................. comments.index
POST      api/v1/comments .................. comments.store
GET|HEAD  api/v1/comments/{comment} ........ comments.show
PUT|PATCH api/v1/comments/{comment} ........ comments.update
DELETE    api/v1/comments/{comment} ........ comments.destroy
GET|HEAD  api/v1/comment-attachments/{id}/download
```

#### 6.2 Test Creating Comment

1. Navigate to a Project detail page
2. Click "Add Comment"
3. Type a comment with @mention: "Great work @john!"
4. Upload a file (optional)
5. Click "Comment"
6. Verify comment appears in list
7. Verify mentioned user receives notification

#### 6.3 Test Threading

1. Click "Reply" on an existing comment
2. Type reply content
3. Submit
4. Verify reply appears nested under parent

#### 6.4 Test Edit/Delete

1. Find your own comment
2. Click "..." menu
3. Click "Edit"
4. Update content
5. Save
6. Verify "(edited)" indicator appears
7. Click "Delete"
8. Confirm deletion
9. Verify comment removed (or shows [deleted] if has replies)

---

## Usage Examples

### Basic Integration

```vue
<CommentsSection commentableType="Project" :commentableId="projectId" />
```

### With Refresh Control

```vue
<template>
    <div>
        <button @click="refreshComments">Refresh Comments</button>

        <CommentsSection
            ref="commentsRef"
            commentableType="Budget"
            :commentableId="budgetId"
        />
    </div>
</template>

<script setup>
import { ref } from "vue";
import CommentsSection from "@/components/comments/CommentsSection.vue";

const commentsRef = ref(null);

const refreshComments = () => {
    if (commentsRef.value) {
        commentsRef.value.refresh();
    }
};
</script>
```

### As a Tab

```vue
<template>
    <div class="tabs">
        <button @click="activeTab = 'details'">Details</button>
        <button @click="activeTab = 'comments'">
            Comments
            <span class="badge">{{ commentCount }}</span>
        </button>
    </div>

    <div v-if="activeTab === 'details'">
        <!-- Details content -->
    </div>

    <div v-if="activeTab === 'comments'">
        <CommentsSection
            commentableType="Expense"
            :commentableId="expenseId"
            @comment-count="(count) => (commentCount = count)"
        />
    </div>
</template>
```

---

## Component Props & Events

### CommentsSection Props

| Prop              | Type          | Required | Description                                 |
| ----------------- | ------------- | -------- | ------------------------------------------- |
| `commentableType` | String        | Yes      | Entity type: 'Project', 'Budget', 'Expense' |
| `commentableId`   | Number/String | Yes      | Entity ID                                   |

### CommentsSection Events

None (internally managed)

### CommentsSection Methods (via ref)

| Method      | Description                        |
| ----------- | ---------------------------------- |
| `refresh()` | Manually refresh the comments list |

---

## Styling Customization

### Override Default Colors

Add to your global CSS or component:

```css
/* Change primary color */
.comments-section .bg-blue-800 {
    @apply bg-purple-800;
}

.comments-section .text-blue-800 {
    @apply text-purple-800;
}

/* Customize comment item */
.comment-item {
    border-left: 3px solid theme("colors.blue.800");
    padding-left: 1rem;
}

/* Custom avatar colors */
.comment-item .avatar {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
}
```

---

## Performance Optimization

### Lazy Loading Comments

```vue
<template>
    <div>
        <button @click="showComments = true" v-if="!showComments">
            Show Comments ({{ commentCount }})
        </button>

        <CommentsSection
            v-if="showComments"
            commentableType="Project"
            :commentableId="projectId"
        />
    </div>
</template>
```

### Pagination Settings

Adjust in backend `CommentController`:

```php
// app/Http/Controllers/CommentController.php

public function index(Request $request)
{
    $perPage = $request->input('per_page', 20); // Increase from 15
    // ...
}
```

---

## Troubleshooting Integration

### Issue: Components not rendering

**Solution:**

1. Check component import paths
2. Verify Vue router setup
3. Check browser console for errors
4. Ensure authentication is working

```bash
# Check if components exist
ls resources/js/components/comments/
```

### Issue: API endpoints not found

**Solution:**

1. Clear route cache: `php artisan route:clear`
2. Re-cache routes: `php artisan route:cache`
3. Verify routes file: `php artisan route:list --name=comments`

### Issue: @mention autocomplete not working

**Solution:**

1. Verify user search endpoint exists
2. Check network tab for API calls
3. Ensure users table has data
4. Check console for JavaScript errors

```javascript
// Test user search endpoint
axios
    .get("/api/v1/users/search?per_page=100")
    .then((response) => console.log(response.data))
    .catch((error) => console.error(error));
```

### Issue: File uploads failing

**Solution:**

1. Check file size (must be < 2MB)
2. Verify file type is allowed (PDF, DOC, images)
3. Check Laravel upload limits in `php.ini`:
    ```ini
    upload_max_filesize = 10M
    post_max_size = 10M
    ```
4. Verify storage directory permissions:
    ```bash
    chmod -R 775 storage/
    ```

### Issue: Comments not loading for entity

**Solution:**

1. Verify `commentableType` matches database value exactly
2. Check `commentable_id` is correct
3. Test API endpoint directly:
    ```bash
    curl "http://localhost:8000/api/v1/comments?commentable_type=Project&commentable_id=1" \
      -H "Authorization: Bearer YOUR_TOKEN"
    ```

---

## Security Checklist

Before deploying to production:

- [ ] All comment content is escaped in frontend (v-html uses renderContent method)
- [ ] File upload validation is in place (size, type, count)
- [ ] Authentication is required for all endpoints
- [ ] Authorization policies are enforced (users can only edit/delete own comments)
- [ ] CSRF protection is enabled
- [ ] API rate limiting is configured
- [ ] Uploaded files are stored outside public directory
- [ ] File downloads require authentication

---

## Testing Checklist

Integration tests to perform:

- [ ] Create root comment on Project
- [ ] Create reply to comment
- [ ] Edit own comment
- [ ] Cannot edit other user's comment
- [ ] Delete own comment
- [ ] Cannot delete other user's comment
- [ ] Upload PDF attachment
- [ ] Upload image attachment
- [ ] Download attachment
- [ ] @mention user (check notification)
- [ ] View nested replies (3+ levels)
- [ ] Pagination works (if > 15 comments)
- [ ] Soft delete preserves thread structure
- [ ] Comments display on Budget detail
- [ ] Comments display on Expense detail

---

## Migration to Production

### 1. Database

```bash
php artisan migrate --force
php artisan db:seed --class=CommentSeeder --force
```

### 2. Storage

Ensure storage directory is writable:

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### 3. Frontend Build

```bash
npm run build
```

### 4. Cache Clear

```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

### 5. Verify

```bash
php artisan route:list --name=comments
php artisan test --filter=Comment
```

---

## Additional Resources

- **API Documentation:** `docs/comments/API_ENDPOINTS.md`
- **Module Overview:** `docs/comments/OVERVIEW.md`
- **Laravel Policies:** https://laravel.com/docs/12.x/authorization
- **Vue Composition API:** https://vuejs.org/guide/extras/composition-api-faq
- **TailwindCSS:** https://tailwindcss.com/docs
- **date-fns:** https://date-fns.org/docs/

---

## Support

For issues or questions:

1. Check docs in `/docs/comments/`
2. Review test files in `/tests/Feature/Comments/`
3. Contact developer: bguvava (https://bguvava.com)

---

**Last Updated:** November 19, 2025  
**Version:** 1.0.0

ISSUES FOUND:
==============
# GLOBAL ISSUES/BUGS TO BE FIXED:

1. session lockout: 
- when the 30 seconds countdown finishes its countdown it follows old lockout flow instead of locking the system and wait for user input. So when the user enters password they will be logged out irregardless of their password being correct because the lockout uses the old lockout flow. When user enter password on the lockout screen they must resume their session. 

2.
3.
4.
5.
6.

==============================================
# Programs Manager Role Issues/Bugs:
1. Projects (/projects):
- Add New Project modal is not giving a confirmation message (via sweet alert) when new project is added, no errors shown when clicked and no response when the add project button is clicked. When adding the project details the Location and selected donors are not being saved into the database. But when i check on the projects list the new projects are there and added and listed in the system 
- Project Detail View Modal: Comments are not working they are showing the same error as shown on the project officer role. 
- Project Detail View Modal: when all user roles try to add Comments, the whole project detail view modal freezes and becomes non responsive the only way out is to refresh the browser. 
- Project List: Archive button shows is not responsive and shows these errors: 
`````
projects:84  [Vue warn]: Unhandled error during execution of native event handler 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ runtime-core.esm-bundler.js:51
logError @ runtime-core.esm-bundler.js:263
handleError @ runtime-core.esm-bundler.js:255
(anonymous) @ runtime-core.esm-bundler.js:209
Promise.catch
callWithAsyncErrorHandling @ runtime-core.esm-bundler.js:208
invoker @ runtime-dom.esm-bundler.js:730
ProjectsList.vue:574  Uncaught (in promise) ReferenceError: canzimSwal is not defined
    at Proxy.handleArchive (ProjectsList.vue:574:20)
    at onClick (ProjectsList.vue:345:49)
    at callWithErrorHandling (runtime-core.esm-bundler.js:199:19)
    at callWithAsyncErrorHandling (runtime-core.esm-bundler.js:206:17)
    at HTMLButtonElement.invoker (runtime-dom.esm-bundler.js:730:5)
handleArchive @ ProjectsList.vue:574
onClick @ ProjectsList.vue:345
callWithErrorHandling @ runtime-core.esm-bundler.js:199
callWithAsyncErrorHandling @ runtime-core.esm-bundler.js:206
invoker @ runtime-dom.esm-bundler.js:730
`````
- Project Edit: When editing the project details, the Project status is not updating immediately it only updates after refresh. The Location and selected donors are not being loaded or are not being saved during project creation. 
- it must show  a success/ failure sweet alert message when user presses update button. 
`````

`````

2. Add New Donor: there is no option or field to add the Donor Funding total amount. 
- Donors (/donors) the donor list all donors funding totals are written as $0.00 but the info cards are showing amounts for donated funds. 

3. Budget:
- Create a budget: when the user creates a budget they get this error messages: "Server Error An unexpected error occurred. Please try again later." and this "Error Failed to create budget: Budget total exceeds available donor funding" even if the budget is not exceeding. The browser console shows these errors:
`````
AddBudgetModal.vue:362   POST http://127.0.0.1:8000/api/v1/budgets 500 (Internal Server Error)
dispatchXhrRequest @ xhr.js:198
xhr @ xhr.js:15
dispatchRequest @ dispatchRequest.js:51
Promise.then
_request @ Axios.js:163
request @ Axios.js:40
httpMethod @ Axios.js:224
wrap @ bind.js:12
submitForm @ AddBudgetModal.vue:362
(anonymous) @ runtime-dom.esm-bundler.js:1764
callWithErrorHandling @ runtime-core.esm-bundler.js:199
callWithAsyncErrorHandling @ runtime-core.esm-bundler.js:206
invoker @ runtime-dom.esm-bundler.js:730
`````

4.
5.
6.

==============================================
# Finance Officer Role Issues/Bugs:
1. a. Projects: Project Detail View Modal:
-  on load this modal shows these browser console logs:
`````
projects:84 [Vue warn]: Unhandled error during execution of render function 
  at <CommentItem key=3 comment= {id: 3, content: 'dsgds', user: {…}, parent_id: null, attachments: Array(1), …} commentableType="Project"  ... > 
  at <CommentsList ref="commentsListRef" commentableType="Project" commentableId=1  ... > 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7525
run @ chunk-BZD72IEI.js?v=2751eb6a:505
setupRenderEffect @ chunk-BZD72IEI.js?v=2751eb6a:7660
mountComponent @ chunk-BZD72IEI.js?v=2751eb6a:7434
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7386
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7316
patch @ chunk-BZD72IEI.js?v=2751eb6a:6864
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
mountElement @ chunk-BZD72IEI.js?v=2751eb6a:7057
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7012
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7316
patch @ chunk-BZD72IEI.js?v=2751eb6a:6864
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=2751eb6a:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2481
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
viewProject @ ProjectsList.vue:547
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <CommentsList ref="commentsListRef" commentableType="Project" commentableId=1  ... > 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=2751eb6a:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2481
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
viewProject @ ProjectsList.vue:547
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
date-fns.js?v=2751eb6a:2375 Uncaught (in promise) RangeError: Invalid time value
    at format (date-fns.js?v=2751eb6a:2375:11)
    at Proxy.formatDateFull (CommentItem.vue:238:12)
    at Proxy._sfc_render (CommentItem.vue:29:37)
    at renderComponentRoot (chunk-BZD72IEI.js?v=2751eb6a:8720:17)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=2751eb6a:7525:46)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=2751eb6a:505:19)
    at setupRenderEffect (chunk-BZD72IEI.js?v=2751eb6a:7660:5)
    at mountComponent (chunk-BZD72IEI.js?v=2751eb6a:7434:7)
    at processComponent (chunk-BZD72IEI.js?v=2751eb6a:7386:9)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6890:11)
format @ date-fns.js?v=2751eb6a:2375
formatDateFull @ CommentItem.vue:238
_sfc_render @ CommentItem.vue:29
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7525
run @ chunk-BZD72IEI.js?v=2751eb6a:505
setupRenderEffect @ chunk-BZD72IEI.js?v=2751eb6a:7660
mountComponent @ chunk-BZD72IEI.js?v=2751eb6a:7434
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7386
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7316
patch @ chunk-BZD72IEI.js?v=2751eb6a:6864
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
mountElement @ chunk-BZD72IEI.js?v=2751eb6a:7057
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7012
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
mountChildren @ chunk-BZD72IEI.js?v=2751eb6a:7134
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7316
patch @ chunk-BZD72IEI.js?v=2751eb6a:6864
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=2751eb6a:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2481
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
viewProject @ ProjectsList.vue:547
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
`````

1. b. Projects: Project Detail View Modal:
-  Project view modal -> Comments: when the project officer adds a comment they get a success message but the comment is not displayed on the comments.  The comments are never stored or displayed again. 
- after posting a comment the Project Detail View modal refuses to close via the close buttons. 
Immediately the browser console shows these errors:
`````
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
chunk-BZD72IEI.js?v=2751eb6a:8025 Uncaught (in promise) TypeError: Cannot destructure property 'type' of 'vnode' as it is null.
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8025:7)
    at unmountComponent (chunk-BZD72IEI.js?v=2751eb6a:8162:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8057:7)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8089:9)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8081:9)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6841:7)
    at patchBlockChildren (chunk-BZD72IEI.js?v=2751eb6a:7256:7)
    at patchElement (chunk-BZD72IEI.js?v=2751eb6a:7174:7)
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8025
unmountComponent @ chunk-BZD72IEI.js?v=2751eb6a:8162
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8057
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8089
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
patch @ chunk-BZD72IEI.js?v=2751eb6a:6841
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <CommentsList ref="commentsListRef" commentableType="Project" commentableId=1  ... > 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
baseWatchOptions.call @ chunk-BZD72IEI.js?v=2751eb6a:8413
job @ chunk-BZD72IEI.js?v=2751eb6a:2026
flushPreFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2452
updateComponentPreRender @ chunk-BZD72IEI.js?v=2751eb6a:7670
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7589
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
chunk-BZD72IEI.js?v=2751eb6a:10688 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'nextSibling')
    at nextSibling (chunk-BZD72IEI.js?v=2751eb6a:10688:31)
    at getNextHostNode (chunk-BZD72IEI.js?v=2751eb6a:8186:16)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6840:16)
    at patchBlockChildren (chunk-BZD72IEI.js?v=2751eb6a:7256:7)
    at patchElement (chunk-BZD72IEI.js?v=2751eb6a:7174:7)
    at processElement (chunk-BZD72IEI.js?v=2751eb6a:7028:9)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6878:11)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=2751eb6a:7612:9)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=2751eb6a:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=2751eb6a:543:12)
nextSibling @ chunk-BZD72IEI.js?v=2751eb6a:10688
getNextHostNode @ chunk-BZD72IEI.js?v=2751eb6a:8186
patch @ chunk-BZD72IEI.js?v=2751eb6a:6840
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
baseWatchOptions.call @ chunk-BZD72IEI.js?v=2751eb6a:8413
job @ chunk-BZD72IEI.js?v=2751eb6a:2026
flushPreFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2452
updateComponentPreRender @ chunk-BZD72IEI.js?v=2751eb6a:7670
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7589
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
`````
1. c. Generate Project PDF report shows same issues and errors as found on the Project Officer role. 


2.
3.
4.
5.
6.

==============================================
# Project Officer Role Issues/Bugs:
1. Projects (/projects):
- the project officer must only see the projects that are assigned to them not all projects in the system.
-  hide/remove the edit but for the projects officer role they must not be able to edit project details. they are only allowed to make comments on the project view. 
- on projects load it shows this error message: "Access Denied. You do not have permission to perform this action." 
-  Project view modal -> Comments: when the project officer adds a comment they get a success message but the comment is not displayed on the comments.  The comments are never stored or displayed again. 
- after posting a comment the Project Detail View modal refuses to close via the close buttons. 
Immediately the browser console shows these errors:
`````
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
chunk-BZD72IEI.js?v=2751eb6a:8025 Uncaught (in promise) TypeError: Cannot destructure property 'type' of 'vnode' as it is null.
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8025:7)
    at unmountComponent (chunk-BZD72IEI.js?v=2751eb6a:8162:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8057:7)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8089:9)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8081:9)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6841:7)
    at patchBlockChildren (chunk-BZD72IEI.js?v=2751eb6a:7256:7)
    at patchElement (chunk-BZD72IEI.js?v=2751eb6a:7174:7)
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8025
unmountComponent @ chunk-BZD72IEI.js?v=2751eb6a:8162
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8057
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8089
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
patch @ chunk-BZD72IEI.js?v=2751eb6a:6841
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <CommentsList ref="commentsListRef" commentableType="Project" commentableId=1  ... > 
  at <CommentsSection commentable-type="Project" commentable-id=1 > 
  at <ViewProjectModal is-open=true project= {id: 1, code: 'PROJ-2025-0001', name: 'Zimbabwe Climate Resilience Initiative', description: 'Building community resilience to climate change through sustainable agriculture and water management', start_date: '2024-01-01T00:00:00.000000Z', …} onClose=fn  ... > 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
baseWatchOptions.call @ chunk-BZD72IEI.js?v=2751eb6a:8413
job @ chunk-BZD72IEI.js?v=2751eb6a:2026
flushPreFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2452
updateComponentPreRender @ chunk-BZD72IEI.js?v=2751eb6a:7670
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7589
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
chunk-BZD72IEI.js?v=2751eb6a:10688 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'nextSibling')
    at nextSibling (chunk-BZD72IEI.js?v=2751eb6a:10688:31)
    at getNextHostNode (chunk-BZD72IEI.js?v=2751eb6a:8186:16)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6840:16)
    at patchBlockChildren (chunk-BZD72IEI.js?v=2751eb6a:7256:7)
    at patchElement (chunk-BZD72IEI.js?v=2751eb6a:7174:7)
    at processElement (chunk-BZD72IEI.js?v=2751eb6a:7028:9)
    at patch (chunk-BZD72IEI.js?v=2751eb6a:6878:11)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=2751eb6a:7612:9)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=2751eb6a:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=2751eb6a:543:12)
nextSibling @ chunk-BZD72IEI.js?v=2751eb6a:10688
getNextHostNode @ chunk-BZD72IEI.js?v=2751eb6a:8186
patch @ chunk-BZD72IEI.js?v=2751eb6a:6840
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
fetchComments @ CommentsList.vue:168
await in fetchComments
(anonymous) @ CommentsList.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
baseWatchOptions.call @ chunk-BZD72IEI.js?v=2751eb6a:8413
job @ chunk-BZD72IEI.js?v=2751eb6a:2026
flushPreFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2452
updateComponentPreRender @ chunk-BZD72IEI.js?v=2751eb6a:7670
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7589
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
handleCommentAdded @ CommentsSection.vue:94
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
submitComment @ CommentBox.vue:386
await in submitComment
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
projects:84 [Vue warn]: Unhandled error during execution of component update 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2298
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
set @ chunk-BZD72IEI.js?v=2751eb6a:1744
_createVNode.onClose._cache.<computed>._cache.<computed> @ ProjectsList.vue:436
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
closeModal @ ViewProjectModal.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
chunk-BZD72IEI.js?v=2751eb6a:8150 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'type')
    at unmountComponent (chunk-BZD72IEI.js?v=2751eb6a:8150:18)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8057:7)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8089:9)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8081:9)
    at unmountChildren (chunk-BZD72IEI.js?v=2751eb6a:8176:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8081:9)
    at unmountComponent (chunk-BZD72IEI.js?v=2751eb6a:8162:7)
    at unmount (chunk-BZD72IEI.js?v=2751eb6a:8057:7)
unmountComponent @ chunk-BZD72IEI.js?v=2751eb6a:8150
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8057
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8089
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
unmountComponent @ chunk-BZD72IEI.js?v=2751eb6a:8162
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8057
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
unmountComponent @ chunk-BZD72IEI.js?v=2751eb6a:8162
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8057
unmountChildren @ chunk-BZD72IEI.js?v=2751eb6a:8176
unmount @ chunk-BZD72IEI.js?v=2751eb6a:8081
patch @ chunk-BZD72IEI.js?v=2751eb6a:6841
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
updateComponent @ chunk-BZD72IEI.js?v=2751eb6a:7463
processComponent @ chunk-BZD72IEI.js?v=2751eb6a:7397
patch @ chunk-BZD72IEI.js?v=2751eb6a:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=2751eb6a:7256
patchElement @ chunk-BZD72IEI.js?v=2751eb6a:7174
processElement @ chunk-BZD72IEI.js?v=2751eb6a:7028
patch @ chunk-BZD72IEI.js?v=2751eb6a:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7612
run @ chunk-BZD72IEI.js?v=2751eb6a:505
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
notify @ chunk-BZD72IEI.js?v=2751eb6a:853
trigger @ chunk-BZD72IEI.js?v=2751eb6a:827
set value @ chunk-BZD72IEI.js?v=2751eb6a:1706
set @ chunk-BZD72IEI.js?v=2751eb6a:1744
_createVNode.onClose._cache.<computed>._cache.<computed> @ ProjectsList.vue:436
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
closeModal @ ViewProjectModal.vue:254
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
`````

1b. Generate Report pdf icon:  shows these errors: "Server Error. An unexpected error occurred. Please try again later." and this: "Report Generation Failed. Request failed with status code 500" and this browser console error:
`````
projectStore.js:274  POST http://127.0.0.1:8000/api/v1/projects/1/report 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
generateReport @ projectStore.js:274
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
handleGenerateReport @ ProjectsList.vue:603
onClick @ ProjectsList.vue:352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
`````

2.  Expenses (/expenses) module:
- when project officer try to create a new expense using create expense buttons, no action nor any modal is displayed. show the add expense modal view. 

3. Documents Module (/dashboard/documents): Upload Document Modal:
- when project officer try to upload a file they get this error: "Server Error An unexpected error occurred. Please try again later." and this: "Error Undefined array key "documentable_type" and the browser console show these errors: 
`````
UploadDocumentModal.vue:336  POST http://127.0.0.1:8000/api/v1/documents 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
submitForm @ UploadDocumentModal.vue:336
(anonymous) @ UploadDocumentModal.vue:38
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
documents:73 Upload error: AxiosError {message: 'Request failed with status code 500', name: 'AxiosError', code: 'ERR_BAD_RESPONSE', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ documents:73
submitForm @ UploadDocumentModal.vue:345
await in submitForm
(anonymous) @ UploadDocumentModal.vue:38
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
`````

4.
5.
6.

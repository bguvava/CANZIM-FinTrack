ISSUES FOUND:
=====================================================
**Programs Manager Role**
1. On Dashboard Module:
- Quick Action Buttons: Create Project and Submit expense are showing 404 Not found error
---
2. Projects Module:
- View Project shows this error: (Error Failed to load comments. Please refresh the page. Confirm) and the browser console shows these messages:
projects:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
CommentsList.vue:159  GET http://127.0.0.1:8000/api/v1/comments?commentable_type=Project&commentable_id=1&page=1 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
_request @ axios.js?v=be3f49fb:2327
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
fetchComments @ CommentsList.vue:159
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
viewProject @ ProjectsList.vue:542
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
projects:84 Failed to fetch comments: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchComments @ CommentsList.vue:177
await in fetchComments
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
viewProject @ ProjectsList.vue:542
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

- posting a comment shows these errors:
projects:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
CommentsList.vue:159  GET http://127.0.0.1:8000/api/v1/comments?commentable_type=Project&commentable_id=4&page=1 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
_request @ axios.js?v=be3f49fb:2327
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
fetchComments @ CommentsList.vue:159
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
viewProject @ ProjectsList.vue:542
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
projects:84 Failed to fetch comments: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchComments @ CommentsList.vue:177
await in fetchComments
(anonymous) @ CommentsList.vue:259
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
viewProject @ ProjectsList.vue:542
onClick @ ProjectsList.vue:326
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
CommentBox.vue:242  GET http://127.0.0.1:8000/api/v1/users/search?per_page=100 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
_request @ axios.js?v=be3f49fb:2327
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
fetchUsers @ CommentBox.vue:242
(anonymous) @ CommentBox.vue:434
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
set @ chunk-BZD72IEI.js?v=21cde2e8:1744
_createElementBlock.onClick._cache.<computed>._cache.<computed> @ CommentsSection.vue:28
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
projects:84 Failed to fetch users: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchUsers @ CommentBox.vue:247
await in fetchUsers
(anonymous) @ CommentBox.vue:434
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2523
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
notify @ chunk-BZD72IEI.js?v=21cde2e8:853
trigger @ chunk-BZD72IEI.js?v=21cde2e8:827
set value @ chunk-BZD72IEI.js?v=21cde2e8:1706
set @ chunk-BZD72IEI.js?v=21cde2e8:1744
_createElementBlock.onClick._cache.<computed>._cache.<computed> @ CommentsSection.vue:28
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
CommentBox.vue:382  POST http://127.0.0.1:8000/api/v1/comments 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
_request @ axios.js?v=be3f49fb:2327
request @ axios.js?v=be3f49fb:2219
httpMethod @ axios.js?v=be3f49fb:2356
wrap @ axios.js?v=be3f49fb:8
submitComment @ CommentBox.vue:382
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

- Export PDF shows this error: (Server Error An unexpected error occurred. Please try again later.) and the browser console logs shows this:
projects:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
projectStore.js:273  POST http://127.0.0.1:8000/api/v1/projects/1/report 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
httpMethod @ axios.js?v=be3f49fb:2356
wrap @ axios.js?v=be3f49fb:8
generateReport @ projectStore.js:273
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
handleGenerateReport @ ProjectsList.vue:601
onClick @ ProjectsList.vue:352
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
projects:84 [Vue warn]: Unhandled error during execution of native event handler 
  at <ProjectsList> 
  at <DashboardLayout> 
  at <Projects>
console.<computed> @ projects:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
logError @ chunk-BZD72IEI.js?v=21cde2e8:2360
handleError @ chunk-BZD72IEI.js?v=21cde2e8:2352
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:2306
Promise.catch
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2305
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358
ProjectsList.vue:608 Uncaught (in promise) ReferenceError: showError is not defined
    at Proxy.handleGenerateReport (ProjectsList.vue:608:9)
handleGenerateReport @ ProjectsList.vue:608
await in handleGenerateReport
onClick @ ProjectsList.vue:352
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

- Add New Project Modal: when user enters all the details and click on create project button, nothing happens, no errors no logs not response no confirmation, it just hangs like that. Fix the add project flow to be fully functional without errors, the project is added in the system and can only be viewed after refreshing the project list
---

3. Budgets Module:
- Add Budget Modal: when user enters all information and click on create budget button, it shows this error: (Validation Error Please check the form for errors.) and the browser console shows these messages: budgets:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
AddBudgetModal.vue:367  POST http://127.0.0.1:8000/api/v1/budgets 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
httpMethod @ axios.js?v=be3f49fb:2356
wrap @ axios.js?v=be3f49fb:8
submitForm @ AddBudgetModal.vue:367
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

---
4. Expenses Module:
- on load expenses modules shows this:
expenses:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
expenses:84 [Vue Router warn]: No match found for location with path "/expenses"
console.<computed> @ expenses:84
warn$1 @ vue-router.js?v=521cc230:207
resolve @ vue-router.js?v=521cc230:2033
pushWithRedirect @ vue-router.js?v=521cc230:2117
push @ vue-router.js?v=521cc230:2089
install @ vue-router.js?v=521cc230:2321
use @ chunk-BZD72IEI.js?v=21cde2e8:6046
(anonymous) @ bootstrap-expenses.js:49

- on load Pending Approval shows this: 
pending-approval:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
pending-approval:84 [Vue warn]: injection "Symbol(router)" not found. 
  at <PendingApproval> 
  at <DashboardLayout> 
  at <PendingApproval>
console.<computed> @ pending-approval:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
inject @ chunk-BZD72IEI.js?v=21cde2e8:6216
useRouter @ vue-router.js?v=521cc230:2356
setup @ PendingApproval.vue:317
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
setupStatefulComponent @ chunk-BZD72IEI.js?v=21cde2e8:10131
setupComponent @ chunk-BZD72IEI.js?v=21cde2e8:10092
mountComponent @ chunk-BZD72IEI.js?v=21cde2e8:7420
processComponent @ chunk-BZD72IEI.js?v=21cde2e8:7386
patch @ chunk-BZD72IEI.js?v=21cde2e8:6890
mountChildren @ chunk-BZD72IEI.js?v=21cde2e8:7134
processFragment @ chunk-BZD72IEI.js?v=21cde2e8:7316
patch @ chunk-BZD72IEI.js?v=21cde2e8:6864
mountChildren @ chunk-BZD72IEI.js?v=21cde2e8:7134
mountElement @ chunk-BZD72IEI.js?v=21cde2e8:7057
processElement @ chunk-BZD72IEI.js?v=21cde2e8:7012
patch @ chunk-BZD72IEI.js?v=21cde2e8:6878
mountChildren @ chunk-BZD72IEI.js?v=21cde2e8:7134
mountElement @ chunk-BZD72IEI.js?v=21cde2e8:7057
processElement @ chunk-BZD72IEI.js?v=21cde2e8:7012
patch @ chunk-BZD72IEI.js?v=21cde2e8:6878
mountChildren @ chunk-BZD72IEI.js?v=21cde2e8:7134
mountElement @ chunk-BZD72IEI.js?v=21cde2e8:7057
processElement @ chunk-BZD72IEI.js?v=21cde2e8:7012
patch @ chunk-BZD72IEI.js?v=21cde2e8:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7532
run @ chunk-BZD72IEI.js?v=21cde2e8:505
setupRenderEffect @ chunk-BZD72IEI.js?v=21cde2e8:7660
mountComponent @ chunk-BZD72IEI.js?v=21cde2e8:7434
processComponent @ chunk-BZD72IEI.js?v=21cde2e8:7386
patch @ chunk-BZD72IEI.js?v=21cde2e8:6890
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7532
run @ chunk-BZD72IEI.js?v=21cde2e8:505
setupRenderEffect @ chunk-BZD72IEI.js?v=21cde2e8:7660
mountComponent @ chunk-BZD72IEI.js?v=21cde2e8:7434
processComponent @ chunk-BZD72IEI.js?v=21cde2e8:7386
patch @ chunk-BZD72IEI.js?v=21cde2e8:6890
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8197
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-pending-approval.js:18

---
5. Bank Accounts
- add bank account modal shows this when creating new account: (Please check the form for errors. Something went wrong.) browser console shows this:
bank-accounts:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
cashFlowStore.js:67  POST http://127.0.0.1:8000/api/v1/bank-accounts 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
httpMethod @ axios.js?v=be3f49fb:2356
wrap @ axios.js?v=be3f49fb:8
createBankAccount @ cashFlowStore.js:67
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
handleSubmit @ AddBankAccountModal.vue:307
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

---
6. Cash Flow Transactions shows these multiple errors on load: (Not Found The requested resource was not found.) and the browser console shows:
transactions:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
RecordInflowModal.vue:278  GET http://127.0.0.1:8000/api/v1/api/v1/projects?status=active&per_page=100 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
(anonymous) @ RecordInflowModal.vue:278
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-cash-flow-transactions.js:16
RecordOutflowModal.vue:326  GET http://127.0.0.1:8000/api/v1/api/v1/projects?status=active&per_page=100 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
(anonymous) @ RecordOutflowModal.vue:326
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-cash-flow-transactions.js:16
transactions:84 Failed to fetch projects: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ transactions:84
(anonymous) @ RecordInflowModal.vue:283
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-cash-flow-transactions.js:16
RecordOutflowModal.vue:329  GET http://127.0.0.1:8000/api/v1/api/v1/expenses?payment_status=unpaid&per_page=100 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
(anonymous) @ RecordOutflowModal.vue:329
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-cash-flow-transactions.js:16
transactions:84 Failed to fetch data: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ transactions:84
(anonymous) @ RecordOutflowModal.vue:337
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-cash-flow-transactions.js:16

---
7. Purchase Orders:
- create purchase order shows this: (Not Found The requested resource was not found.) and the browser console shows these:
purchase-orders:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
CreatePurchaseOrderModal.vue:421  GET http://127.0.0.1:8000/api/v1/api/v1/projects?status=active&per_page=100 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
loadProjects @ CreatePurchaseOrderModal.vue:421
(anonymous) @ CreatePurchaseOrderModal.vue:524
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
baseWatchOptions.call @ chunk-BZD72IEI.js?v=21cde2e8:8413
job @ chunk-BZD72IEI.js?v=21cde2e8:2026
flushPreFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2452
updateComponentPreRender @ chunk-BZD72IEI.js?v=21cde2e8:7670
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7589
run @ chunk-BZD72IEI.js?v=21cde2e8:505
updateComponent @ chunk-BZD72IEI.js?v=21cde2e8:7463
processComponent @ chunk-BZD72IEI.js?v=21cde2e8:7397
patch @ chunk-BZD72IEI.js?v=21cde2e8:6890
patchBlockChildren @ chunk-BZD72IEI.js?v=21cde2e8:7256
processFragment @ chunk-BZD72IEI.js?v=21cde2e8:7334
patch @ chunk-BZD72IEI.js?v=21cde2e8:6864
patchBlockChildren @ chunk-BZD72IEI.js?v=21cde2e8:7256
patchElement @ chunk-BZD72IEI.js?v=21cde2e8:7174
processElement @ chunk-BZD72IEI.js?v=21cde2e8:7028
patch @ chunk-BZD72IEI.js?v=21cde2e8:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7612
run @ chunk-BZD72IEI.js?v=21cde2e8:505
runIfDirty @ chunk-BZD72IEI.js?v=21cde2e8:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

---
8. Add Vendor:
- adding a new vendor shows this: (Validation Error Please check the form for errors.) browser console logs show:
vendors:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
purchaseOrderStore.js:282  POST http://127.0.0.1:8000/api/v1/vendors 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
httpMethod @ axios.js?v=be3f49fb:2356
wrap @ axios.js?v=be3f49fb:8
createVendor @ purchaseOrderStore.js:282
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
handleSubmit @ AddVendorModal.vue:291
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
invoker @ chunk-BZD72IEI.js?v=21cde2e8:11358

---
9. Donors Module:
- Add Donor button is not responding or showing the respective Modal for adding the donors.
- on page load it shows these browser console messages: 
donors:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
donors:84 [Vue warn]: Unhandled error during execution of render function 
  at <DonorsList> 
  at <DashboardLayout> 
  at <Donors>
console.<computed> @ donors:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
logError @ chunk-BZD72IEI.js?v=21cde2e8:2360
handleError @ chunk-BZD72IEI.js?v=21cde2e8:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=21cde2e8:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7603
run @ chunk-BZD72IEI.js?v=21cde2e8:505
runIfDirty @ chunk-BZD72IEI.js?v=21cde2e8:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchDonors @ donorStore.js:99
await in fetchDonors
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:865
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16
donors:84 [Vue warn]: Unhandled error during execution of component update 
  at <DonorsList> 
  at <DashboardLayout> 
  at <Donors>
console.<computed> @ donors:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
logError @ chunk-BZD72IEI.js?v=21cde2e8:2360
handleError @ chunk-BZD72IEI.js?v=21cde2e8:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2298
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchDonors @ donorStore.js:99
await in fetchDonors
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:865
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16
DonorsList.vue:252 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'length')
    at Proxy._sfc_render (DonorsList.vue:252:31)
    at renderComponentRoot (chunk-BZD72IEI.js?v=21cde2e8:8720:17)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=21cde2e8:7603:26)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=21cde2e8:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=21cde2e8:543:12)
    at callWithErrorHandling (chunk-BZD72IEI.js?v=21cde2e8:2296:33)
    at flushJobs (chunk-BZD72IEI.js?v=21cde2e8:2504:9)
_sfc_render @ DonorsList.vue:252
renderComponentRoot @ chunk-BZD72IEI.js?v=21cde2e8:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7603
run @ chunk-BZD72IEI.js?v=21cde2e8:505
runIfDirty @ chunk-BZD72IEI.js?v=21cde2e8:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchDonors @ donorStore.js:99
await in fetchDonors
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:865
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16
donors:84 [Vue warn]: Unhandled error during execution of render function 
  at <DonorsList> 
  at <DashboardLayout> 
  at <Donors>
console.<computed> @ donors:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
logError @ chunk-BZD72IEI.js?v=21cde2e8:2360
handleError @ chunk-BZD72IEI.js?v=21cde2e8:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=21cde2e8:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7603
run @ chunk-BZD72IEI.js?v=21cde2e8:505
runIfDirty @ chunk-BZD72IEI.js?v=21cde2e8:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchStatistics @ donorStore.js:124
await in fetchStatistics
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:866
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16
donors:84 [Vue warn]: Unhandled error during execution of component update 
  at <DonorsList> 
  at <DashboardLayout> 
  at <Donors>
console.<computed> @ donors:84
warn$1 @ chunk-BZD72IEI.js?v=21cde2e8:2149
logError @ chunk-BZD72IEI.js?v=21cde2e8:2360
handleError @ chunk-BZD72IEI.js?v=21cde2e8:2352
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2298
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchStatistics @ donorStore.js:124
await in fetchStatistics
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:866
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16
DonorsList.vue:252 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'length')
    at Proxy._sfc_render (DonorsList.vue:252:31)
    at renderComponentRoot (chunk-BZD72IEI.js?v=21cde2e8:8720:17)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=21cde2e8:7603:26)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=21cde2e8:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=21cde2e8:543:12)
    at callWithErrorHandling (chunk-BZD72IEI.js?v=21cde2e8:2296:33)
    at flushJobs (chunk-BZD72IEI.js?v=21cde2e8:2504:9)
_sfc_render @ DonorsList.vue:252
renderComponentRoot @ chunk-BZD72IEI.js?v=21cde2e8:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=21cde2e8:7603
run @ chunk-BZD72IEI.js?v=21cde2e8:505
runIfDirty @ chunk-BZD72IEI.js?v=21cde2e8:543
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
flushJobs @ chunk-BZD72IEI.js?v=21cde2e8:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=21cde2e8:2418
queueJob @ chunk-BZD72IEI.js?v=21cde2e8:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=21cde2e8:7654
trigger @ chunk-BZD72IEI.js?v=21cde2e8:533
endBatch @ chunk-BZD72IEI.js?v=21cde2e8:591
trigger @ chunk-BZD72IEI.js?v=21cde2e8:980
set @ chunk-BZD72IEI.js?v=21cde2e8:1268
set value @ chunk-BZD72IEI.js?v=21cde2e8:1796
set @ chunk-BZD72IEI.js?v=21cde2e8:1252
set @ pinia.js?v=bd6571d4:5201
fetchStatistics @ donorStore.js:124
await in fetchStatistics
wrappedAction @ pinia.js?v=bd6571d4:5508
store.<computed> @ pinia.js?v=bd6571d4:5205
(anonymous) @ DonorsList.vue:866
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-donors.js:16

---
10. 
- on load it shows these errors: (Error No query results for model [App\Models\User] activity-logs) and the browser console shows: 
activity-logs:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
ActivityLogs.vue:525  GET http://127.0.0.1:8000/api/v1/users/activity-logs?page=1&per_page=25 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
loadActivityLogs @ ActivityLogs.vue:525
(anonymous) @ ActivityLogs.vue:691
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ app.js:122
ActivityLogs.vue:525  GET http://127.0.0.1:8000/api/v1/users/activity-logs?page=1&per_page=25 404 (Not Found)
dispatchXhrRequest @ axios.js?v=be3f49fb:1696
xhr @ axios.js?v=be3f49fb:1573
dispatchRequest @ axios.js?v=be3f49fb:2107
Promise.then
_request @ axios.js?v=be3f49fb:2310
request @ axios.js?v=be3f49fb:2219
Axios.<computed> @ axios.js?v=be3f49fb:2346
wrap @ axios.js?v=be3f49fb:8
loadActivityLogs @ ActivityLogs.vue:525
(anonymous) @ ActivityLogs.vue:691
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-activity-logs.js:39
activity-logs:84 Error loading activity logs: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ activity-logs:84
loadActivityLogs @ ActivityLogs.vue:532
await in loadActivityLogs
(anonymous) @ ActivityLogs.vue:691
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ app.js:122
activity-logs:84 Error loading activity logs: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ activity-logs:84
loadActivityLogs @ ActivityLogs.vue:532
await in loadActivityLogs
(anonymous) @ ActivityLogs.vue:691
(anonymous) @ chunk-BZD72IEI.js?v=21cde2e8:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=21cde2e8:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=21cde2e8:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=21cde2e8:2481
render2 @ chunk-BZD72IEI.js?v=21cde2e8:8211
mount @ chunk-BZD72IEI.js?v=21cde2e8:6122
app.mount @ chunk-BZD72IEI.js?v=21cde2e8:12437
(anonymous) @ bootstrap-activity-logs.js:39


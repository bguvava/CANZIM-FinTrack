ISSUES FOUND:
==============
# GLOBAL ISSUES/BUGS TO BE FIXED:

1.  when users are automatically logged out and try to login again they see the login error "Login Failed. CSRF token mismatch."  these are the console error logs:

authStore.js:67  POST http://127.0.0.1:8000/api/v1/auth/login 419 (unknown status)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
login @ authStore.js:67
await in login
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
handleLogin @ LoginForm.vue:210
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358

- the only way they can successfully re-login in is when they refresh the browser.

2. Occasional console errors:
- users occassionally see these browser console errors:
purchase-orders:1 [Intervention] Slow network is detected. See https://www.chromestatus.com/feature/5636954674692096 for more details. Fallback font will be used while loading: https://fonts.bunny.net/instrument-sans/files/instrument-sans-latin-400-normal.woff2
purchase-orders:1 [Intervention] Slow network is detected. See https://www.chromestatus.com/feature/5636954674692096 for more details. Fallback font will be used while loading: https://fonts.bunny.net/instrument-sans/files/instrument-sans-latin-500-normal.woff2
purchase-orders:1 [Intervention] Slow network is detected. See https://www.chromestatus.com/feature/5636954674692096 for more details. Fallback font will be used while loading: https://fonts.bunny.net/instrument-sans/files/instrument-sans-latin-600-normal.woff2


3. session lockout: 
- design a 30 seconds countdown with options to stay in the session or continue to lockout.
- design a fully functional and effective session lockout screen that appears on the center of the screen with the user email and option to enter password to login or completely logout of the system. this lockout window must be effective in such a way that even if users refresh the browser or click outside the modal they must no gain access to the system until they provide valid password. 

4. redesign the logout modal and add colors (red and blue) to the buttons on that modal, check the attached screenshot ".github/bugs/screenbugs/logout_modal.png"

5.
6.

==============================================
# Programs Manager Role Issues/Bugs:
1.
2.
3.
4.
5.
6.

==============================================
# Finance Officer Role Issues/Bugs:

1. Dashboard (/dashboard):
- make sure all the info cards on the dashboard are fetching real dynamic data, they must not display static/hard coded data
- Pending Expenses info card  shows "$NaN", format this correctly
- make sure these section fetch real dynamic data: Budget vs Actual chart, Expense Categories chart, Recent Activity, 
- Quick Actions: all quick action buttons are not opening the respective links and views they are showing 404 not found error. These are the errors being displayed by the action buttons:

a. Create Project shows this error:
create:1 Uncaught TypeError: Failed to resolve module specifier "vue". Relative references must start with either "/", "./", or "../".

b. submit expense shows this error: 
create:1 Uncaught TypeError: Failed to resolve module specifier "vue". Relative references must start with either "/", "./", or "../".

c. View Reports shows this error: reports:1  GET http://127.0.0.1:8000/reports 404 (Not Found)


2. Expenses (/expenses):
- on load it shows these errors:
expenses:84 [Vue Router warn]: No match found for location with path "/expenses"
console.<computed> @ expenses:84
warn$1 @ vue-router.js?v=2751eb6a:207
resolve @ vue-router.js?v=2751eb6a:2033
pushWithRedirect @ vue-router.js?v=2751eb6a:2117
push @ vue-router.js?v=2751eb6a:2089
install @ vue-router.js?v=2751eb6a:2321
use @ chunk-BZD72IEI.js?v=2751eb6a:6046
(anonymous) @ bootstrap-expenses.js:49

2. Pending Review (/expenses/pending-review): it shows a blank black screen and these browser console errors:
pending-review:1 Uncaught TypeError: Failed to resolve module specifier "vue". Relative references must start with either "/", "./", or "../".


3. Cashflow (/cashflow):

3a. Add Bank Account Modal: this modal is adding the new bank account successfully but it shows a lot of console errors which are as follows:
---
bank-accounts:84 [Vue warn]: Unhandled error during execution of component update 
  at <DashboardLayout> 
  at <BankAccounts>
console.<computed> @ bank-accounts:84
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
noTracking @ chunk-BZD72IEI.js?v=2751eb6a:1157
unshift @ chunk-BZD72IEI.js?v=2751eb6a:1082
createBankAccount @ cashFlowStore.js:68
await in createBankAccount
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
handleSubmit @ AddBankAccountModal.vue:307
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
bank-accounts:84 [Vue warn]: Unhandled error during execution of render function 
  at <DashboardLayout> 
  at <BankAccounts>
console.<computed> @ bank-accounts:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
handleAccountCreated @ BankAccounts.vue:590
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
handleSubmit @ AddBankAccountModal.vue:309
await in handleSubmit
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
bank-accounts:84 [Vue warn]: Unhandled error during execution of component update 
  at <DashboardLayout> 
  at <BankAccounts>
console.<computed> @ bank-accounts:84
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
handleAccountCreated @ BankAccounts.vue:590
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
handleSubmit @ AddBankAccountModal.vue:309
await in handleSubmit
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
BankAccounts.vue:504 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'bank_name')
    at BankAccounts.vue:504:63
    at wrappedFn (chunk-BZD72IEI.js?v=2751eb6a:1116:19)
    at Array.map (<anonymous>)
    at apply (chunk-BZD72IEI.js?v=2751eb6a:1124:27)
    at Proxy.map (chunk-BZD72IEI.js?v=2751eb6a:1048:12)
    at ComputedRefImpl.fn (BankAccounts.vue:504:46)
    at refreshComputed (chunk-BZD72IEI.js?v=2751eb6a:659:29)
    at isDirty (chunk-BZD72IEI.js?v=2751eb6a:630:68)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=2751eb6a:542:9)
    at callWithErrorHandling (chunk-BZD72IEI.js?v=2751eb6a:2296:33)
(anonymous) @ BankAccounts.vue:504
wrappedFn @ chunk-BZD72IEI.js?v=2751eb6a:1116
apply @ chunk-BZD72IEI.js?v=2751eb6a:1124
map @ chunk-BZD72IEI.js?v=2751eb6a:1048
(anonymous) @ BankAccounts.vue:504
refreshComputed @ chunk-BZD72IEI.js?v=2751eb6a:659
isDirty @ chunk-BZD72IEI.js?v=2751eb6a:630
runIfDirty @ chunk-BZD72IEI.js?v=2751eb6a:542
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
flushJobs @ chunk-BZD72IEI.js?v=2751eb6a:2504
Promise.then
queueFlush @ chunk-BZD72IEI.js?v=2751eb6a:2418
queueJob @ chunk-BZD72IEI.js?v=2751eb6a:2413
effect2.scheduler @ chunk-BZD72IEI.js?v=2751eb6a:7654
trigger @ chunk-BZD72IEI.js?v=2751eb6a:533
endBatch @ chunk-BZD72IEI.js?v=2751eb6a:591
noTracking @ chunk-BZD72IEI.js?v=2751eb6a:1157
unshift @ chunk-BZD72IEI.js?v=2751eb6a:1082
createBankAccount @ cashFlowStore.js:68
await in createBankAccount
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
handleSubmit @ AddBankAccountModal.vue:307
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
cashFlowStore.js:33 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'is_active')
    at cashFlowStore.js:33:56
    at wrappedFn (chunk-BZD72IEI.js?v=2751eb6a:1116:19)
    at Array.filter (<anonymous>)
    at apply (chunk-BZD72IEI.js?v=2751eb6a:1124:27)
    at Proxy.filter (chunk-BZD72IEI.js?v=2751eb6a:1016:12)
    at ComputedRefImpl.fn (cashFlowStore.js:33:28)
    at refreshComputed (chunk-BZD72IEI.js?v=2751eb6a:659:29)
    at get value (chunk-BZD72IEI.js?v=2751eb6a:1861:5)
    at MutableReactiveHandler.get (chunk-BZD72IEI.js?v=2751eb6a:1221:68)
    at BankAccounts.vue:123:50
(anonymous) @ cashFlowStore.js:33
wrappedFn @ chunk-BZD72IEI.js?v=2751eb6a:1116
apply @ chunk-BZD72IEI.js?v=2751eb6a:1124
filter @ chunk-BZD72IEI.js?v=2751eb6a:1016
(anonymous) @ cashFlowStore.js:33
refreshComputed @ chunk-BZD72IEI.js?v=2751eb6a:659
get value @ chunk-BZD72IEI.js?v=2751eb6a:1861
get @ chunk-BZD72IEI.js?v=2751eb6a:1221
(anonymous) @ BankAccounts.vue:123
renderFnWithContext @ chunk-BZD72IEI.js?v=2751eb6a:2800
renderSlot @ chunk-BZD72IEI.js?v=2751eb6a:5168
_sfc_render @ DashboardLayout.vue:212
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
handleAccountCreated @ BankAccounts.vue:590
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
emit @ chunk-BZD72IEI.js?v=2751eb6a:8604
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:10323
handleSubmit @ AddBankAccountModal.vue:309
await in handleSubmit
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---

- Edit Bank Account Modal:
- it is not loading the description information even when typed and saved the description section contents are not staying consistent or being loaded. The browser console is showing the same errors as above. 

4. Purchase Orders:
4a. Create Purchase Order Modals:
- the Vendor * and Project drop down lists are empty, they are supposed to automatically fetch the vendors and projects available from the database. 
- the Add Line Items is not responding, no new line of items is being added. 
- when this modal is opened, the browser console shows a list of errors:
---
purchase-orders:84 [Vue warn]: Unhandled error during execution of render function 
  at <CreatePurchaseOrderModal isVisible=true onClose=fn onPoCreated=fn<handlePOCreated> > 
  at <DashboardLayout> 
  at <PurchaseOrders>
console.<computed> @ purchase-orders:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
loadProjects @ CreatePurchaseOrderModal.vue:424
await in loadProjects
(anonymous) @ CreatePurchaseOrderModal.vue:524
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
purchase-orders:84 [Vue warn]: Unhandled error during execution of component update 
  at <CreatePurchaseOrderModal isVisible=true onClose=fn onPoCreated=fn<handlePOCreated> > 
  at <DashboardLayout> 
  at <PurchaseOrders>
console.<computed> @ purchase-orders:84
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
loadProjects @ CreatePurchaseOrderModal.vue:424
await in loadProjects
(anonymous) @ CreatePurchaseOrderModal.vue:524
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
purchase-orders:84 [Vue warn]: Unhandled error during execution of render function 
  at <CreatePurchaseOrderModal isVisible=true onClose=fn onPoCreated=fn<handlePOCreated> > 
  at <DashboardLayout> 
  at <PurchaseOrders>
console.<computed> @ purchase-orders:84
warn$1 @ chunk-BZD72IEI.js?v=2751eb6a:2149
logError @ chunk-BZD72IEI.js?v=2751eb6a:2360
handleError @ chunk-BZD72IEI.js?v=2751eb6a:2352
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8756
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
(anonymous) @ CreatePurchaseOrderModal.vue:525
await in (anonymous)
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
purchase-orders:84 [Vue warn]: Unhandled error during execution of component update 
  at <CreatePurchaseOrderModal isVisible=true onClose=fn onPoCreated=fn<handlePOCreated> > 
  at <DashboardLayout> 
  at <PurchaseOrders>
console.<computed> @ purchase-orders:84
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
(anonymous) @ CreatePurchaseOrderModal.vue:525
await in (anonymous)
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
CreatePurchaseOrderModal.vue:123 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'id')
    at CreatePurchaseOrderModal.vue:123:51
    at renderList (chunk-BZD72IEI.js?v=2751eb6a:5118:18)
    at Proxy._sfc_render (CreatePurchaseOrderModal.vue:127:42)
    at renderComponentRoot (chunk-BZD72IEI.js?v=2751eb6a:8720:17)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=2751eb6a:7603:26)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=2751eb6a:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=2751eb6a:543:12)
    at callWithErrorHandling (chunk-BZD72IEI.js?v=2751eb6a:2296:33)
    at flushJobs (chunk-BZD72IEI.js?v=2751eb6a:2504:9)
(anonymous) @ CreatePurchaseOrderModal.vue:123
renderList @ chunk-BZD72IEI.js?v=2751eb6a:5118
_sfc_render @ CreatePurchaseOrderModal.vue:127
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
loadProjects @ CreatePurchaseOrderModal.vue:424
await in loadProjects
(anonymous) @ CreatePurchaseOrderModal.vue:524
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
CreatePurchaseOrderModal.vue:123 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'id')
    at CreatePurchaseOrderModal.vue:123:51
    at renderList (chunk-BZD72IEI.js?v=2751eb6a:5118:18)
    at Proxy._sfc_render (CreatePurchaseOrderModal.vue:127:42)
    at renderComponentRoot (chunk-BZD72IEI.js?v=2751eb6a:8720:17)
    at ReactiveEffect.componentUpdateFn [as fn] (chunk-BZD72IEI.js?v=2751eb6a:7603:26)
    at ReactiveEffect.run (chunk-BZD72IEI.js?v=2751eb6a:505:19)
    at ReactiveEffect.runIfDirty (chunk-BZD72IEI.js?v=2751eb6a:543:12)
    at callWithErrorHandling (chunk-BZD72IEI.js?v=2751eb6a:2296:33)
    at flushJobs (chunk-BZD72IEI.js?v=2751eb6a:2504:9)
(anonymous) @ CreatePurchaseOrderModal.vue:123
renderList @ chunk-BZD72IEI.js?v=2751eb6a:5118
_sfc_render @ CreatePurchaseOrderModal.vue:127
renderComponentRoot @ chunk-BZD72IEI.js?v=2751eb6a:8720
componentUpdateFn @ chunk-BZD72IEI.js?v=2751eb6a:7603
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
(anonymous) @ CreatePurchaseOrderModal.vue:525
await in (anonymous)
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
processFragment @ chunk-BZD72IEI.js?v=2751eb6a:7334
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
trigger @ chunk-BZD72IEI.js?v=2751eb6a:980
set @ chunk-BZD72IEI.js?v=2751eb6a:1268
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ PurchaseOrders.vue:15
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---


4b. Vendors (/purchase-orders/vendors):
- Add New Vendor modal: is adding successfully but there is an issue, it is not saving the  Vendor Name * 
- View Vendor Details: it is not showing the vendor details modal, it shows this error: "Error. Failed to load vendor details."
- Edit Vendor modal: it is not loading the  Vendor Name *, even after adding it, is successfully says updated but it never reloads again. 

4c. Receiving (/purchase-orders/receiving):
- on load it shows a blank black screen and these browser console log errors:
---
receiving:1 Uncaught TypeError: Failed to resolve module specifier "vue". Relative references must start with either "/", "./", or "../".
---

5. Donors:
- View Donor Details Modal: when the view donor details modal is opened it shows this error: "Not Found The requested resource was not found." and it also shows these browser console logs: 
---
CommunicationHistory.vue:253  GET http://127.0.0.1:8000/api/v1/api/v1/communications?communicable_type=App%5CModels%5CDonor&communicable_id=10&page=1 404 (Not Found)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
Axios.<computed> @ axios.js?v=2751eb6a:2346
wrap @ axios.js?v=2751eb6a:8
loadCommunications @ CommunicationHistory.vue:253
(anonymous) @ CommunicationHistory.vue:247
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
viewDonor @ DonorsList.vue:729
await in viewDonor
onClick @ DonorsList.vue:421
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
donors:84 Error loading communications: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ donors:84
loadCommunications @ CommunicationHistory.vue:269
await in loadCommunications
(anonymous) @ CommunicationHistory.vue:247
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
viewDonor @ DonorsList.vue:729
await in viewDonor
onClick @ DonorsList.vue:421
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---

5b. - View Donor Details Modal: Communications Tab: 
- when user enters all information and press the "log Communication" button they get this error: "Not Found The requested resource was not found." and these browser console errors:
---
AddCommunicationForm.vue:257  POST http://127.0.0.1:8000/api/v1/api/v1/communications 404 (Not Found)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
handleSubmit @ AddCommunicationForm.vue:257
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
donors:84 Error saving communication: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ donors:84
handleSubmit @ AddCommunicationForm.vue:270
await in handleSubmit
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---


6.

==============================================
# Project Officer Role Issues/Bugs:
1. The submit expense button on the dashboard leads to "/expenses/create" but the shows a blank black page. the browser console shows these errors:
---
create:1 Uncaught TypeError: Failed to resolve module specifier "vue". Relative references must start with either "/", "./", or "../".
---

2. Projects (/projects):
- the project officer must only see the projects that are assigned to them not all projects.
-  the donors drop down search filter is empty, its supposed to dynamically fetch all the linked donors to the assigned projects. 
- on projects load it shows this error message: "Access Denied. You do not have permission to perform this action."
-  the browser console is showing these error messages:
---
projectStore.js:308  GET http://127.0.0.1:8000/api/v1/donors 403 (Forbidden)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
Axios.<computed> @ axios.js?v=2751eb6a:2346
wrap @ axios.js?v=2751eb6a:8
fetchDonors @ projectStore.js:308
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
(anonymous) @ ProjectsList.vue:676
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=2751eb6a:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2481
render2 @ chunk-BZD72IEI.js?v=2751eb6a:8211
mount @ chunk-BZD72IEI.js?v=2751eb6a:6122
app.mount @ chunk-BZD72IEI.js?v=2751eb6a:12437
(anonymous) @ bootstrap-projects.js:24
projects:84 Error fetching donors: AxiosError {message: 'Request failed with status code 403', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchDonors @ projectStore.js:313
await in fetchDonors
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
(anonymous) @ ProjectsList.vue:676
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=2751eb6a:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=2751eb6a:2481
render2 @ chunk-BZD72IEI.js?v=2751eb6a:8211
mount @ chunk-BZD72IEI.js?v=2751eb6a:6122
app.mount @ chunk-BZD72IEI.js?v=2751eb6a:12437
(anonymous) @ bootstrap-projects.js:24
---


- when they open the project detail view modal, they get this error: "Not Found. The requested resource was not found." and this error: "Error. Failed to load comments. Please refresh the page." The browser console shows these errors:
---
CommentsList.vue:159  GET http://127.0.0.1:8000/api/v1/api/v1/comments?commentable_type=Project&commentable_id=1&page=1 404 (Not Found)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
Axios.<computed> @ axios.js?v=2751eb6a:2346
wrap @ axios.js?v=2751eb6a:8
fetchComments @ CommentsList.vue:159
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
projects:84 Failed to fetch comments: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchComments @ CommentsList.vue:177
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
---

-  remove access to "edit/ the edit button" a project from the Projects Officer, they can only view the project details and must not be able to edit any information about that project. 
- they are allowed to make comments on the project view. 
- when the user open comments/makes a comment view they get this error: "Not Found. The requested resource was not found." and also get these browser console errors:
---
CommentBox.vue:242  GET http://127.0.0.1:8000/api/v1/api/v1/users/search?per_page=100 404 (Not Found)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
Axios.<computed> @ axios.js?v=2751eb6a:2346
wrap @ axios.js?v=2751eb6a:8
fetchUsers @ CommentBox.vue:242
(anonymous) @ CommentBox.vue:434
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
set @ chunk-BZD72IEI.js?v=2751eb6a:1744
_createElementBlock.onClick._cache.<computed>._cache.<computed> @ CommentsSection.vue:28
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
projects:84 Failed to fetch users: AxiosError {message: 'Request failed with status code 404', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchUsers @ CommentBox.vue:247
await in fetchUsers
(anonymous) @ CommentBox.vue:434
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
set @ chunk-BZD72IEI.js?v=2751eb6a:1744
_createElementBlock.onClick._cache.<computed>._cache.<computed> @ CommentsSection.vue:28
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---

- when user tries to post a comment they get this error: "Error. Failed to post comment. Please try again." and the browser console shows:
---
CommentBox.vue:382  POST http://127.0.0.1:8000/api/v1/api/v1/comments 404 (Not Found)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
submitComment @ CommentBox.vue:382
(anonymous) @ chunk-BZD72IEI.js?v=2751eb6a:12376
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
---

- Generate Report pdf icon shows these errors: "Server Error. An unexpected error occurred. Please try again later." and this: "Report Generation Failed. Request failed with status code 500" and this browser console error:
---
projectStore.js:273  POST http://127.0.0.1:8000/api/v1/projects/1/report 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=2751eb6a:1696
xhr @ axios.js?v=2751eb6a:1573
dispatchRequest @ axios.js?v=2751eb6a:2107
Promise.then
_request @ axios.js?v=2751eb6a:2310
request @ axios.js?v=2751eb6a:2219
httpMethod @ axios.js?v=2751eb6a:2356
wrap @ axios.js?v=2751eb6a:8
generateReport @ projectStore.js:273
wrappedAction @ pinia.js?v=2751eb6a:5508
store.<computed> @ pinia.js?v=2751eb6a:5205
handleGenerateReport @ ProjectsList.vue:603
onClick @ ProjectsList.vue:352
callWithErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=2751eb6a:2303
invoker @ chunk-BZD72IEI.js?v=2751eb6a:11358
----

3. Documents Module (/dashboard/documents): Upload Document Modal:
- remove attach to, and all the corresponding attache to drop down items. we want to simplify the file upload as simple as possible. documents will be arranged and grouped according to the categories which is good enough. 

4. Expenses (/expenses) module:
- when program officer opens this module they get these browser console errors:
---
expenses:84 [Vue Router warn]: No match found for location with path "/expenses"
console.<computed> @ expenses:84
warn$1 @ vue-router.js?v=2751eb6a:207
resolve @ vue-router.js?v=2751eb6a:2033
pushWithRedirect @ vue-router.js?v=2751eb6a:2117
push @ vue-router.js?v=2751eb6a:2089
install @ vue-router.js?v=2751eb6a:2321
use @ chunk-BZD72IEI.js?v=2751eb6a:6046
(anonymous) @ bootstrap-expenses.js:49
---

- when they try to create a new expense using create expense buttons, no action nor any modal is displayed. show the add expense modal view

5.
6.



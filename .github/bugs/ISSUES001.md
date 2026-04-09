ISSUES FOUND:

#Dashboards:
- fix the info widgets cards to fetch real time data these are
- all dashboard sections must have real data fetched dynamically from the database
---

#Projects (/projects):
- its showing this error on page load: "Unauthenticated." this happens for all users
- these are the browser console logs being shown:
[projects:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
projectStore.js:90  GET http://127.0.0.1:8000/api/v1/projects?page=1&per_page=25 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchProjects @ projectStore.js:90
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadProjects @ ProjectsList.vue:529
(anonymous) @ ProjectsList.vue:674
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-projects.js:18
projects:84 Error fetching projects: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchProjects @ projectStore.js:106
await in fetchProjects
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadProjects @ ProjectsList.vue:529
(anonymous) @ ProjectsList.vue:674
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-projects.js:18
projectStore.js:316  GET http://127.0.0.1:8000/api/v1/donors 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchDonors @ projectStore.js:316
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ ProjectsList.vue:675
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-projects.js:18
projects:84 Error fetching donors: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ projects:84
fetchDonors @ projectStore.js:321
await in fetchDonors
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ ProjectsList.vue:675
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-projects.js:18
]

---

#Budgets (/budgets):
on module load there are these errors being displayed by the browser console:
[budgets:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
budgetStore.js:149  GET http://127.0.0.1:8000/api/v1/budgets?page=1&per_page=25 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchBudgets @ budgetStore.js:149
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadBudgets @ BudgetsList.vue:259
(anonymous) @ BudgetsList.vue:326
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
budgets:84 Error fetching budgets: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ budgets:84
fetchBudgets @ budgetStore.js:163
await in fetchBudgets
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadBudgets @ BudgetsList.vue:259
(anonymous) @ BudgetsList.vue:326
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
projectStore.js:90  GET http://127.0.0.1:8000/api/v1/projects?page=1&per_page=25 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchProjects @ projectStore.js:90
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ BudgetsList.vue:327
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
budgets:84 Error fetching projects: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ budgets:84
fetchProjects @ projectStore.js:106
await in fetchProjects
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ BudgetsList.vue:327
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
projectStore.js:316  GET http://127.0.0.1:8000/api/v1/donors 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchDonors @ projectStore.js:316
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ BudgetsList.vue:328
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
budgets:84 Error fetching donors: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ budgets:84
fetchDonors @ projectStore.js:321
await in fetchDonors
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
(anonymous) @ BudgetsList.vue:328
await in (anonymous)
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-budgets.js:18
]
---

#Expenses (/expenses):
- module load it shows this error message for all users: "Unauthenticated." and the browser console shows the following errors:
[expenses:25 🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs
expenses:84 [Vue warn]: injection "Symbol(router)" not found. 
  at <ExpensesList> 
  at <DashboardLayout> 
  at <Expenses>
console.<computed> @ expenses:84
warn$1 @ chunk-BZD72IEI.js?v=abe40f3d:2149
inject @ chunk-BZD72IEI.js?v=abe40f3d:6216
useRouter @ vue-router.js?v=abe40f3d:2356
setup @ ExpensesList.vue:348
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
setupStatefulComponent @ chunk-BZD72IEI.js?v=abe40f3d:10131
setupComponent @ chunk-BZD72IEI.js?v=abe40f3d:10092
mountComponent @ chunk-BZD72IEI.js?v=abe40f3d:7420
processComponent @ chunk-BZD72IEI.js?v=abe40f3d:7386
patch @ chunk-BZD72IEI.js?v=abe40f3d:6890
mountChildren @ chunk-BZD72IEI.js?v=abe40f3d:7134
processFragment @ chunk-BZD72IEI.js?v=abe40f3d:7316
patch @ chunk-BZD72IEI.js?v=abe40f3d:6864
mountChildren @ chunk-BZD72IEI.js?v=abe40f3d:7134
mountElement @ chunk-BZD72IEI.js?v=abe40f3d:7057
processElement @ chunk-BZD72IEI.js?v=abe40f3d:7012
patch @ chunk-BZD72IEI.js?v=abe40f3d:6878
mountChildren @ chunk-BZD72IEI.js?v=abe40f3d:7134
mountElement @ chunk-BZD72IEI.js?v=abe40f3d:7057
processElement @ chunk-BZD72IEI.js?v=abe40f3d:7012
patch @ chunk-BZD72IEI.js?v=abe40f3d:6878
mountChildren @ chunk-BZD72IEI.js?v=abe40f3d:7134
mountElement @ chunk-BZD72IEI.js?v=abe40f3d:7057
processElement @ chunk-BZD72IEI.js?v=abe40f3d:7012
patch @ chunk-BZD72IEI.js?v=abe40f3d:6878
componentUpdateFn @ chunk-BZD72IEI.js?v=abe40f3d:7532
run @ chunk-BZD72IEI.js?v=abe40f3d:505
setupRenderEffect @ chunk-BZD72IEI.js?v=abe40f3d:7660
mountComponent @ chunk-BZD72IEI.js?v=abe40f3d:7434
processComponent @ chunk-BZD72IEI.js?v=abe40f3d:7386
patch @ chunk-BZD72IEI.js?v=abe40f3d:6890
componentUpdateFn @ chunk-BZD72IEI.js?v=abe40f3d:7532
run @ chunk-BZD72IEI.js?v=abe40f3d:505
setupRenderEffect @ chunk-BZD72IEI.js?v=abe40f3d:7660
mountComponent @ chunk-BZD72IEI.js?v=abe40f3d:7434
processComponent @ chunk-BZD72IEI.js?v=abe40f3d:7386
patch @ chunk-BZD72IEI.js?v=abe40f3d:6890
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8197
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
expenseStore.js:95  GET http://127.0.0.1:8000/api/v1/expenses?page=1&per_page=15&search= 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchExpenses @ expenseStore.js:95
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadData @ ExpensesList.vue:446
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
expenses:84 Error loading data: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ expenses:84
loadData @ ExpensesList.vue:451
await in loadData
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
expenseStore.js:117  GET http://127.0.0.1:8000/api/v1/expenses/categories 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchCategories @ expenseStore.js:117
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadData @ ExpensesList.vue:447
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
expenses:84 Error fetching categories: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ expenses:84
fetchCategories @ expenseStore.js:120
await in fetchCategories
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadData @ ExpensesList.vue:447
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
projectStore.js:90  GET http://127.0.0.1:8000/api/v1/projects?page=1&per_page=25 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=abe40f3d:1696
xhr @ axios.js?v=abe40f3d:1573
dispatchRequest @ axios.js?v=abe40f3d:2107
_request @ axios.js?v=abe40f3d:2327
request @ axios.js?v=abe40f3d:2219
Axios.<computed> @ axios.js?v=abe40f3d:2346
wrap @ axios.js?v=abe40f3d:8
fetchProjects @ projectStore.js:90
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadProjects @ ExpensesList.vue:457
loadData @ ExpensesList.vue:448
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
expenses:84 Error fetching projects: AxiosError {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
console.<computed> @ expenses:84
fetchProjects @ projectStore.js:106
await in fetchProjects
wrappedAction @ pinia.js?v=abe40f3d:5508
store.<computed> @ pinia.js?v=abe40f3d:5205
loadProjects @ ExpensesList.vue:457
loadData @ ExpensesList.vue:448
(anonymous) @ ExpensesList.vue:544
(anonymous) @ chunk-BZD72IEI.js?v=abe40f3d:5003
callWithErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2296
callWithAsyncErrorHandling @ chunk-BZD72IEI.js?v=abe40f3d:2303
hook.__weh.hook.__weh @ chunk-BZD72IEI.js?v=abe40f3d:4983
flushPostFlushCbs @ chunk-BZD72IEI.js?v=abe40f3d:2481
render2 @ chunk-BZD72IEI.js?v=abe40f3d:8211
mount @ chunk-BZD72IEI.js?v=abe40f3d:6122
app.mount @ chunk-BZD72IEI.js?v=abe40f3d:12437
(anonymous) @ bootstrap-expenses.js:18
] 
- the "New Expense Button" is not not responding it is showing this errors on the browser console:
(Uncaught TypeError: can't access property "push", router is undefined
    goToCreateExpense ExpensesList.vue:485
    callWithErrorHandling runtime-core.esm-bundler.js:199
    callWithAsyncErrorHandling runtime-core.esm-bundler.js:206
    invoker runtime-dom.esm-bundler.js:730
    addEventListener runtime-dom.esm-bundler.js:681
    patchEvent runtime-dom.esm-bundler.js:699
    patchProp runtime-dom.esm-bundler.js:776
    mountElement runtime-core.esm-bundler.js:4957
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    mountChildren runtime-core.esm-bundler.js:5016
    mountElement runtime-core.esm-bundler.js:4939
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    mountChildren runtime-core.esm-bundler.js:5016
    mountElement runtime-core.esm-bundler.js:4939
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    componentUpdateFn runtime-core.esm-bundler.js:5412
    run reactivity.esm-bundler.js:237
    setupRenderEffect runtime-core.esm-bundler.js:5540
    mountComponent runtime-core.esm-bundler.js:5314
    processComponent runtime-core.esm-bundler.js:5266
    patch runtime-core.esm-bundler.js:4772
    mountChildren runtime-core.esm-bundler.js:5016
    processFragment runtime-core.esm-bundler.js:5196
    patch runtime-core.esm-bundler.js:4746
    mountChildren runtime-core.esm-bundler.js:5016
    mountElement runtime-core.esm-bundler.js:4939
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    mountChildren runtime-core.esm-bundler.js:5016
    mountElement runtime-core.esm-bundler.js:4939
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    mountChildren runtime-core.esm-bundler.js:5016
    mountElement runtime-core.esm-bundler.js:4939
    processElement runtime-core.esm-bundler.js:4894
    patch runtime-core.esm-bundler.js:4760
    componentUpdateFn runtime-core.esm-bundler.js:5412
    run reactivity.esm-bundler.js:237
    setupRenderEffect runtime-core.esm-bundler.js:5540
    mountComponent runtime-core.esm-bundler.js:5314
    processComponent runtime-core.esm-bundler.js:5266
    patch runtime-core.esm-bundler.js:4772
    componentUpdateFn runtime-core.esm-bundler.js:5412
    run reactivity.esm-bundler.js:237
    setupRenderEffect runtime-core.esm-bundler.js:5540
    mountComponent runtime-core.esm-bundler.js:5314
    processComponent runtime-core.esm-bundler.js:5266
    patch runtime-core.esm-bundler.js:4772
    render2 runtime-core.esm-bundler.js:6077
    mount runtime-core.esm-bundler.js:4009
    mount runtime-dom.esm-bundler.js:1826
    <anonymous> bootstrap-expenses.js:18
ExpensesList.vue:485:9
)

---

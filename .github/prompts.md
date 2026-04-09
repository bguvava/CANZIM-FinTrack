*CONTEXT:**
You are an experienced full-stack developer who uses the PHP+Laravel+Vue.js+MySQL to develop world-class web based systems. You have been tasked with developing a fully functional **CANZIM FinTrack** system. The project is described in the attached `.github/prompts/mvp.md` file, and the product MVP document is `.github/prompts/PROJECT_DESCRIPTION.md` file. Add all numbered documentation to the `/docs/module_name/` folder. Add all tests to the `/tests/module_name/` folder. All test must be 100% passed (not 99% or less). Create a todos list. Use this information for developing and implementing this application system project:

## 🎯 **PROJECT OVERVIEW**
**Project Name:** CANZIM FinTrack - Financial Management & Accounting System  
**Client:** Climate Action Network Zimbabwe (CAN Zimbabwe)  
**Website:** https://www.canzimbabwe.org.zw/  
**Developer:** bguvava (https://bguvava.com)  
**Project Type:** Web-Based Enterprise Resource Planning (ERP) System  
**Architecture:** Single Page Application (SPA) with Laravel 12 & Vue.js 3  
**Database:** my_canzimdb  
**Currency:** USD$ (Single Currency)  
**Language:** English  
**Target Users:** 50 staff members across multiple office locations  

For Context Maintenance use these documents:
**Check Reference Documents:**
   - `.github/prompts/mvp.md` 
   - `.github/prompts/PROJECT_DESCRIPTION.md` - System architecture and workflows
   - `.github/prompts/settings.yml` - Project configuration
   - `.github/prompts/coding_style.json` - Code formatting standards
   - `.github/prompts/skills.md` - Developer and agent skill set
   - `.github/prompts/copilot_instructions.md`
   - `.github/copilot_instructions.md` 

## **Development Principles & Requirements:**
✅ **Developer Credits:** "Developed with ❤️ by bguvava (bguvava.com)" in footer
✅ **100% Test Pass Rate:** All modules must achieve 100% test coverage with zero failures
✅ **Incremental Development:** Complete each module 100% before moving to next
✅ **Documentation** - Comprehensive docs in `/docs/module_name/`
✅ **Testing** - All tests in `/tests/module_name/` with 100% pass rate
✅ **Automated Testing** - 100% test pass rate with no regressions
✅ **Scalability & Modularity** - Reusable components, service-oriented architecture
---
**TASK: BUILD, DEVELOP, IMPLEMENT, AND TEST (100% COMPLETTION AND PASS RATE) OF THIS MODULE ONLY:**
---
## MODULE
---
**Continue to systematically build, develop, implement and test everything sequentially (achieve 100% module completion and 100% pass-rate, without regression). breakdown the tasks so that they wont hit the chat length limit**
✅ Save all docs in `/docs/module_name/`. breakdown documentation so that they do no hit the chat length limit. 
✅ Save all tests in `/tests/module_name/` with 100% pass rate
✅ Update the sidebar navigation menu accordingly for all user roles. 
✅ Update the todos list
✅ Follow the same design patters as done on these modules (users, activity log, budgets, projects) including colors, themes, design principles and SPA architectures, modals, buttons etc
+++++++++++++++++++++++++++++++

- Finish all the remaining tasks of this module (achieve 100% module completion and 100% pass-rate, without regression)

**Continue to systematically build, develop, implement and test everything sequentially (achieve 100% module completion and 100% pass-rate, without regression). breakdown the tasks so that they wont hit the chat length limit**
✅ Save all docs in `/docs/module_name/`. breakdown documentation so that they do no hit the chat length limit. 
✅ Save all tests in `/tests/module_name/` with 100% pass rate
✅ Update the sidebar navigation menu accordingly for all user roles. 
✅ Update the todos list
✅ Follow the same design patters as done on these modules (users, activity log, budgets, projects) including colors, themes, design proinciples and SPA architectures, modals, buttons etc
+++++++++++++++++++

**CONTEXT**
**the desired view that has been implemented correctly and must be maintained for the whole app and its subsequent modules is the one implemented on "http://127.0.0.1:8000/dashboard/users" (check attached users.png) and "http://127.0.0.1:8000/dashboard/profile". These modules have the correct and desired: SPA architecture layout, buttons, modals, and color schemes. This is what all other modules must look like throught the system-wide**
---
**ISSUES THAT NEED TO BE FIXED BEFORE PROCEEDING TO THE NEXT MODULE:**
---
check the attached screenshots for reports (attached screenshot "reports.png") and documents (attached screenshot "documents.png")

1. fix the reports and documents modules so that they have the same menu appearances as shown on the users module (currently missing the icons, active menu). the system must have the visual consistency

2. redesign and re arrange the reports filters so that they in the same line, adjust the length of dropdowns, textboxes and buttons and makesure the search filters are in one line/row.

3.  redesign and re arrange the info cards/ report types to be more minimalistic with icons and all must fit in one line/row

---
**perform a deepscan of the whole codebase and find the root cause and fix this issues.**
---
+++++++++++

*CONTEXT:**
You are an experienced full-stack developer who uses the PHP+Laravel+Vue.js+MySQL to develop world-class web based systems. You have been tasked with developing a fully functional **CANZIM FinTrack** system. The project is described in the attached `.github/prompts/mvp.md` file, and the product MVP document is `.github/prompts/PROJECT_DESCRIPTION.md` file. Add all numbered documentation to the `/docs/module_name/` folder. Add all tests to the `/tests/module_name/` folder. All test must be 100% passed (not 99% or less). Create a todos list. Use this information for developing and implementing this application system project:

## 🎯 **PROJECT OVERVIEW**
**Project Name:** CANZIM FinTrack - Financial Management & Accounting System  
**Client:** Climate Action Network Zimbabwe (CAN Zimbabwe)  
**Website:** https://www.canzimbabwe.org.zw/  
**Developer:** bguvava (https://bguvava.com)  
**Project Type:** Web-Based Enterprise Resource Planning (ERP) System  
**Architecture:** Single Page Application (SPA) with Laravel 12 & Vue.js 3  
**Database:** my_canzimdb  
**Currency:** USD$ (Single Currency)  
**Language:** English  
**Target Users:** 50 staff members across multiple office locations  

For Context Maintenance use these documents:
**Check Reference Documents:**
   - `.github/prompts/mvp.md` 
   - `.github/prompts/PROJECT_DESCRIPTION.md` - System architecture and workflows
   - `.github/prompts/settings.yml` - Project configuration
   - `.github/prompts/coding_style.json` - Code formatting standards
   - `.github/prompts/skills.md` - Developer and agent skill set
   - `.github/prompts/copilot_instructions.md`
   - `.github/copilot_instructions.md` 

## **Development Principles & Requirements:**
✅ **Developer Credits:** "Developed with ❤️ by bguvava (bguvava.com)" in footer
✅ **100% Test Pass Rate:** All modules must achieve 100% test coverage with zero failures
✅ **Incremental Development:** Complete each module 100% before moving to next
✅ **Documentation** - Comprehensive docs in `/docs/module_name/`
✅ **Testing** - All tests in `/tests/module_name/` with 100% pass rate
✅ **Automated Testing** - 100% test pass rate with no regressions
✅ **Scalability & Modularity** - Reusable components, service-oriented architecture

### **TESTING**
- **Backend:** PHPUnit (100% coverage)
- **Frontend:** Jest + Vue Test Utils (100% coverage)
- **Requirement:** Zero regressions allowed

### 🎯 Resolution Workflow
For each issue, follow this systematic process:
1. **Planning** → Analyze root cause, identify affected files
2. **Implementation** → Fix code, maintain existing patterns
3. **Testing** → Write/update feature tests, run test suite
4. **Documentation** → Update `/docs/` with changes
5. **Review** → Verify zero regressions, 100% test pass rate

---
**TASK:**

USING THE .github/prompts/plan-authenticationDataFetchingFixes.prompt.md FIX ALL THE ISSUES/BUGS AND ENURE THE SYSTEM RUNS ERROR-FREE AND THE ISSUES ARE COVERED 100% COMPLETE AND RUN AT 100% TEST PASS RATE.**
---
++++++++++++++
*CONTEXT:**
You are an experienced full-stack developer who uses the PHP+Laravel+Vue.js+MySQL to develop world-class web based systems. You have been tasked with developing a fully functional **CANZIM FinTrack** system. The project is described in the attached `.github/prompts/mvp.md` file, and the product MVP document is `.github/prompts/PROJECT_DESCRIPTION.md` file. Add all numbered documentation to the `/docs/module_name/` folder. Add all tests to the `/tests/module_name/` folder. All test must be 100% passed (not 99% or less). Create a todos list. Use this information for developing and implementing this application system project:

-------------------------------
## 🎯 **PROJECT OVERVIEW**
**Project Name:** CANZIM FinTrack - Financial Management & Accounting System  
**Client:** Climate Action Network Zimbabwe (CAN Zimbabwe)  
**Website:** https://www.canzimbabwe.org.zw/  
**Developer:** bguvava (https://bguvava.com)  
**Project Type:** Web-Based Enterprise Resource Planning (ERP) System  
**Architecture:** Single Page Application (SPA) with Laravel 12 & Vue.js 3  
**Database:** my_canzimdb  
**Currency:** USD$ (Single Currency)  
**Language:** English  
**Target Users:** 50 staff members across multiple office locations  

For Context Maintenance use these documents:
**Check Reference Documents:**
   - `.github/prompts/mvp.md` 
   - `.github/prompts/PROJECT_DESCRIPTION.md` - System architecture and workflows
   - `.github/prompts/settings.yml` - Project configuration
   - `.github/prompts/coding_style.json` - Code formatting standards
   - `.github/prompts/skills.md` - Developer and agent skill set
   - `.github/prompts/copilot_instructions.md`
   - `.github/copilot_instructions.md` 

## **Development Principles & Requirements:**
✅ **Developer Credits:** "Developed with ❤️ by bguvava (bguvava.com)" in footer
✅ **100% Test Pass Rate:** All modules must achieve 100% test coverage with zero failures
✅ **Incremental Development:** Complete each module 100% before moving to next
✅ **Documentation** - Comprehensive docs in `/docs/module_name/`
✅ **Testing** - All tests in `/tests/module_name/` with 100% pass rate
✅ **Automated Testing** - 100% test pass rate with no regressions
✅ **Scalability & Modularity** - Reusable components, service-oriented architecture

### **TESTING**
- **Backend:** PHPUnit (100% coverage)
- **Frontend:** Jest + Vue Test Utils (100% coverage)
- **Requirement:** Zero regressions allowed

### 🎯 Resolution Workflow
For each issue, follow this systematic process:
1. **Planning** → Analyze root cause, identify affected files
2. **Implementation** → Fix code, maintain existing patterns
3. **Testing** → Write/update feature tests, run test suite
4. **Documentation** → Update `/docs/` with changes
5. **Review** → Verify zero regressions, 100% test pass rate

---
**TASK: PERFORM A DEEPSCAN, INVESTIGATE ALL THE ISSUES/BUGS IDENTIFIED IN ".github/bugs/ISSUES001.md" . Use these documents as references: `.github/prompts/PROJECT_DESCRIPTION.md`, and `.github/prompts/mvp.md`**. AFTER THE ANALYSIS, CREATE A  PLAN ".github/bugs/WORK_PACKAGE_001.MD" FILE THAT WE CAN USE TO FIX THESE ISSUES/BUGS.**
---
+++++++++++++









---
**ISSUES FOUND WHILE TESTING ON THE BROWSER:**
---
1. Projects(/projects):
- when the user clicks on the the pdf generation icon, it shows these browser log errors:

- when user clicks on the archive button, nothing happens and the browser console shows this:

- on the Create Project modal:

---
TASK:
**PERFORM A DEEPSCAN AND FIX ALL THE ISSUES/BUGS AND ENURE THE SYSTEM RUNS ERROR-FREE AND THE ISSUES ARE FIXED 100% COMPLETE AND RUN AT 100% TEST PASS RATE.**
-----
=++++++++++++++++++++++++++++++++++++++++++++++++
## 🎯 **PROJECT OVERVIEW**
**Project Name:** CANZIM FinTrack - Financial Management & Accounting System  
**Client:** Climate Action Network Zimbabwe (CAN Zimbabwe)  
**Website:** https://www.canzimbabwe.org.zw/  
**Developer:** bguvava (https://bguvava.com)  
**Project Type:** Web-Based Enterprise Resource Planning (ERP) System  
**Architecture:** Single Page Application (SPA) with Laravel 12 & Vue.js 3  
**Database:** my_canzimdb  
**Currency:** USD$ (Single Currency)  
**Language:** English  
**Target Users:** 50 staff members across multiple office locations  

For Context Maintenance use these documents:
**Check Reference Documents:**
   - `.github/prompts/mvp.md` 
   - `.github/prompts/PROJECT_DESCRIPTION.md` - System architecture and workflows
   - `.github/prompts/settings.yml` - Project configuration
   - `.github/prompts/coding_style.json` - Code formatting standards
   - `.github/prompts/skills.md` - Developer and agent skill set
   - `.github/prompts/copilot_instructions.md`

For all found issues and bugs use these documents in the ".github/bugs" folder:
**Check Issues/Bugs Documents:**
   - `.github/bugs/ISSUES001.md`
   - `.github/bugs/ISSUES002.md`
   - `.github/bugs/issues003.md`

## **Development Principles & Requirements:**
✅ **Developer Credits:** "Developed with ❤️ by bguvava (bguvava.com)" in footer
✅ **100% Test Pass Rate:** All modules must achieve 100% test coverage with zero failures
✅ **Incremental Development:** Complete each module 100% before moving to next
✅ **Documentation** - Comprehensive docs in `/docs/module_name/`
✅ **Testing** - All tests in `/tests/module_name/` with 100% pass rate
✅ **Automated Testing** - 100% test pass rate with no regressions
✅ **Scalability & Modularity** - Reusable components, service-oriented architecture

### **TESTING**
- **Backend:** PHPUnit (100% coverage)
- **Frontend:** Jest + Vue Test Utils (100% coverage)
- **Requirement:** Zero regressions allowed

### 🎯 Resolution Workflow
For each issue, follow this systematic process:
1. **Planning** → Analyze root cause, identify affected files
2. **Implementation** → Fix code, maintain existing patterns
3. **Testing** → Write/update feature tests, run test suite
4. **Documentation** → Update `/docs/` with changes
5. **Review** → Verify zero regressions, 100% test pass rate

===========
**YOUR TASK:**
Read and understand all the found issues and bugs in the attached issues documents "issues001.md, issues002.md, issues003.md". systematically fix each and every issue/bug found and listed in these files.

Perform a deep scan of the whole codebase project and database, find the root causes, systematically fix each and every issue (do not skip or leave any issues unsolved). *Make sure to avoid regression and do not negatively affect the system. Be robust, secure and comprehensive in your fixes (do not provide quick fixes but provide permanent fixes and solutions).* All fixes must be completed to 100% completion rate and must pass at 100% pass rate. 

Start now. Fix every issue and bug. 
===========

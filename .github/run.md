# Terminal 1: Laravel backend
cd C:\xampp\htdocs\CANZIM
$env:Path = "C:\xampp\php;" + $env:Path
php artisan serve

# Terminal 2: Vite frontend
cd C:\xampp\htdocs\CANZIM
npm run dev
---
Programs Manager: programs@canzim.test / programs123
Finance Officer: finance@canzim.test / finance123
Project Officer: project@canzim.test / project123
Data Entry Clerk: dataentry@canzim.test / dataentry123
Admin: admin@canzim.org.zw / password123

+++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++
# 1. Create GitHub repository named "CANZIM-FinTrack" (private)
# 2. Then run these commands:

cd C:\xampp\htdocs\CANZIM
git remote add origin https://github.com/bguvava/CANZIM-FinTrack.git
git push -u origin main
---
…or create a new repository on the command line
echo "# CANZIM-FinTrack" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/bguvava/CANZIM-FinTrack.git
git push -u origin main
---
…or push an existing repository from the command line
git remote add origin https://github.com/bguvava/CANZIM-FinTrack.git
git branch -M main
git push -u origin main

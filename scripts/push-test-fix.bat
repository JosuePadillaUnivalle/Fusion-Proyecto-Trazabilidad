@echo off
cd /d "%~dp0.."
set GIT_AUTHOR_NAME=Josue Padilla
set GIT_AUTHOR_EMAIL=JosuePadillaUnivalle@users.noreply.github.com
set GIT_COMMITTER_NAME=Josue Padilla
set GIT_COMMITTER_EMAIL=JosuePadillaUnivalle@users.noreply.github.com
git add tests/Feature/AlmacenDashboardScopeTest.php
for /f %%i in ('git write-tree') do set TREE=%%i
for /f %%i in ('git commit-tree %TREE% -p c10e9f7 -m "fix(tests): alinear dashboard agricola con vista inicio" -m "Actualiza AlmacenDashboardScopeTest tras el cambio de dashboard.roles a dashboard.inicio para el rol agricultor."') do set NEW=%%i
git reset --hard %NEW%
git push --force-with-lease origin main

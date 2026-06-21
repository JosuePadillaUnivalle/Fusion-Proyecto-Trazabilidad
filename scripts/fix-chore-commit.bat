@echo off
cd /d "%~dp0.."
set GIT_AUTHOR_NAME=Josue Padilla
set GIT_AUTHOR_EMAIL=JosuePadillaUnivalle@users.noreply.github.com
set GIT_COMMITTER_NAME=Josue Padilla
set GIT_COMMITTER_EMAIL=JosuePadillaUnivalle@users.noreply.github.com
for /f %%i in ('git write-tree') do set TREE=%%i
for /f %%i in ('git commit-tree %TREE% -p c10e9f7 -m "chore: regla de commits sin coautores" -m "Evita que Cursor agregue cursoragent en futuros commits de Fusion-Proyectos."') do set NEW=%%i
git reset --hard %NEW%
git cat-file -p HEAD

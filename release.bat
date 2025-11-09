@echo off
set VERSION=6.0
git tag %VERSION%
git push origin %VERSION%
gh release create v%VERSION% --title "Mobo Core %VERSION%" --notes "%VERSION%"
pause
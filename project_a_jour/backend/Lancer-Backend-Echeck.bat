@echo off
REM Script automatique robuste pour lancer le backend et afficher toutes les IP valides

REM -- Affiche toutes les adresses IPv4 trouvées --
echo ###############
echo Adresses IPv4 detectees :
for /f "tokens=2 delims=: " %%a in ('ipconfig ^| findstr /C:"IPv4"') do echo   %%a

echo.
echo Choisis l'adresse IPv4 de ta carte Wi-Fi (ex: 192.168.88.xx) et copie-la dans lib/env.dart sur le mobile !
echo ###############
echo.

REM -- Vérifie l'existence du dossier public --
if not exist public (
  echo ERREUR : Le dossier ^"public^" est introuvable dans ce dossier !
  echo Va dans le dossier backend qui contient le dossier public puis relance ce script.
  pause
  exit /b
)

REM -- Demande à l'utilisateur d'entrer l'IP à ouvrir dans le navigateur --
set /p IPCHOIX="Entrez l'adresse IPv4 à ouvrir dans le navigateur (ou laisse vide pour ignorer) : "
if not "%IPCHOIX%"=="" start "" http://%IPCHOIX%:8000

php -S 0.0.0.0:8000 -t public
pause

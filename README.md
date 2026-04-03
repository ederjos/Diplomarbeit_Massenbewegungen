# Diplomarbeit_Massenbewegungen
Diplomarbeit als Web-Plattform zur Visualisierung von Massenbewegungen

## Installation

Auf Linux or via WSL:
1. Repo clonen `git clone https://github.com/ederjos/Diplomarbeit_Massenbewegungen.git`.
2. In das neue Verzeichnis wechseln `cd Diplomarbeit_Massenbewegungen`.
3. Docker starten.
4. Bash-Skript `setup.sh` ausführen. Dies kann einige Zeit in Anspruch nehmen.

Zum Starten: `./start.sh` ausführen  
Zum Stoppen: `./start.sh` stoppen sowie `./stop.sh` ausführen

## Nach dem Pullen

Manchmal wird bei Commits die Datenbank bearbeitet. Danach einfach diesen Befehl ausführen:  
`./vendor/bin/sail artisan migrate:fresh --seed`

## TBD/TODO

### Technical Debt

* **Bezugsepoche GUI**: Admin/Editor soll Bezugsepoche pro Projekt setzen können (Settings-Seite)
* **Punktesichtbarkeit**: Einzelne Messpunkte ein-/ausblenden (für Admin)
* **Transformationen pro Punkt**: GUI zum Festlegen ob Addition, Projektion oder beides pro Punkt
* **CSV-Import im Web-Interface**: Import von Messdaten

* **Kommentare** hinzufügen, bearbeiten

###  Refactor

* phpDocumentor

* change db password

* Harden Models

* Return types, docstrings?

* test coverage?

## notes

* import L from leaflet -> bad (tree shaking)
* leaflet latest release very old

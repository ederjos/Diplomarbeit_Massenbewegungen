# Diplomarbeit_Massenbewegungen
Diplomarbeit als Web-Plattform zur Visualisierung von Massenbewegungen

## Installation

Auf Linux or via WSL:
1. Repo clonen `git clone https://github.com/ederjos/Diplomarbeit_Massenbewegungen.git`.
2. In das neue Verzeichnis wechseln `cd Diplomarbeit_Massenbewegungen`.
3. Docker starten.
4. Bash-Skript `setup.sh` ausführen. Dies kann einige Zeit in Anspruch nehmen.

Zum Starten: `start.sh` ausführen  
Zum Stoppen: `start.sh` stoppen sowie `stop.sh` ausführen

## TBD/TODO

Load measurement values chronologically (since the last update, the files are loaded by filename, not by their correct time order)

## Changes

### 04.11.2025 column rename

Let's rename the column 'interval' of type `INTERVAL` to 'period'
- Changed in PHP -> [x]
- Changed in ERM -> [x]

### 18.11.2025 naming a measurement

Let's add a column 'name' to the table `MEASUREMENT`
- Changed in Migration -> [x]
- Changed in Seeder    -> [x]
- Changed in ERM       -> [x]

## Projektplanungsdiagramme

* Prozessdiagramm (Flussdiagramm)
* Softwareentwurf Diagramme

## To be determined:

* max zoom for maps (schummerung, ...)
* which maps are needed

## remove polyline-decorator plugin
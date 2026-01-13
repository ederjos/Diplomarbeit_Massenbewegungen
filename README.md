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

## TBD/TODO


* change db password
* in time series: show name of measurement (NM, FM42)
* Add button to select/deselect all points from time series (or implement this in another way, e.g. dbl click)

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

## To be determined:

* max zoom for maps (schummerung, ...)
* which maps are needed

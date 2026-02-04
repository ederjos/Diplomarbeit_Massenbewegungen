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

* URL hashen? Oder Zugriff im Rendering unterbinden?

* (SRID (nur EPSG) in Formular auswählen (default vbg 31254))
    * Backend: Standard-SRID auf Lat/Lon setzen

* Frontend SRID-unabhängig machen (fehlt noch ProjectTimeline)

* PHP-Comments on own line

* Ambiguous numbers MUST be clarified

* Single quotes MUST be used

* Check for type-hinting

* phpDocumentor

* Rohdaten nur zum Speichern (x,y,z), nicht damit rechnen (geom in x,y,z konvertieren)

* Berichte drucken? (Print-Funktion auf /projects/id)

* Was automatisieren? -> Trendanalyse? Projekte mit großen Änderungen hervorheben!

* 3D-Distanz berechnen? Pythi(x + y + z) -> Ungenau

* change db password

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


## Für den schriftlichen Teil

* Überschrift mit Text: "Im Kapitel wird das ....-Modul beschrieben. In 17.1 gibt's 'nen Überblick, in 17.2 geht's um ..."
* Anhand Inhaltsverzeichnis schon roten Faden, überlegen was wo drin ist.
* Abb mit Erklärung: auch mehrere Zeilen (erklären)
* JEDE Abb MUSS IM TEXT ERWÄHNT WERDEN! (Im Fließtext)
* Unten steht, wer geschrieben hat
* Keine Stiefmütterchen
* Abb unten beschriften, Tabellen oben (letztere können sehr lang werden, Zsf zuerst macht mehr Sinn; Grafik sowieso -> darunter)
* Relevante Ausschnitte aus dem Code (wie Authentifizierung lösen -> relevante Teile) KEINE Screenshots mit Darkmode (Lightmode & Text)
* Querformat nach Möglichkeit vermeiden
* Über Software-Patterns schreiben
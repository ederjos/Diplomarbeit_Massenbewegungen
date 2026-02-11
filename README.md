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

### From Lastenheft (not yet implemented)
* **Bezugsepoche GUI**: Admin/Editor soll Bezugsepoche pro Projekt setzen können (Settings-Seite)
* **Epochenauswahl Zeitbereich**: Von/Bis-Datumsfilter für die Darstellung der Messungen
* **Punktesichtbarkeit**: Einzelne Messpunkte ein-/ausblenden (Checkbox pro Punkt)
* **Verschiebungsarten im GUI umschaltbar**: Option A (Pythagoras 2D), Option B (Projektion auf Achse), Option C (3D-Vektorlänge) — Backend ist implementiert, GUI-Auswahl fehlt
* **Kommentare editierbar**: Admin/Editor sollen Kommentare zu Messepochen ändern können (CRUD-UI)
* **Übersichtsbild pro Messepoche**: Upload/Link eines Orthophotos oder Fotos pro Messepoche
* **Transformationen pro Punkt**: GUI zum Festlegen ob Addition, Projektion oder beides pro Punkt
* **CSV-Import im Web-Interface**: Import von Messdaten im Format `Punktnummer;X;Y;Z;Anmerkung`
* **Rollenbasierte Berechtigungen**: Viewer hat nur Leserechte, Editor kann hinzufügen/bearbeiten, Admin kann alles (Authorization Policies fehlen)
* **Projektion-Achse pro Punkt**: GUI zum Einstellen der normierten Achse (ax, ay) pro Punkt

### Technical Debt

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

### Patterns

| Pattern                    | Where to Apply                                                                        | Benefit                                                |
| -------------------------- | ------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Repository Pattern         | Extract DB queries from controllers into `ProjectRepository`, `MeasurementRepository` | Testability, separation of concerns                    |
| Form Request Validation    | Create `ComputeDisplacementRequest` instead of inline validation                      | Cleaner controllers, reusable validation               |
| Policy/Gate Authorization  | Create `ProjectPolicy`, `CommentPolicy` with `viewAny`, `update`, `delete`            | Role-based access per Lastenheft (Admin/Editor/Viewer) |
| Strategy Pattern           | For displacement calculation modes (A/B/C) — each mode as its own strategy class      | Open/Closed Principle, easy to add new modes           |
| Observer Pattern           | Already used in `MeasurementValue::booted()` — could extend to log changes            | Audit trail                                            |
| Enum                       | Replace string-based role names with PHP 8.1 Enum (`RoleEnum::Admin`)                 | Type safety                                            |


### Notes

The Clerk model exists separately from User but the seeder uses the same IDs — consider if clerks should actually be users with a role, to avoid the FK confusion
magellan.php config lists SRID 31254 as "geodetic" — technically MGI/Austria GK West is a projected CRS, not geodetic. This may cause subtle issues with distance calculations
The Controller base class uses deprecated AuthorizesRequests, DispatchesJobs traits that are removed in newer Laravel versions
Empty Support and Traits directories should be cleaned up or used
The CommentsTableSeeder hardcodes measurement_id => 28 which is fragile


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
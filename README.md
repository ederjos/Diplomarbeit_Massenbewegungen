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

## Nach dem Pushen

Manchmal wird bei Commits die Datenbank bearbeitet. Danach einfach diesen Befehl ausführen:  
`./vendor/bin/sail artisan migrate:fresh --seed`

## TBD/TODO

### From Lastenheft (not yet implemented)

* **Bezugsepoche GUI**: Admin/Editor soll Bezugsepoche pro Projekt setzen können (Settings-Seite)
* **Punktesichtbarkeit**: Einzelne Messpunkte ein-/ausblenden (für Admin)
* **Kommentare editierbar**: Admin/Editor sollen Kommentare zu Messepochen ändern können (CRUD-UI)
* **Übersichtsbild pro Messepoche**: Upload/Link eines Orthophotos oder Fotos pro Messepoche
* **Transformationen pro Punkt**: GUI zum Festlegen ob Addition, Projektion oder beides pro Punkt
* **CSV-Import im Web-Interface**: Import von Messdaten
* **Rollenbasierte Berechtigungen**: Viewer hat nur Leserechte, Editor kann hinzufügen/bearbeiten, Admin kann alles (Authorization Policies fehlen)

### Technical Debt

* je Projekt: Foto anzeigen; wo: unter Orthofoto oder unter ProjectTimeline

* Projektion: immer auf aktuelle Referenzepoche; auch in ProjectTimeline anwenden!

* Slaven fragen: Reset-Button für Formular-Eingabe?

###  Refactor

* Get rid of ".*" queries in php -> loads unnecessarily much

* Component Structure:
```vue
<script setup lang="ts">
// 1. Imports (external first, then internal)
// 2. Props with withDefaults
// 3. Emits
// 4. Composables
// 5. Local state (ref, reactive)
// 6. Computed properties
// 7. Watchers
// 8. Methods/Event handlers
// 9. Lifecycle hooks (onMounted, onUnmounted)
</script>
```

* Template structure:
    ```vue
    <element
        v-if v-show v-for
        v-model
        :key
        ref
        :prop-name (alphabetically)
        @event-name
        class
        other-attributes
    >
    ```

* phpDocumentor

* change db password

### Patterns

| Pattern                    | Where to Apply                                                                        | Benefit                                                |
| -------------------------- | ------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Repository Pattern         | Extract DB queries from controllers into `ProjectRepository`, `MeasurementRepository` | Testability, separation of concerns                    |
| Form Request Validation    | Create `ComputeDisplacementRequest` instead of inline validation                      | Cleaner controllers, reusable validation               |
| Policy/Gate Authorization  | Create `ProjectPolicy`, `CommentPolicy` with `viewAny`, `update`, `delete`            | Role-based access per Lastenheft (Admin/Editor/Viewer) |
| Strategy Pattern           | For displacement calculation modes (A/B/C) — each mode as its own strategy class      | Open/Closed Principle, easy to add new modes           |
| Observer Pattern           | Already used in `MeasurementValue::booted()` — could extend to log changes            | Audit trail                                            |
| Enum                       | Replace string-based role names with PHP 8.1 Enum (`RoleEnum::Admin`)                 | Type msafety                                            |


### Notes

magellan.php config lists SRID 31254 as "geodetic" — technically MGI/Austria GK West is a projected CRS, not geodetic. This may cause subtle issues with distance calculations
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
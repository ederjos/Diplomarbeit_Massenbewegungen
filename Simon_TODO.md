**Kommentare!**

**KI-Code selber neu schreiben!**

- ProjectTimeline.vue
    - **TODOs!**
    - Styling anpassen (auch in Project.vue)
    - Zoomen!
    - Größe und Auflösung verbessern
- Admin-Bereich:
    - Registrierungen authorisieren
        - Bei Signup wird nur eine Anfrage zum Signup gestellt, zusätzlich zu den aktuellen Infos soll auch ein Textfeld hinzugefügt werden, in dem der User weitere Infos für den Admin angeben kann (z.B. gewünschte Rolle/Schreibrechte)
        - Admin muss die Registrierung überprüfen & eine Rolle zuweisen (Erstellen einer neuen Rolle soll hier nicht möglich sein, das muss auf der dafür gedachten Seite erledigt werden)
    - **Nachdem das Rechte-System überarbeitet wurde:**\
      Benutzer & Rollen verwalten
        - Tabellen für Benutzer & Rollen
        - Formular für Add/Edit/Delete von Benutzern & Rollen
- Rollen & Rechte:
    - **Das Rechte-System wird überarbeitet werden müssen, da es aktuell einige Probleme gibt:**
        - keine Differenzierung zwischen Add Projekt & Add Messung
        - keine Projektspezifischen Rechte (z.B. nur für Projekt X Add Messung erlauben)
    - Nur Mitglieder eines Projektes können auf dieses zugreifen.
    - Nur Benutzer, welche eine Rolle mit den entsprechenden Rechten haben, können gewisse Aktionen durchführen (z.B. Add/Edit)
    - Formular für Add/Edit/Delete von Projekten & Messungen.

Upgrades:

- Laravel 13
- PHP 8.5 & PostgreSQL 18 (with PostGIS)
- Laravel Boost

---

**Auch CSS verstehen!**

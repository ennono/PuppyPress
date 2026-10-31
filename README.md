# Rudelwerk

**Vereinsverwaltung für Hundezuchtvereine als WordPress-Plugin.**

Rudelwerk bündelt alles, was ein Zuchtverein auf seiner Website braucht: Deckrüden mit dreistufiger Abstammung, Würfe und Welpenvermittlung, Vermittlungshunde, Körberichte, Zuchtzulassungen, Ergebnisse, Ausstellungen, Trimmnachweise, Tierbetreuungen, Literatur und Urlaubsangebote. Dazu kommen Züchter-, Besitzer- und Zwingerverwaltung, Filter für die Deckrüdensuche, Übersichtsseiten und Widgets.

Das Plugin ist aus dem Child-Theme *Extra Child* hervorgegangen und übernimmt dessen komplette Fachlogik. Seitdem kümmert sich das Theme nur noch um das Aussehen; die Inhalte bleiben auch bei einem Theme-Wechsel erhalten.

> **Ohne Datenmigration:** Alle Schlüssel für Post Types, Taxonomien, Meta-Felder, Term-Meta, Widgets und Sidebars sind identisch mit dem bisherigen Theme. Mit der mitgelieferten deutschen Übersetzung bleiben auch alle URLs gleich.

## Funktionen

| Bereich | Inhalt |
| --- | --- |
| **Inhaltstypen** | Deckrüden, Welpen/Würfe, Vermittlungshunde, Körberichte, Zuchtzulassungen, Ergebnisse, Ausstellungen, Trimmnachweise, Tierbetreuungen, Literatur, Urlaubsangebote |
| **Taxonomien** | Zwinger, Züchter, Besitzer, Prüfungen, HD, ED, Größen, Titel, Veranstaltungen, Zeiträume, Städte, PLZ, Kategorien, Autoren u. a. |
| **Backend** | Meta-Boxen mit Feld-Schema, Abstammungsraster (14 Ahnen inkl. Körbericht), Mehrfach-Fotoauswahl über die Mediathek, Kontaktfelder für Zwinger, Züchter und Besitzer |
| **Frontend** | Detailansichten für Deckrüden, Würfe und Vermittlungshunde, die mit jedem Theme funktionieren; Anzeigenamen mit Zwinger (z. B. „Arko vom Waldrand“); Deckrüdenfilter (PLZ, Größe, HD, ED, Prüfungen, Titel, Körbericht) |
| **Übersichten** | 9 Shortcodes (siehe unten), die die alten „Alle …“-Seitentemplates ersetzen. Seiten mit alten Templates funktionieren ohne Anpassung weiter. |
| **Widgets** | 7 „Neueste …“-Widgets mit unveränderten IDs, Sidebar für Ergebnisseiten |
| **Sprachen** | Englisch (Quelltext) und Deutsch, inkl. `.mo` und `.l10n.php` (WordPress 6.5+) |

## Installation

1. Ordner `rudelwerk` nach `wp-content/plugins/` kopieren (oder ZIP über *Plugins → Installieren* hochladen).
2. Plugin aktivieren.
3. Bei bestehender Website mit dem *Extra Child*-Theme: der [Migrationsanleitung](docs/MIGRATION.md) folgen.

Voraussetzungen: WordPress 6.0+, PHP 7.4+. Für das Plugin selbst ist weder Composer noch ein Build-Schritt nötig.

## Shortcodes

Alle Shortcodes akzeptieren zusätzlich `title="…"` und `intro="…"` für eine Überschrift und einen Einleitungstext.

| Shortcode | Ausgabe | Wichtige Attribute |
| --- | --- | --- |
| `[rudelwerk_stud_dogs]` | Deckrüden als Karten, nach Zwingern gruppiert | `filter="yes"` zeigt den Filter, `group="none"` für eine alphabetische Gesamtliste |
| `[rudelwerk_stud_dog_filter]` | Nur das Filterformular | `action="current"` filtert auf der aktuellen Seite statt im Deckrüden-Archiv |
| `[rudelwerk_terms]` | Hierarchische Liste einer Taxonomie | `taxonomy="kennel"`, `count="yes"`, `hide_empty="no"`, `link_empty="no"`, `depth="2"`, `orderby`, `order` |
| `[rudelwerk_placement_overview]` | Welpenvermittlung und Vermittlungskategorien mit Anzahl | – |
| `[rudelwerk_posts]` | Alphabetische Liste mit Zwingername | `post_type="approvals"` oder `post_type="breeding-license"` |
| `[rudelwerk_litters]` | Gefallene und erwartete Würfe, nach PLZ sortiert | `status="all\|born\|expected"` |
| `[rudelwerk_breeders_board]` | Züchtertafel: sichtbare Zwinger nach PLZ mit Züchterkontakten | – |
| `[rudelwerk_directory]` | Tabelle nach PLZ-Regionen | `post_type="trimming-proofs"` oder `post_type="dog-sitters"` |
| `[rudelwerk_literature]` | Literatur nach Kategorien | – |

## Anpassen

### Templates überschreiben

Alle Ausgaben stammen aus `templates/`. Wer etwas anpassen will, kopiert die Datei ins Theme nach `rudelwerk/` und ändert sie dort, z. B.:

```text
wp-content/themes/mein-theme/rudelwerk/single/stud-dog.php
wp-content/themes/mein-theme/rudelwerk/parts/pedigree.php
```

Templates bekommen ihre Daten über `$args`. Mit `rudelwerk_template( 'parts/pedigree.php', $args )` lassen sie sich auch in eigenen Theme-Dateien einbinden.

### Template-Tags

```php
rudelwerk_stud_dog_name( $post );        // "Arko vom Waldrand"
rudelwerk_display_kennel( $post );       // WP_Term|null (erster sichtbarer Zwinger)
rudelwerk_the_pedigree( $post );         // Abstammungstabelle
rudelwerk_stud_dog_filter_form( $url );  // Filterformular
```

### Hooks (Auswahl)

| Hook | Zweck |
| --- | --- |
| `rudelwerk_post_type_args`, `rudelwerk_taxonomy_args` | Registrierungsargumente ändern |
| `rudelwerk_post_type_slug`, `rudelwerk_taxonomy_slug` | URL-Slugs unabhängig von der Übersetzung festlegen |
| `rudelwerk_post_fields`, `rudelwerk_term_fields` | Felder hinzufügen oder entfernen |
| `rudelwerk_display_titles` | `false` schaltet „Name + Zwinger“ bzw. „Welpen …“ in Titeln ab |
| `rudelwerk_single_content` | `false` schaltet die automatische Detailansicht ab |
| `rudelwerk_archive_filter_form` | `false` blendet den Filter im Deckrüden-Archiv aus |
| `rudelwerk_load_styles` | `false` lädt das Plugin-CSS nicht |
| `rudelwerk_template` | Template-Pfade umleiten |
| `rudelwerk_widgets` | Widget-Konfigurationen ändern |
| `rudelwerk_date_format` | Datumsformat für Wurftage (Standard: WordPress-Einstellung) |
| `rudelwerk_icon` | Eigene Icons statt der eingebauten SVGs |
| `rudelwerk_result_replaced_sidebars` | Welche Theme-Sidebar auf Ergebnisseiten ersetzt wird |

## Aufbau

```text
rudelwerk.php                 Plugin-Header, Autoloader, Bootstrap
includes/
  Plugin.php                  Verdrahtet alle Module
  Content/                    Post Types, Taxonomien, Feld-Schema, Abstammung
  Admin/                      Meta-Boxen, Term-Felder, Assets, Hinweise
  Frontend/                   Titel, Detailansichten, Abfragen, Filter, Shortcodes, Templates
  Data/                       Lese-Helfer (Hunde, Würfe, Kontakte)
  Widgets/                    Ein konfigurierbares Widget für alle sieben Varianten
  Compat/                     Sortierung nach „Category Custom Fields“
  template-functions.php      Öffentliche Template-Tags
templates/                    Überschreibbare Ausgabe-Templates
assets/                       CSS und JS (Backend-Mediathek)
languages/                    POT, de_DE (PO, MO, l10n.php)
bin/i18n.php                  Übersetzungen extrahieren und kompilieren
tests/run.php                 Tests ohne Abhängigkeiten
```

Das Feld-Schema in `includes/Content/Fields.php` bestimmt alles Weitere: Meta-Registrierung, Formulare, Sanitizing und REST-Berechtigungen. Ein neues Feld braucht deshalb nur einen Eintrag dort.

## Entwicklung

```bash
php tests/run.php            # Tests (Datenmodell, Sanitizing, Abstammung, Filter, Übersetzungen, Templates)
php bin/i18n.php pot         # languages/rudelwerk.pot neu erzeugen
php bin/i18n.php update      # POT in die PO-Dateien übernehmen
php bin/i18n.php compile     # PO → MO und .l10n.php
composer install && composer lint   # optional: WordPress Coding Standards
```

## Lizenz

GPL-2.0-or-later © Timo Fricke

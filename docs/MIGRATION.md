# Migration vom Theme „Extra Child“ zu Rudelwerk

Diese Anleitung beschreibt den Umstieg einer bestehenden Website, die bisher das Child-Theme *Extra Child* verwendet. Daten müssen dabei **nicht** migriert werden: Post Types, Taxonomien, Meta-Keys, Term-Meta, Widget-IDs und die Sidebar-ID sind identisch.

## Kurzfassung

1. Backup von Datenbank und `wp-content` anlegen.
2. Rudelwerk installieren, **noch nicht aktivieren**.
3. Im Child-Theme die Fachlogik entfernen (siehe unten) und die neue, schlanke `functions.php` einsetzen.
4. Rudelwerk aktivieren.
5. Einmal *Einstellungen → Permalinks* öffnen und speichern (Rudelwerk spült die Regeln zwar selbst, sicher ist sicher).
6. Die Prüfliste am Ende durchgehen.

Schritte 3 und 4 sollten direkt nacheinander passieren. Solange beide aktiv sind, registrieren Theme und Plugin dieselben Inhalte doppelt. Rudelwerk zeigt in diesem Fall einen Hinweis im Backend an.

## Was ins Plugin gewandert ist

| Bisher im Theme | Jetzt in Rudelwerk |
| --- | --- |
| `includes/custom_post_types.php` | `includes/Content/PostTypes.php` |
| `includes/custom_taxonomies.php` (Registrierung) | `includes/Content/Taxonomies.php` |
| `includes/custom_taxonomies.php` (Felder für Zwinger, Züchter, Besitzer) | `includes/Content/Fields.php` + `includes/Admin/TermFields.php` |
| `includes/custom_fields.php` | `includes/Content/Fields.php`, `includes/Content/Pedigree.php`, `includes/Admin/MetaBoxes.php` |
| `includes/widgets.php` | `includes/Widgets/` |
| `includes/sidebars.php` | `includes/Frontend/Sidebars.php` |
| `js/upload_image_meta_box.js` | `assets/js/admin-media.js` |
| Abfrage-Logik der Templates `archive-*`, `taxonomy-*` | `includes/Frontend/Queries.php`, `includes/Frontend/StudDogFilter.php` |
| Fachliche Ausgabe der `single-*`-Templates | `templates/single/*.php` (über `the_content`, theme-unabhängig) |
| Seitentemplates `template-all-*.php`, `template-breeders-board.php` | Shortcodes, siehe unten |
| Sortierung nach „Category Custom Fields“ in `functions.php` | `includes/Compat/TermCustomFieldOrder.php` |
| `extrachild.pot`, `de_DE.po/.mo` | `languages/rudelwerk*.{pot,po,mo,l10n.php}` |

## Aufräumen im Theme

### Diese Dateien löschen

```text
includes/                                   (kompletter Ordner)
js/upload_image_meta_box.js
archive-breeding-licenses.php               (war leer)
archive-puppies.php
archive-stud-dogs.php
archive-vacation-deals.php                  (entsprach dem Standard-Archiv von Extra)
single-dogs-to-place.php
single-puppies.php
single-results.php
single-stud-dogs.php
taxonomy-event.php
taxonomy-kennel.php
template-all-*.php                          (12 Dateien)
template-breeders-board.php
.*.swp                                      (Vim-Swap-Dateien)
*.po~ / *.pot~                              (Loco-Backups)
extrachild.pot, de_DE.po, de_DE.mo          (Strings sind jetzt im Plugin)
```

### Was im Theme bleibt

Diese Teile betreffen nur das Aussehen und gehören deshalb ins Theme:

- `style.css` (Theme-Header und Farben für den Footer)
- `footer.php` (Footer-Markup)
- Laden des Parent-Stylesheets
- Entfernen der Präfixe in Archivtiteln („Kategorie:“, „Archiv:“ …)
- Font Awesome, falls das Theme oder Inhalte es noch nutzen. Rudelwerk braucht es nicht mehr, weil es eigene SVG-Icons mitbringt.
- `page-template-blog-feed.php` samt `extrachild_get_blog_feed_page_options()`. Das ist eine Variante des Extra-Templates „Blog Feed“. **Achtung:** Die Abfrage nutzte `post_type => 'dogs'`, einen Post Type, den es nie gab. Das Template hat also nie Inhalte angezeigt und kann vermutlich weg.
- Optional das Abschalten des Divi-Post-Types „Projekte“ (`unregister_post_type( 'project' )`). Das ist eine Einstellung der Website und hat mit den Vereinsinhalten nichts zu tun.

### Neue `functions.php` für das Child-Theme

```php
<?php
/**
 * Extra Child – nur noch Präsentation. Die Vereinsinhalte liefert das Plugin Rudelwerk.
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

	// Nur behalten, wenn Inhalte oder Theme Font-Awesome-Icons verwenden.
	wp_enqueue_style( 'font-awesome', get_stylesheet_directory_uri() . '/fonts/fontawesome-free-5.10.0-web/css/all.min.css', array(), '5.10.0' );
} );

// Divi-Post-Type "Projekte" ausblenden.
add_action( 'init', function () {
	unregister_post_type( 'project' );
}, 20 );

// Präfixe wie "Kategorie:" aus Archivtiteln entfernen.
add_filter( 'get_the_archive_title', function ( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	if ( is_author() ) {
		return '<span class="vcard">' . get_the_author() . '</span>';
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	if ( is_home() ) {
		return single_post_title( '', false );
	}
	return $title;
} );
```

## Seitentemplates → Shortcodes

Seiten, denen noch ein altes Template zugewiesen ist, funktionieren ohne Änderung weiter. Sobald die Template-Datei im Theme fehlt, nutzt WordPress `page.php`, und Rudelwerk hängt die passende Übersicht an den Seiteninhalt an. Die Templates bleiben im Feld *Seiten-Attribute → Template* auswählbar.

Wer die Seiten umstellen möchte, setzt das Template auf „Standard“ und fügt den Shortcode in den Inhalt ein:

| Altes Template | Shortcode |
| --- | --- |
| All Stud Dogs | `[rudelwerk_terms taxonomy="kennel" title="Registrierte Zwinger mit Deckrüden"]` |
| All Stud Dogs (Complete list) | `[rudelwerk_stud_dogs group="none"]` |
| All approvals | `[rudelwerk_posts post_type="approvals"]` |
| All breeding licenses | `[rudelwerk_posts post_type="breeding-license"]` |
| All puppies | `[rudelwerk_terms taxonomy="puppies-city" count="yes" hide_empty="no"]` |
| All dogs to place | `[rudelwerk_placement_overview]` |
| All shows | `[rudelwerk_terms taxonomy="period"]` |
| All results | `[rudelwerk_terms taxonomy="event" title="Veranstaltungen"][rudelwerk_terms taxonomy="period" title="Ausstellungen"]` |
| All trimming proofs | `[rudelwerk_directory post_type="trimming-proofs"]` |
| All dog sitters | `[rudelwerk_directory post_type="dog-sitters"]` |
| All literature | `[rudelwerk_literature]` |
| Breeder's board | `[rudelwerk_breeders_board]` |

Neu hinzugekommen: `[rudelwerk_stud_dogs filter="yes"]` (Deckrüden mit Filter auf einer beliebigen Seite) und `[rudelwerk_litters]` (gefallene und erwartete Würfe, wie bisher im Welpen-Archiv).

## Verhalten, das sich bewusst geändert hat

Rudelwerk übernimmt die Funktionen 1:1, behebt aber Fehler, die im Theme steckten:

**Daten und Backend**
- Beim Speichern eines Deckrüden wurden die Meta-Felder `hd` und `ed` immer geleert (die Felder gab es im Formular nicht). Beim Speichern eines Wurfs passierte dasselbe mit `address`, `phone`, `mobile` und `fax`. Rudelwerk schreibt nur noch Felder, die im Formular vorkommen. Bereits gelöschte Werte lassen sich damit nicht zurückholen.
- Beschreibungen in der Abstammung verloren beim Speichern ihre Zeilenumbrüche (`sanitize_text_field`). Jetzt bleiben sie erhalten und werden auch so angezeigt.
- Alle Ausgaben werden escaped, E-Mail-Adressen werden mit `antispambot()` verschleiert, Term-Felder sind per Nonce und Berechtigungsprüfung geschützt (vorher ungeprüftes `$_POST`).
- Die Fotoauswahl für Vater und Mutter erlaubt Mehrfachauswahl, zeigt Vorschaubilder und kann einzelne Fotos wieder entfernen.
- Das Häkchen „Erwartete Welpen“ speichert `1` statt `on`. Beide Werte werden erkannt.

**Frontend**
- Taxonomie „Titel“: Die Archiv-URLs (`/by-title/…`) lieferten nie Ergebnisse, weil die Query-Variable `title` in WordPress für den Beitragstitel reserviert ist. Die Taxonomie nutzt jetzt die Query-Variable `stud_dog_title`. Dasselbe gilt für die Tierbetreuungs-Art (`dog_sitter_type`).
- Der Deckrüdenfilter nutzt GET-Parameter (`rw_zip`, `rw_hd` …). Gefilterte Ergebnisse lassen sich so als Link teilen und mit „Zurück“ erreichen. Zwinger ohne PLZ fehlten bisher im Archiv; jetzt werden sie nur ausgeblendet, wenn nach PLZ gefiltert wird.
- Der Zwingername im Titel wurde auf der Detailseite nur angezeigt, wenn einem Deckrüden mehr als ein Zwinger/Züchter/Besitzer zugeordnet war. Jetzt gilt überall: erster nicht versteckter Zwinger.
- Leere Wurftage erschienen als „01.01.70“. Jetzt werden sie weggelassen, und das Datumsformat folgt den WordPress-Einstellungen (anpassbar über `rudelwerk_date_format`).
- Leere Abschnitte (z. B. „Besitzerdaten“ ohne Besitzer) werden nicht mehr als leere Überschrift angezeigt.
- Die Züchtertafel blendet jetzt nur Zwinger aus, die als „unsichtbar“ markiert sind. Vorher fehlten auch Zwinger, bei denen das Feld nie gespeichert worden war.
- Tierbetreuungen zeigten als Ort die Städte der Trimmnachweise (falsche Taxonomie). Jetzt stimmt die Zuordnung.
- Im Widget „Recent Results“ wurden Zuchtzulassungen nie angezeigt (falscher Post-Type-Slug `breeding-licenses`). Das ist behoben.
- Der „Alle …“-Button der Widgets steht jetzt innerhalb des Widget-Containers.

## Prüfliste nach der Umstellung

- [ ] Deckrüden-, Welpen- und Vermittlungsseiten öffnen: Daten, Kontakte, Abstammung und Fotos sind sichtbar.
- [ ] Deckrüden-Archiv: Filter funktioniert, Zwingername steht beim Hund.
- [ ] Alle Übersichtsseiten (ehemalige Templates) zeigen ihre Listen.
- [ ] *Design → Widgets*: Die „Rudelwerk: …“-Widgets stehen an ihren bisherigen Positionen, die „Alle …“-Links sind gesetzt.
- [ ] Alte URLs (z. B. `/deckrueden/`, `/zwinger/…`, `/zucht/koerberichte/…`) funktionieren.
- [ ] Einen Deckrüden im Backend öffnen und speichern: Abstammung und Körberichte bleiben erhalten.

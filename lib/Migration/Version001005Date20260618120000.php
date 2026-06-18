<?php
namespace OCA\KursUmstufung\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * v1.0.5 — Soft-Delete: `deleted_at` erlaubt das Wiederherstellen gelöschter
 * Anträge (Undo). Gelöschte Zeilen bleiben erhalten, werden aber aus den
 * Listenabfragen ausgeblendet.
 */
class Version001005Date20260618120000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        if (!$schema->hasTable('kurs_umstufung_requests')) {
            return null;
        }
        $table = $schema->getTable('kurs_umstufung_requests');

        if (!$table->hasColumn('deleted_at')) {
            $table->addColumn('deleted_at', 'datetime', [
                'notnull' => false,
            ]);
        }

        return $schema;
    }
}

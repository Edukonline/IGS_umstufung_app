<?php
namespace OCA\KursUmstufung\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * v1.0.4 — Genehmigungs-Workflow & Archiv:
 *  - decision_reason / decided_by für Annehmen/Ablehnen
 *  - school_year für Archiv-/Jahresfilter
 *  - Index auf status für findAllSubmitted()
 */
class Version001004Date20260612120000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        if (!$schema->hasTable('kurs_umstufung_requests')) {
            return null;
        }
        $table = $schema->getTable('kurs_umstufung_requests');

        if (!$table->hasColumn('school_year')) {
            $table->addColumn('school_year', 'string', [
                'length' => 16,
                'notnull' => false,
            ]);
        }
        if (!$table->hasColumn('decided_by')) {
            $table->addColumn('decided_by', 'string', [
                'length' => 64,
                'notnull' => false,
            ]);
        }
        if (!$table->hasColumn('decision_reason')) {
            $table->addColumn('decision_reason', 'text', [
                'notnull' => false,
            ]);
        }
        if (!$table->hasIndex('kurs_umstufung_status_idx')) {
            $table->addIndex(['status'], 'kurs_umstufung_status_idx');
        }

        return $schema;
    }
}

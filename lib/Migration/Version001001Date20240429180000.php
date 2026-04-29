<?php
namespace OCA\KursUmstufung\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\DB\ISchemaWrapper;

class Version001001Date20240429180000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
        $schema = $schemaClosure();
        $table = $schema->getTable('kurs_umstufung_requests');
        if (!$table->hasColumn('updated_at')) {
            $table->addColumn('updated_at', 'datetime', [
                'notnull' => false,
            ]);
        }
        if (!$table->hasColumn('class')) {
            $table->addColumn('class', 'string', [
                'length' => 16,
                'notnull' => false,
            ]);
        }
        return $schema;
    }
}

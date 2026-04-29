<?php
namespace OCA\KursUmstufung\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\DB\ISchemaWrapper;

class Version001001Date20240429190000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
        $schema = $schemaClosure();
        $table = $schema->getTable('kurs_umstufung_requests');
        if ($table->hasColumn('class') && !$table->hasColumn('student_class')) {
            $table->dropColumn('class');
            $table->addColumn('student_class', 'string', [
                'length' => 16,
                'notnull' => false,
            ]);
        }
        return $schema;
    }
}

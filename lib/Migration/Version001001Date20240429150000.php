<?php
namespace OCA\KursUmstufung\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\DB\ISchemaWrapper;

class Version001001Date20240429150000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
        $schema = $schemaClosure();
        if (!$schema->hasTable('kurs_umstufung_requests')) {
            $table = $schema->createTable('kurs_umstufung_requests');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);
            $table->addColumn('student_name', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);
            $table->addColumn('subject', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);
            $table->addColumn('old_level', 'string', [
                'length' => 32,
                'notnull' => true,
            ]);
            $table->addColumn('new_level', 'string', [
                'length' => 32,
                'notnull' => true,
            ]);
            $table->addColumn('reason', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('status', 'string', [
                'length' => 32,
                'notnull' => true,
                'default' => 'draft',
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
                'default' => 'CURRENT_TIMESTAMP',
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'kurs_umstufung_user_idx');
        }
        return $schema;
    }
}

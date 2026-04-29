<?php
namespace OCA\UmstufungMNS\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version001001Date20240429150000 extends SimpleMigrationStep {
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('umstufung_mns_requests')) {
            $table = $schema->createTable('umstufung_mns_requests');
            $table->addColumn('id', 'bigint', [
                'autoincrement' => true,
                'notnull' => true,
                'length' => 20,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('student_name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('subject', 'string', [
                'notnull' => true,
                'length' => 128,
            ]);
            $table->addColumn('old_level', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('new_level', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('reason', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('status', 'string', [
                'notnull' => true,
                'length' => 32,
                'default' => 'draft',
            ]);
            $table->addColumn('created_at', 'integer', [
                'notnull' => true,
                'length' => 11,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'umstufung_user_idx');
            $table->addIndex(['status'], 'umstufung_status_idx');
        }

        return $schema;
    }
}

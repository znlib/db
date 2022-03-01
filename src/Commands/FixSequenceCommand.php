<?php

namespace ZnLib\Db\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Factories\ManagerFactory;
use ZnSandbox\Sandbox\Generator\Domain\Repositories\Eloquent\SchemaRepository;

class FixSequenceCommand extends Command
{
    protected static $defaultName = 'db:database:fix-sequence';
    private $capsule;
    private $schemaRepository;

    public function __construct(?string $name = null, SchemaRepository $schemaRepository)
    {
        $this->capsule = ManagerFactory::createManagerFromEnv();
        $this->schemaRepository = $schemaRepository;
        parent::__construct($name);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption(
                'withConfirm',
                null,
                InputOption::VALUE_REQUIRED,
                'Your selection migrations',
                true
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['<fg=white># Fix Sequence</>']);

        $connections = DbFacade::getConfigFromEnv();
        foreach ($connections as $connectionName => $connection) {
            $conn = $this->capsule->getConnection($connectionName);
            $tableList = $this->schemaRepository->allTables();
            /*$tableList = $conn->select('
                SELECT *
                FROM pg_catalog.pg_tables
                WHERE schemaname != \'pg_catalog\' AND schemaname != \'information_schema\';');*/
            $tables = [];
            $schemas = [];
            foreach ($tableList as $tableEntity) {
                $tableName = $tableEntity->getName();
                if ($tableEntity->getSchemaName()) {
                    $tableName = $tableEntity->getSchemaName() . '.' . $tableName;
                }
                $tables[] = $tableName;
                if ($tableEntity->getSchemaName() && $tableEntity->getSchemaName() != 'public') {
                    $schemas[] = $tableEntity->getSchemaName();
                }
            }

            if (empty($tables)) {
                $output->writeln(['', '<fg=yellow>Not found tables!</>', '']);
            } else {
                foreach ($tableList as $tableEntity) {
                    /*foreach ($tables as $t) {
                      /*$tt = explode('.', $t);
                        if (count($tt) == 2) {
                            list($schema, $tableName) = $tt;
                        } else {
                            dd($t);
                        }*/
                    $tableName = $tableEntity->getName();

                    try {
                        $sql = 'SELECT last_value FROM ' . $tableName . '_id_seq;';
//                        $conn->statement($sql);
                        $lastValueRow = $this->capsule->getConnection()->select($sql);
                        $last_value = $lastValueRow[0]->last_value;
                        $queryBuilder = $this->capsule->getQueryBuilderByConnectionName('default', $tableName);
                        // if ($driver == DbDriverEnum::PGSQL && $schema->hasColumn($name, 'id')) {
                        $max = $queryBuilder->max('id');
                        if ($max) {
//                                $pkName = 'id';
//                                $sql = 'SELECT setval(\'' . $targetTableName . '_' . $pkName . '_seq\', ' . ($max) . ')';
//                                $connection = $queryBuilder->getConnection();
//                                $connection->statement($sql);
                        }
                        //}
                        $output->write("{$tableName} ... ");
                        if (empty($max)) {
                            $output->writeln("<fg=yellow>EMPTY TABLE (seq-{$last_value})</>");
                        } else {
                            if ($last_value >= $max) {
                                $output->writeln("<fg=green>OK ($last_value)</>");
                            } else {
                                $output->writeln("<fg=red>FAIL (seq-{$last_value} maxId-{$max})</>");
                            }
                        }
                    } catch (\Throwable $e) {
                        //throw new \Exception($e->getMessage());
                    }
                }
            }
        }
        $output->writeln(['', '<fg=green>Fix Sequence success!</>', '']);
        return 0;
    }
}

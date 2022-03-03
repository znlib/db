<?php

namespace ZnLib\Db\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnCore\Base\Helpers\StringHelper;
use ZnCore\Base\Legacy\Yii\Helpers\FileHelper;
use ZnLib\Db\Entities\TableEntity;
use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Factories\ManagerFactory;
use ZnLib\Fixture\Domain\Repositories\DbRepository;
use ZnSandbox\Sandbox\Generator\Domain\Repositories\Eloquent\SchemaRepository;
use ZnSandbox\Sandbox\Office\Domain\Libs\Zip;

class DumpCreateCommand extends Command
{
    protected static $defaultName = 'db:database:dump-create';
    private $capsule;
    private $schemaRepository;
    private $dbRepository;
    private $currentDumpPath;

    public function __construct(?string $name = null, SchemaRepository $schemaRepository, DbRepository $dbRepository)
    {
        $this->capsule = ManagerFactory::createManagerFromEnv();
        $this->schemaRepository = $schemaRepository;
        $this->dbRepository = $dbRepository;

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
        $output->writeln(['<fg=white># Dump Create</>']);

        $this->currentDumpPath = $_ENV['ROOT_DIRECTORY'] . '/' . $_ENV['DUMP_DIRECTORY'] . '/' . date('Y-m/d/H-i-s');

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

            //$currentDumpPath = $_ENV['ROOT_DIRECTORY'] . '/' . $_ENV['DUMP_DIRECTORY'] . '/' . date('Y-m/d/H-i-s');
            FileHelper::createDirectory($this->currentDumpPath);

            if (empty($tables)) {
                $output->writeln(['', '<fg=yellow>Not found tables!</>', '']);
            } else {

                // todo: блокировка БД от записи

//                foreach ($tables as $t) {
                foreach ($tableList as $tableEntity) {
                    $tableName = $tableEntity->getSchemaName() . '.' . $tableEntity->getName();
                    $output->write($tableName . ' ... ');
                    $this->dump($tableName, $tableEntity);
                    $output->writeln('<fg=green>OK</>');
                }

                // todo: разблокировка БД от записи
            }
        }

        $output->writeln(['', '<fg=green>Path: ' . $this->currentDumpPath . '</>', '']);

        $output->writeln(['', '<fg=green>Dump Create success!</>', '']);
        return 0;
    }

    private function dump(string $tableName, TableEntity $tableEntity) {
        $tablePath = $this->currentDumpPath . '/' . $tableName;
        $zip = new Zip($tablePath . '.zip');

        $page = 1;
        $perPage = 500;
        $queryBuilder = $this->dbRepository->getQueryBuilderByTableName($tableName);

        // todo: если есть ID или уникальные поля, сортировать по ним

        do {
            $queryBuilder->forPage($page, $perPage);
            $data = $queryBuilder->get()->toArray();
            if (!empty($data)) {
                $file = StringHelper::fill($page, 11, '0', 'before') . '.json';
                $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $zip->writeFile($file, $jsonData);
//                            $dumpFile = $tablePath . '/' . $file;
//                            FileHelper::save($dumpFile, $tableData);
            }
            $page++;
        } while (!empty($data));

        $zip->close();
    }
}

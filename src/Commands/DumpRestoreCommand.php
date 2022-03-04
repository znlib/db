<?php

namespace ZnLib\Db\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnCore\Base\Helpers\StringHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\FileHelper;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnLib\Console\Symfony4\Question\ChoiceQuestion;
use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Factories\ManagerFactory;
use ZnLib\Fixture\Domain\Repositories\DbRepository;
use ZnSandbox\Sandbox\Generator\Domain\Repositories\Eloquent\SchemaRepository;
use ZnSandbox\Sandbox\Office\Domain\Libs\Zip;

class DumpRestoreCommand extends Command
{
    protected static $defaultName = 'db:database:dump-restore';
    private $capsule;
    private $schemaRepository;
    private $dbRepository;
    private $currentDumpPath;
    private $dumpPath;

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

    /**
     * @return \ZnLib\Db\Capsule\Manager
     */
    public function getCapsule(): \ZnLib\Db\Capsule\Manager
    {
        return $this->capsule;
    }

    private function getHistory(): array
    {
        $options = [];
//        $options['only'][] = '*.zip';
        $tree = FileHelper::findFiles($this->dumpPath, $options);
        foreach ($tree as &$item) {
            $item = str_replace($this->dumpPath, '', $item);
            $item = dirname($item);
            $item = trim($item, '/');
        }
        $tree = array_unique($tree);
        sort($tree);
        $tree = array_values($tree);
        return $tree;
    }

    private function getTables(string $version)
    {
        $versionPath = $this->dumpPath . '/' . $version;
        $files = FileHelper::scanDir($versionPath);
        $tables = [];
        foreach ($files as $file) {
            $tables[] = str_replace('.zip', '', $file);
        }
        return $tables;
    }

    private function getZipPath(string $version, string $table): string
    {
        $versionPath = $this->dumpPath . '/' . $version;
        $zipPath = $versionPath . '/' . $table . '.zip';
        return $zipPath;
    }

    public function getQueryBuilderByTableName($name): \Illuminate\Database\Query\Builder
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        $queryBuilder = $connection->table($targetTableName);
        return $queryBuilder;
    }

    private function one(string $version, string $table): int
    {
        $this->dbRepository->truncateData($table);
        $zipPath = $this->getZipPath($version, $table);
        $zip = new Zip($zipPath);
        $result = 0;
        $queryBuilder = $this->dbRepository->getQueryBuilderByTableName($table);
        foreach ($zip->files() as $file) {
            $jsonData = $zip->readFile($file);
            $data = json_decode($jsonData, JSON_OBJECT_AS_ARRAY);
            //dd($data);
            $queryBuilder->insert($data);
            $result = $result + count($data);
            /*$conn = $this->capsule->getConnection();
            dd($conn->select());*/
            //$result = array_merge($result, $data);
        }
        return $result;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['<fg=white># Dump restore</>']);

        $this->dumpPath = $_ENV['ROOT_DIRECTORY'] . '/' . $_ENV['DUMP_DIRECTORY'];
        $this->currentDumpPath = $this->dumpPath . '/' . date('Y-m/d/H-i-s');

        $versions = $this->getHistory();

        $output->writeln('');
        $question = new ChoiceQuestion(
            'Select tables for import',
            $versions,
            'a'
        );
        $question->setMultiselect(false);
        $selectedVesrion = $this->getHelper('question')->ask($input, $output, $question);

//        $tree = FileHelper::scanDirTree($this->dumpPath, $options);

        $tables = $this->getTables($selectedVesrion);

        $tt = [];

        $tableList = $this->schemaRepository->allTables();
        $tableList = $this->schemaRepository->allTablesByName(EntityHelper::getColumn($tableList, 'name'));
        foreach ($tableList as $tableEntity) {
            $tableName = $tableEntity->getName();
            if($tableEntity->getRelations()) {
                $deps = [];
                foreach ($tableEntity->getRelations() as $relationEntity) {
                    $deps[] = $relationEntity->getForeignTableName();
                }
            }
            $this->tt[$tableName] = [
                'deps' => array_values(array_unique($deps)),
            ];
        }

        $tableQueue = [];
        foreach ($this->tt as $tableName => $tableInfo) {
            $this->pp($tableName);
        }

//        dd($this->pp);

        $ignoreTables = [
            'eq_migration',
        ];

        foreach ($this->pp as $tableName) {
            if(!in_array($tableName, $ignoreTables)) {
                $output->write($tableName . ' ... ');
                $count = $this->one($selectedVesrion, 'public.' . $tableName);
                $output->writeln('(' . $count . ') <fg=green>OK</>');
            }
        }

//        dd($tables);

        $output->writeln(['', '<fg=green>Dump restore success!</>', '']);
        return 0;
    }

    private $tt = [];
    private $pp = [];
    private $dd = [];

    protected function pp($tableName) {
        if(in_array($tableName, $this->dd)) {
            return;
        }
        $this->dd[] = $tableName;
//        dump($tableName);
        $deps = ArrayHelper::getValue($this->tt, [$tableName, 'deps']);
//        dd($deps);
        if($deps) {
            foreach ($deps as $dep) {
//                $this->pp[] = $dep;
                $this->pp($dep);
            }
        }
        $this->pp[] = $tableName;
    }
}

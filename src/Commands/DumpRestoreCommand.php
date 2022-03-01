<?php

namespace ZnLib\Db\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnCore\Base\Helpers\StringHelper;
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
        $this->dumpPath = $_ENV['ROOT_DIRECTORY'] . '/' . $_ENV['DUMP_DIRECTORY'];
        $this->currentDumpPath = $this->dumpPath . '/' . date('Y-m/d/H-i-s');
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

    private function getZipPath(string $version, string $table): array
    {
        $versionPath = $this->dumpPath . '/' . $version;
        $zipPath = $versionPath . '/' . $table . '.zip';
        return $zipPath;
    }

    private function one(string $version, string $table): array
    {
        $zipPath = $this->getZipPath($version, $table);
        $zip = new Zip($zipPath);
        $result = [];
        foreach ($zip->files() as $file) {
            $jsonData = $zip->readFile($file);
            $data = json_decode($jsonData);
            $result = array_merge($result, $data);
        }
        return $result;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['<fg=white># Dump restore</>']);

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

        foreach ($tables as $tableName) {
            $data = $this->one($selectedVesrion, $tableName);
            dd($data);
        }

        dd($tables);

        $output->writeln(['', '<fg=green>Dump restore success!</>', '']);
        return 0;
    }
}

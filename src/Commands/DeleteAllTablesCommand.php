<?php

namespace ZnLib\Db\Commands;

use Illuminate\Support\Collection;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnLib\Fixture\Domain\Entities\FixtureEntity;
use ZnLib\Console\Symfony4\Helpers\OutputHepler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAllTablesCommand extends BaseCommand
{
    protected static $defaultName = 'db:delete-all-tables';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Delete all tables')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['<fg=white># DELETE all tables</>']);

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allForDelete();

        if (empty($tableCollection->count())) {
            $output->writeln(['', '<fg=magenta>- No tables -</>', '']);
            return 0;
        }

        $withConfirm = $input->getOption('withConfirm');
        if ($withConfirm) {
            $versionArray = ArrayHelper::getColumn($tableCollection, 'name');
            $versionArray = array_values($versionArray);
            $output->writeln('');
            OutputHepler::writeList($output, $versionArray);
            $output->writeln('');
        }

        if ( ! $this->isContinueQuestion('Sure DELETE all tables?', $input, $output)) {
            return 0;
        }

        $output->writeln('');

        foreach ($tableCollection as $fixtureEntity) {
            $this->fixtureService->dropTable($fixtureEntity->name);
            $output->writeln(' ' . $fixtureEntity->name);
        }

        //$this->fixtureService->dropAllTables();

        $output->writeln(['', '<fg=green>DELETE all tables success!</>', '']);
        return 0;
    }

}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;

class MigrationGeneratorCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('process-manager:migrations:generate')
            ->setDescription('Generates a migration for a specific configuration.')
            ->addOption(
                'configuration',
                null,
                InputOption::VALUE_REQUIRED,
                'Configuration id for which the migration should be generated.'
            );
    }

    protected string $template = '<?php

declare(strict_types=1);

namespace <configurationMigrationsNamespace>;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;

final class <versionName> extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Migration for ProcessManager configuration \'<configurationId>\'";
    }

    /**
    * @var string[]
     */
    protected array $configurationData = <configurationData>;


    public function up(Schema $schema): void
    {
        $db = \Pimcore\Db::get();

        $configurationData = \Pimcore\Db\Helper::quoteDataIdentifiers($db,$this->configurationData);
        if(Configuration::getById($this->configurationData[\'id\'])) {
            $db->update(
                ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION,
                $configurationData,
                [\'id\' => $this->configurationData[\'id\']]
            );
        }else{
            $db->insert(
                ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION,
                $configurationData
            );
        }
    }

    public function down(Schema $schema): void
    {
        //not implemented
    }
}';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $options = ['all' => 'All configurations'];
        $configList = new Configuration\Listing();
        foreach ($configList->load() as $config) {
            $options[$config->getId()] = $config->getName();
        }

        $configurationIds = [];
        if ($input->getOption('configuration')) {
            $configurationIds = explode_and_trim(',', $input->getOption('configuration'));
        } else {
            $question = new ChoiceQuestion(
                'For which configuration would you like to generate a migration (defaults to "all")',
                $options,
                'all'
            );
            $question->setMultiselect(true);
            /**
             * @var QuestionHelper $helper
             */
            $configurationIds = $helper->ask($input, $output, $question);
            if (in_array('all', $configurationIds)) {
                $configurationIds = array_keys($options);
                $configurationIds = array_diff($configurationIds, ['all']);
            }
        }

        $config = ElementsProcessManagerBundle::getConfiguration();
        $configMigrationsDirectory = $config->getConfigurationMigrationsDirectory();

        $filesystem = new Filesystem();
        $filesystem->mkdir($configMigrationsDirectory);

        $db = \Pimcore\Db::get();

        foreach ($configurationIds as $configurationId) {
            $data = $db->fetchAssociative(
                'SELECT * FROM `' . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . '` WHERE `id` = :id ',
                ['id' => $configurationId]
            );
            if (!$data) {
                $output->writeln('<error>Configuration with id "' . $configurationId . '" not found.</error>');

                continue;
            }

            $template = $this->template;
            $template = str_replace('<configurationId>', $data['id'], $template);
            $template = str_replace('<configurationData>', var_export($data, true), $template);
            $template = str_replace(
                '<configurationMigrationsNamespace>',
                $config->getConfigurationMigrationsNamespace(),
                $template
            );
            $file = $this->getVersionFilePath(
                'VersionProcessManager' . ucfirst(\Pimcore\File::getValidFilename($data['id'])),
                $configMigrationsDirectory,
                1
            );

            $versionName = str_replace('.php', '', basename($file));
            $template = str_replace('<versionName>', $versionName, $template);

            $file = $configMigrationsDirectory . '/' . $versionName . '.php';
            file_put_contents($file, $template);
            $output->writeln('<info>Migration file created: ' . $file . '</info>');
        }

        return self::SUCCESS;
    }

    protected function getVersionFilePath(
        string $versionName,
        string $configMigrationsDirectory,
        int $versionNumber
    ): string {
        $file = $configMigrationsDirectory . '/' . $versionName . '_' . $versionNumber . '.php';
        while (file_exists($file)) {
            $versionNumber++;
            $file = $this->getVersionFilePath($versionName, $configMigrationsDirectory, $versionNumber);
        }

        return $file;
    }
}

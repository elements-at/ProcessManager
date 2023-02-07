<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Elements\Bundle\ProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class MultiprocessingSampleCommand extends AbstractCommand
{
    use ProcessManagerBundle\ExecutionTrait;
    use ProcessManagerBundle\MonitoringTrait;

    protected function configure(): void
    {
        $this
            ->setName('process-manager:multiprocessing-sample')
            ->setDescription('Create a set of sample products for testing.')
            ->addMonitoringItemIdOption()
            ->addMonitoringItemParentIdOption();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $monitoringItem = $this->initProcessManagerByInputOption($input);
        if ($input->getOption("monitoring-item-parent-id")) {
            $this->executeChild($input, $output, $monitoringItem); //child process
        } else {
            $this->executeParent($input, $output, $monitoringItem); //main process
        }
        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param MonitoringItem $monitoringItem
     * @throws \Exception
     */
    protected function executeParent(InputInterface $input, OutputInterface $output, MonitoringItem $monitoringItem)
    {
        $monitoringItem->getLogger()->debug('Start collection data...');

        $data = json_decode('[{"id":"84","fullpath":"\/PIM\/Product Tree\/Test Product 01","published":"1","creationDate":"1581108862","modificationDate":"1588164251","name":"Test Product 01","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/auto-1515428.jpg"},{"id":"85","fullpath":"\/PIM\/Product Tree\/Test Product 02","published":"1","creationDate":"1581108863","modificationDate":"1588164259","name":"Test Product 02","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/transport-546819.jpg"},{"id":"86","fullpath":"\/PIM\/Product Tree\/Test Product 03","published":"1","creationDate":"1581108863","modificationDate":"1588164265","name":"Test Product 03","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/Dodge.383.magnum-black.front.view-sstvwf.JPG"},{"id":"87","fullpath":"\/PIM\/Product Tree\/Test Product 04","published":"1","creationDate":"1581108863","modificationDate":"1588164268","name":"Test Product 04","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/dodge-charger-78260.jpg"},{"id":"88","fullpath":"\/PIM\/Product Tree\/Test Product 05","published":"1","creationDate":"1581108863","modificationDate":"1588164276","name":"Test Product 05","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/1962_Ferrari_250_GTE.jpg"},{"id":"89","fullpath":"\/PIM\/Product Tree\/Test Product 06","published":"1","creationDate":"1581108864","modificationDate":"1588164402","name":"Test Product 06","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/Inetrior_Ferrari_250_GTB_Berlinetta_SWB.jpg"},{"id":"90","fullpath":"\/PIM\/Product Tree\/Test Product 07","published":"1","creationDate":"1581108864","modificationDate":"1588164413","name":"Test Product 07 (Motor)","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/1990_Ferrari_Testarossa_engine.jpg"},{"id":"91","fullpath":"\/PIM\/Product Tree\/Test Product 08","published":"1","creationDate":"1581108864","modificationDate":"1588164419","name":"Test Product 08","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/Citroen_2CV_1X7A7979.jpg"},{"id":"92","fullpath":"\/PIM\/Product Tree\/Test Product 09","published":"1","creationDate":"1581108864","modificationDate":"1588164426","name":"Test Product 09","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/Citroen_DS_and_elephant.jpg"},{"id":"93","fullpath":"\/PIM\/Product Tree\/Test Product 10","published":"1","creationDate":"1581108864","modificationDate":"1588164433","name":"Test Product 10","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/red-89219.jpg"},{"id":"94","fullpath":"\/PIM\/Product Tree\/Test Product 11","published":"1","creationDate":"1581108865","modificationDate":"1588164438","name":"Test Product 11","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 2","additionalCategories":"","mainImage":"\/Car Images\/citroen\/car-1679015.jpg"},{"id":"95","fullpath":"\/PIM\/Product Tree\/Test Product 12","published":"1","creationDate":"1581108865","modificationDate":"1588164443","name":"Test Product 12","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 2","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/Ferrari_250_GT_Berlinetta_SWB.jpg"},{"id":"96","fullpath":"\/PIM\/Product Tree\/Test Product 13","published":"1","creationDate":"1581108865","modificationDate":"1588164448","name":"Test Product 13","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 2","additionalCategories":"","mainImage":"\/Car Images\/dodge\/field-1833347.jpg"},{"id":"97","fullpath":"\/PIM\/Product Tree\/Test Product 14","published":"1","creationDate":"1581108865","modificationDate":"1588164454","name":"Test Product 14","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/asphalt-automobile-automotive-1085174.jpg"},{"id":"98","fullpath":"\/PIM\/Product Tree\/Test Product 15","published":"1","creationDate":"1581108865","modificationDate":"1588164463","name":"Test Product 15","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/charger-1833346.jpg"},{"id":"99","fullpath":"\/PIM\/Product Tree\/Test Product 16","published":"1","creationDate":"1581108866","modificationDate":"1588164476","name":"Test Product 16","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/Charger_photo5.JPG"},{"id":"100","fullpath":"\/PIM\/Product Tree\/Test Product 17","published":"1","creationDate":"1581108866","modificationDate":"1588164539","name":"Test Product 17","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/ferrari\/Ferrari_Testarossa_-_Flickr_-_Alexandre_Pr-C3-A9vot_-285-29_-28cropped-29.jpg"},{"id":"101","fullpath":"\/PIM\/Product Tree\/Test Product 18","published":"1","creationDate":"1581108866","modificationDate":"1588164523","name":"Test Product 18","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/car-978088.jpg"},{"id":"102","fullpath":"\/PIM\/Product Tree\/Test Product 19","published":"1","creationDate":"1581108866","modificationDate":"1588164528","name":"Test Product 19","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/citroen\/auto-1515426.jpg"},{"id":"103","fullpath":"\/PIM\/Product Tree\/Test Product 20","published":"1","creationDate":"1581108866","modificationDate":"1588164533","name":"Test Product 20","mainCategory":"object:\/PIM\/Product Category Tree\/Test Category 1","additionalCategories":"","mainImage":"\/Car Images\/dodge\/classic-car-2726200.png"}]',true);

        $callback = function (MonitoringItem $childMonitoringItem){ //do some fancy stuff with the monitoring item before the job is executed
            $childMonitoringItem->setActions([])->setName('PM import  child :-)');
        };
        $this->executeChildProcesses($monitoringItem, $data, 2, 4, $callback);
        $monitoringItem->setMessage('Finished')->save();
    }

    protected function executeChild(InputInterface $input, OutputInterface $output, ProcessManagerBundle\Model\MonitoringItem $monitoringItem)
    {
        $monitoringItem->setMessage('Starting child process');
        $monitoringItem->getLogger()->info('Workload' . $monitoringItem->getMetaData());

        $workload = json_decode($monitoringItem->getMetaData(),true);

        $monitoringItem->setCurrentWorkload(0)->setTotalWorkload(count($workload))->setMessage('Processing Data')->save();
        foreach ($workload as $i => $data) {

            #$object = \AppBundle\Model\DataObject\Product::getById($data['id']);
            $object = new \stdClass();

            if ($data['id'] == 88) {
                #    throw new \Exception('Oh something happened with 88');
            }
            if ($object) {
                $monitoringItem->setMessage('Updating object ID:' . $data['id'])->setCurrentWorkload($i+1)->save();
                #$object->setName($data['name'].' MID: ' . $monitoringItem->getId() ,'en');
                #$object->save();
                sleep(1); //just for demo
            }
        }

        $monitoringItem->setMessage('Workload processed');
    }
}

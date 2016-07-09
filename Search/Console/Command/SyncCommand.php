<?php
namespace Klevu\Search\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SyncCommand extends Command {

    //params
    protected $_updatesonly;
    protected $_alldata;

    protected function configure() {
        $this->setName('klevu:syncdata')
                ->setDescription('Sync product and content Data With klevu.')
                ->setDefinition($this->getInputList());
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
		if(file_exists("klevu_running_index.lock")){
			echo "Klevu indexing process is in running state";
			return;
		} 
		fopen("pub/klevu_running_index.lock", "w");

        try {
			
			$state = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\App\State');
			$state->setAreaCode('frontend');
            //Sync Data 
			if($input->hasParameterOption('--updatesonly')) {
				

				$sync = \Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Search\Model\Product\Sync');
			    $sync->run();
				$sync = \Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Content\Model\Content');
				$sync->run();
				echo "Data updates have been sent to Klevu";
			} 
			
			if($input->hasParameterOption('--alldata')){
				$sync = \Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Search\Model\Product\Sync');
				$sync->markAllProductsForUpdate();
			    $sync->run();
				$sync = \Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Content\Model\Content');
				$sync->run();
				echo "All Data have been sent to Klevu";
			}

			
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln('Not able to update');
            $output->writeln($e->getMessage());
        }
		if(file_exists("pub/klevu_running_index.lock")){
			unlink("pub/klevu_running_index.lock");
		}
    }

    public function addArguments($input) {
        $this->_updatesonly = $input->getOption("updatesonly");
		$this->_alldata = $input->getOption("alldata");
    }

    public function getInputList() {
        $inputList = [];
		$inputList[] = new InputOption('updatesonly', null, InputOption::VALUE_OPTIONAL, 'Data updates have been sent to Klevu', 'updatesonly');
        $inputList[] = new InputOption('alldata', null, InputOption::VALUE_OPTIONAL, 'All Data have been sent to Klevu','alldata');
        return $inputList;
    }

}

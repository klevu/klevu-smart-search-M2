<?php
namespace Klevu\Search\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;
use Klevu\Search\Model\Product\Sync;
use Klevu\Content\Model\Content;

class SyncCommand extends Command
{
    const LOCK_FILE = 'var/klevu_running_index.lock';

    protected function configure()
    {
        $this->setName('klevu:syncdata')
                ->setDescription('Sync product and content Data With klevu.')
                ->setDefinition($this->getInputList());
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists(self::LOCK_FILE)) {
            $output->writeln('<info>Klevu indexing process is in running state</info>');
			return;
		} 

        fopen(self::LOCK_FILE, 'w');
			
        try {
            $state = ObjectManager::getInstance()->get('\Magento\Framework\App\State');
			$state->setAreaCode('frontend');

            //Sync Data 
            $sync = ObjectManager::getInstance()->get(Sync::class);
				
            if ($input->hasParameterOption('--alldata')) {
                $sync->markAllProductsForUpdate();
            }

		    $sync->run();

            $sync = ObjectManager::getInstance()->get(Content::class);
			$sync->run();
			
			if($input->hasParameterOption('--alldata')){
                $output->writeln('<info>Data updates have been sent to Klevu</info>');
            } elseif ($input->hasParameterOption('--updatesonly')) {
                $output->writeln('<info>All Data have been sent to Klevu</info>');
			}

        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } catch (Exception $e) {
            $output->writeln('<error>Not able to update</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        if (file_exists(self::LOCK_FILE)) {
            unlink(self::LOCK_FILE);
        }
    }

    public function getInputList()
    {
        $inputList = [];

        $inputList[] = new InputOption(
            'updatesonly',
            null,
            InputOption::VALUE_OPTIONAL,
            'Data updates have been sent to Klevu',
            'updatesonly'
        );

        $inputList[] = new InputOption(
            'alldata',
            null,
            InputOption::VALUE_OPTIONAL,
            'All Data have been sent to Klevu',
            'alldata'
        );

        return $inputList;
    }

}

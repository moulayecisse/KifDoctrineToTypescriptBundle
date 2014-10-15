<?php


namespace Kif\DoctrineToTypescriptBundle\Command;


use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Serializer;
use Kif\DoctrineToTypescriptBundle\Service\EntityIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ConvertCommand extends Command
{

    /**
     * @var ClassMetadata[]
     */
    private $allMetaData;

    private $serializer;

    public function __construct(array $allMetaData, Serializer $serializer = null)
    {
        if ($allMetaData == []) {
            throw new \Exception(
                'No Doctrine Entities on your system.'
            );
        }
        $this->allMetaData = $allMetaData;
        $this->serializer = $serializer;
        parent::__construct();

    }


    /**
     * configure the main command line
     */
    protected function configure()
    {
        $this
            ->setName('kif:doctrine:typescript:generate')
            ->setDescription('Convert doctrine entities into Typescript classes')
            ->addArgument('destination_folder', InputArgument::REQUIRED, 'In which folder to generate the .ts files?')
            ->addOption(
                'exposed-only',
                null,
                InputOption::VALUE_NONE,
                'If set, only exposed entites/variables will be generated'
            );

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destinationFolder = $input->getArgument('destination_folder');
        $exposedOnly = false;
        $generateSingleFile = false;


        if (!file_exists($destinationFolder)) {
            throw new FileNotFoundException(
                'The destination folder does not exist.'
            );
        }


        if ($input->getOption('exposed-only')) {
            $output->writeln('<info>Generating only exposed entities....</info>');
            if ($this->serializer == null) {
                throw new ServiceNotFoundException(
                    'install the jms serializer bundle to use the --exposed-only option'
                );
            }
            $exposedOnly = true;
        }

        $output->writeln('<info>Generating Typescript....</info>');
        $entityIterator = new EntityIterator($this->allMetaData, $destinationFolder, $exposedOnly, $generateSingleFile);
        $entityIterator->entityBundlesIterator();


    }


}

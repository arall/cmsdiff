<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Arall\CMSDiff\Mapper\Mapper;
use Exception;

class RepositoryMap extends Command
{
    public function configure()
    {
        $this
            ->setName('repository:map')
            ->setDescription('Map all releases files from a repository')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Repository releases folder name (data/joomla-cms)'
            )
            ->addArgument(
                'product',
                InputArgument::REQUIRED,
                'Product name (Joomla)'
            )
            ->addOption(
               'output',
               'o',
               InputOption::VALUE_OPTIONAL,
               'JSON Output file'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $product = $input->getArgument('product');
        $outputFile = $input->getOption('output') ?: 'data/output.json.gz';
        $outputPath = dirname($outputFile);

        // Check output path
        if (!is_dir($outputPath) || !is_writable($outputPath)) {
            return $output->writeln('<error>Destination path '.$outputPath.' is not writable</error>');
        }

        try {
            $mapper = new Mapper($target, $product);
            $mapper->scan();
        } catch (Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        // Generate json
        $mapper->save($outputFile);

        $output->writeln('<info>Files map saved to '.$outputFile.'</info>');
        $output->writeln('');
    }
}

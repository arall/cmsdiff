<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Arall\CMSDiff;

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
        $outputFile = $input->getOption('output') ?: 'data/output.json';
        $outputPath = dirname($outputFile);

        // Check output path
        if (!is_dir($outputPath) || !is_writable($outputPath)) {
            return $output->writeln('<error>Destination path '.$outputPath.' is not writable</error>');
        }

        try {
            $diff = new CMSDiff($target);
        } catch (\Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        // Generate json
        file_put_contents($outputFile, json_encode($diff->map, JSON_PRETTY_PRINT));

        $output->writeln('<info>Files map saved to '.$outputFile.'</info>');
        $output->writeln();
    }
}

<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Arall\CMSDiff\Matcher\Fetcher;
use Arall\CMSDiff\Matcher\Matcher;
use Arall\CMSDiff\Mapper\Map;
use Exception;

class WebsiteMatch extends Command
{
    public function configure()
    {
        $this
            ->setName('website:match')
            ->setDescription('Get website CMS version')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Website url (http://www.joomla.org/)'
            )
            ->addArgument(
                'json',
                InputArgument::REQUIRED,
                'Path to JSON map file / dir (generated with repository:map)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $jsonPath = $input->getArgument('json');

        // Load Json file
        if (!file_exists($jsonPath)) {
            return $output->writeln('<error>Data file / path not found: '.$jsonPath.'</error>');
        }

        try {
            $fetcher = new Fetcher($url);
            $map = new Map($jsonPath);
            $matcher = new Matcher($fetcher, $map, $output);

            $versions = $matcher->match();
        } catch (Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        $output->writeln('<info>Possible product version(s): '.implode(', ', $versions).'</info>');
        $output->writeln('');
    }
}
